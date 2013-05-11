<?php

// will do what tables.php does for user and tutorial controllers, to request data ini files

// will
// 1. validate all mandatory fields are there
// 2. ommit and response on execces data
// 3. validate each data field to hold a valid value
// 4. convert data field to stored type

class DataManager {
	const TAG = 'interface_data_manager.php, DataManager:';
	
	final public static function process_data(array $data, $ini_file)
	{
		$data_to_store = array();
		$columns =  parse_ini_file($ini_file,true);
		foreach ($columns as $key => $fieldP) {
			$is_mandatory = (array_key_exists('is_mandatory', $fieldP)) ? $fieldP['is_mandatory'] : false;
			
			if (!array_key_exists($key, $data)) {
				$stored_key = (array_key_exists('stored_name', $fieldP)) ? $fieldP['stored_name'] : $key;
				// Validate value:
				if (array_key_exists('validate', $fieldP)) {
					call_user_func('self::validate_column_value', $fieldP['validate'], $data[$key]);
				}
				// Convert to stored value:
				if (array_key_exists('rule', $fieldP)) {
					$data_to_store[$stored_key] = call_user_func('self::convertion_rule', $val['rule'], $data[$key]);
				} else {
					$data_to_store[$stored_key] = $data[$key];
				}
			} elseif ($is_mandatory) {
				throw new SKHR_Exception(self::TAG. 'missing missing mandatory field for:  '.$ini_file, Messages::INVALID_FIELD_VALUE);
			}
			
			
		}
		return $data_to_store;
	}
	
	// ---------------- RULES:
	# at the ini file, the rule keys are either 'method' or discret values.
	final private function convertion_rule(array $rule_info, $value) {
		if (array_key_exists('method', $rule_info)) {
			$method = $rule_info['method'];
			try {
				$res = call_user_func($method, $value);
			} catch (Exception $e) {
				throw new SKHR_Exception(self::TAG. 'Method '.$method.' exit with exception: '.$e->getMessage().' for: '.$value,\
						Messages::INVALID_FIELD_VALUE);
			}
			return $res;
		}
		if (array_key_exists($value, $rule_info)) {
			return $rule_info[$value];
		} else {
			throw new SKHR_Exception(self::TAG. 'missing method key or rule key for '.$value, Messages::INVALID_FIELD_VALUE);
		}
	}
	
	// ---------------- VALIDATORS:
	final private function validate_column_value(array $validate_info, $value) {
		if (array_key_exists('method', $validate_info)) {
			$method = $validate_info['method'];
		} else {
			throw new SKHR_Exception(self::TAG. 'missing method key in validate array', Messages::INVALID_FIELD_VALUE);
		}
		unset($validate_info['method']);
		$restrictions = $validate_info;
		try {
			$res = call_user_func('self::'.$method, $value, $restrictions);
		} catch (Exception $e) {
			throw new SKHR_Exception(self::TAG. 'Method '.$method.' exit with exception: '.$e->getMessage().' for: '.$value,\
					Messages::INVALID_FIELD_VALUE);
		}
		return $res;
	}
	
	final private function validate_email($email, $restrictions) {
		if (filter_var($email,FILTER_VALIDATE_EMAIL) == FALSE) {
			throw new SKHR_Exception(self::TAG.'Field: email. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final private function validate_int($value, $restrictions) {
	
		$options = array(
				'options' => array(
						'min_range' => $restrictions['min'],
						'max_range' => $restrictions['max']
				)
		);
	
		if (filter_var($value, FILTER_VALIDATE_INT, $options) === false) {
			throw new SKHR_Exception(self::TAG.'  value: '.$value, Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final private function validate_link($link, $restrictions) {
		if (filter_var($link,FILTER_VALIDATE_URL) == FALSE) {
			throw new SKHR_Exception(self::TAG.'   LINK: '.$link, Messages::INVALID_FIELD_VALUE);
		}
		if (array_key_exists('max_length', $restrictions)) {
			if (strlen($link) > $restrictions['max_length']) {
				throw new SKHR_Exception(self::TAG.' value exceeds max length', Messages::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('contain', $restrictions)) {
			if (!strpos($value, $restrictions['contain'])) {
				throw new SKHR_Exception(self::TAG.'  '.$restrictions['contain'].' not appears', Messages::INVALID_FIELD_VALUE);
			}
		}
	}
	
	final private function validate_string($value, $restrictions)
	{
		if (array_key_exists('max_length', $restrictions)) {
			if (strlen($value) > $restrictions['max_length']) {
				throw new SKHR_Exception(self::TAG.' value exceeds max length. Value: '.$value, Messages::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('min_length', $restrictions)) {
			if (strlen($value) < $restrictions['min_length']) {
				throw new SKHR_Exception(self::TAG.' value is shorter than min length. Value: '.$value, Messages::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('contain', $restrictions)) {
			if (!strpos($value, $restrictions['contain'])) {
				throw new SKHR_Exception(self::TAG.'  '.$restrictions['contain'].' not appears', Messages::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('options', $restrictions)) {
			$options = preg_split('/[\s]+/', $restrictions['options']);
			if (!in_array($value, $options)) {
				throw new SKHR_Exception(self::TAG.' value must be one of: '.$restrictions['options'], Messages::INVALID_FIELD_VALUE);
			}
		}
	}
}
?>