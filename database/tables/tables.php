<?php
class Table 
{
	const USER = 'users';
	const USER_TABLE_INI_FILE = 'user.ini';
	
	const TOKEN = 'tokens';
	const TOKEN_TABLE_INI_FILE = 'token.ini';
	
	const TUTORIAL = 'tutorials';
	const COMMENT = 'comments';
	const FOLOWER = 'followers';
	const IMAGE = 'images';
}

class TableDataManager 
{

	const TAG = 'tables.php, TableDataManager:';
	
	final public static function render_client_data(array $server_data, $table_ini_file) 
	{
		$client_data = array();
		$columns =  parse_ini_file($table_ini_file,true);
		foreach ($server_data as $key => $val) {
			$pkey = (array_key_exists('presented_name', $columns[$key])) ? $columns[$key]['presented_name'] : $key;
			if (array_key_exists('rule', $columns[$key])) {
				$client_data[$pkey] = call_user_func(self::convertion_rule($columns[$key], $val, true));
			} else {
				$client_data[$pkey] = $server_data[$key];
			}
		}
		return $client_data;
	}

	final public static function render_server_data(array $client_data, $table_ini_file) 
	{
		$server_data = array();
		$columns =  parse_ini_file($table_ini_file,true);
		foreach ($columns as $key => $val) {
			$pkey = (array_key_exists('presented_name', $columns[$key])) ? $columns[$key]['presented_name'] : $key;
			if (array_key_exists($pkey, $client_data)) {
				// Validate presented value:
				if (array_key_exists('validate', $columns[$key])) {
// 					echo "value: ".$key."\n";
					call_user_func('self::validate_column_value', $columns[$key], $client_data[$pkey]);
				}
				// Convert presented to stored value:
				if (array_key_exists('rule', $columns[$key])) {
					$server_data[$key] = call_user_func('self::convertion_rule', $columns[$key], $client_data[$pkey], false);
				} else {
					$server_data[$key] = $client_data[$pkey];
				}
			}
		}
		return $server_data;
	}
	
	// ---------------- RULES:
	# human =
	# true: convert from client to stored value
	# false: convert from stored to client value
	# at the ini file, the rule keys are either 'method' or discret human values.
	final private function convertion_rule(array $col_descriptor, $value, $human) {
		if (array_key_exists('rule', $col_descriptor)) {
			$rule_info = $col_descriptor['rule'];
		} else {
			return $value;
		}
		if (array_key_exists('method', $rule_info)) {
			$method = $rule_info['method'];
			return call_user_func($method, $value, $human);
		}
		
		if (!$human) {
			if (array_key_exists($value, $rule_info)) {
				return $rule_info[$value];
			} else {
				throw new SKHR_Exception(self::TAG. 'missing method key or rule key for '.$value, Messages::INVALID_FIELD_VALUE);
			}
		} else {
			if ($key = array_search($value, $rule_info)) {
				return $key;
			} else {
				throw new SKHR_Exception(self::TAG. 'missing method key or value for '.$value, Messages::INVALID_FIELD_VALUE);
			}
		}
	}
		
	// ---------------- VALIDATORS:
	final private function validate_column_value(array $col_descriptor, $value) {
		if (array_key_exists('validate', $col_descriptor)) {
			$validate_info = $col_descriptor['validate'];
		} else {
			return true;
		}
		if (array_key_exists('method', $validate_info)) {
			$method = $validate_info['method'];
		} else {
			throw new SKHR_Exception(self::TAG. 'missing method key in validate array', Messages::INVALID_FIELD_VALUE);
		}
		unset($validate_info['method']);
		$restrictions = $validate_info;
		return call_user_func('self::'.$method, $value, $restrictions);
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


