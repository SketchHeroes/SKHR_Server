<?php

// will do what tables.php does for user and tutorial controllers, to request data ini files

// will
// 1. validate all mandatory fields are there
// 2. ommit and response on execces data
// 3. validate each data field to hold a valid value
// 4. convert data field to stored type

require_once('packages/bcrypt.php');

class DataManager {
	const TAG = 'interface_data_manager.php, DataManager:';
	
	final public static function process_data(array $process_data, $ini_file)
	{
		$result_data = array();
		$columns =  parse_ini_file($ini_file,true);
		foreach ($columns as $key => $fieldP) {
			$is_mandatory = (array_key_exists('is_mandatory', $fieldP)) ? $fieldP['is_mandatory'] : false;
			
			if (array_key_exists($key, $process_data)) {
				$process_key = (array_key_exists('name', $fieldP)) ? $fieldP['name'] : $key;
				// Validate value:
				if (array_key_exists('validate', $fieldP)) {
					call_user_func('self::validate_column_value', $fieldP['validate'], $process_data[$key]);
				}
				// Convert to stored value:
				if (array_key_exists('rule', $fieldP)) {
					$result_data[$process_key] = call_user_func('self::convertion_rule', $fieldP['rule'], $process_data[$key]);
				} else {
					$result_data[$process_key] = $process_data[$key];
				}
				unset($process_data[$key]);
			} elseif ($is_mandatory) {
				throw new SKHR_Exception(self::TAG. 'missing missing mandatory field for:  '.$ini_file, ExitCode::INVALID_FIELD_VALUE);
			}
		}
// 		if ($process_data != array()) {
// 			echo 'Warning:Excess DATA: '.print_r($process_data);
// 		}
		return $result_data;
	}
	
	// ---------------- RULE:
	# at the ini file, the rule keys are either 'method' or discret values.
	final static function convertion_rule(array $rule_info, $value) {
		if (array_key_exists('method', $rule_info)) {
			$method = $rule_info['method'];
			try {
				$res = call_user_func(array('self', $method), $value);
			} catch (Exception $e) {
				throw new SKHR_Exception(self::TAG. 'Method '.$method.' exit with exception: '.$e->getMessage().' for: '.$value,\
						ExitCode::INVALID_FIELD_VALUE);
			}
			return $res;
		}
		if (array_key_exists($value, $rule_info)) {
			return $rule_info[$value];
		} else {
			throw new SKHR_Exception(self::TAG. 'missing method key or rule key for '.$value, ExitCode::INVALID_FIELD_VALUE);
		}
	}
	
	// ---------------- RULE Methods:
	final static function encode_password($value) {
		$bcrypt = new Bcrypt(15);
		$hash = $bcrypt->hash($value);
		return $hash;
// 		$isGood = $bcrypt->verify('password', $hash);
// 		return password_hash($value, PASSWORD_DEFAULT);
	}
	
	// ---------------- VALIDATORS:
	final static function validate_column_value(array $validate_info, $value) {
		if (array_key_exists('method', $validate_info)) {
			$method = $validate_info['method'];
		} else {
			throw new SKHR_Exception(self::TAG. 'missing method key in validate array', ExitCode::INVALID_FIELD_VALUE);
		}
		unset($validate_info['method']);
		$restrictions = $validate_info;
		try {
			$res = call_user_func('self::'.$method, $value, $restrictions);
		} catch (Exception $e) {
			throw new SKHR_Exception(self::TAG. 'Method '.$method.' exit with exception: '.$e->getMessage().' for: '.$value,\
					ExitCode::INVALID_FIELD_VALUE);
		}
		return $res;
	}
	
	// ---------------- VALIDATORS Methods:
	final static function validate_email($email, $restrictions) {
		if (filter_var($email,FILTER_VALIDATE_EMAIL) == FALSE) {
			throw new SKHR_Exception(self::TAG.'Field: email. ', ExitCode::INVALID_FIELD_VALUE);
		}
	}
	
	final static function validate_int($value, $restrictions) {
	
		$options = array(
				'options' => array(
						'min_range' => @$restrictions['min'],
						'max_range' => @$restrictions['max']
				)
		);
	
// 		if (filter_var($value, FILTER_VALIDATE_INT, $options) === false) {
// 			throw new SKHR_Exception(self::TAG.'  value: '.$value.' is out of range: '.$restrictions['min'].'-'.$restrictions['max'], ExitCode::INVALID_FIELD_VALUE);
// 		}
		
	}
	
	final static function validate_link($link, $restrictions) {
		if (filter_var($link,FILTER_VALIDATE_URL) == FALSE) {
			throw new SKHR_Exception(self::TAG.'   LINK: '.$link, ExitCode::INVALID_FIELD_VALUE);
		}
		if (array_key_exists('max_length', $restrictions)) {
			if (strlen($link) > $restrictions['max_length']) {
				throw new SKHR_Exception(self::TAG.' value exceeds max length', ExitCode::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('contain', $restrictions)) {
			if (!strpos($value, $restrictions['contain'])) {
				throw new SKHR_Exception(self::TAG.'  '.$restrictions['contain'].' not appears', ExitCode::INVALID_FIELD_VALUE);
			}
		}
	}
	
	final static function validate_string($value, $restrictions)
	{
		if (array_key_exists('max_length', $restrictions)) {
			if (strlen($value) > $restrictions['max_length']) {
				throw new SKHR_Exception(self::TAG.' value exceeds max length. Value: '.$value, ExitCode::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('min_length', $restrictions)) {
			if (strlen($value) < $restrictions['min_length']) {
				throw new SKHR_Exception(self::TAG.' value is shorter than min length. Value: '.$value, ExitCode::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('contain', $restrictions)) {
			if (!strpos($value, $restrictions['contain'])) {
				throw new SKHR_Exception(self::TAG.'  '.$restrictions['contain'].' not appears', ExitCode::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('options', $restrictions)) {
			$options = preg_split('/[\s]+/', $restrictions['options']);
			if (!in_array($value, $options)) {
				throw new SKHR_Exception(self::TAG.' value must be one of: '.$restrictions['options'], ExitCode::INVALID_FIELD_VALUE);
			}
		}
	}
}
?>