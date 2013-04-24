<?php
class UserTable { 
	const TAG = 'tables.php, UserTable:'; 
	const TABLE_NAME = 'users';
	const USER_ID_OFFSET = 320000200020001;
	const STANDARD_STRING_LEN = 50;
	const USER_TYPES_LIST = 'skhr facebook';
	
	final public static function columns() {
		return(parse_ini_file('./DataBase/tables/user.ini',true));
	}

	// ---------------- RULES:
	# to_stored = 
	# true: convert from accepted to stored value
	# false: convert from stored to accept value
	final public static function convertion_rule($value, $to_stored) {
		
	}
	
	final public static function type_rule($getp) {
		if (gettype($getp) == gettype('string')) {
			$rtype = ($getp == 'facebook') ? 1 : 0;
		} elseif (gettype($getp) == gettype(2)) {
			$rtype = ($getp) ? 'facebook' : 'skhr'; 
		}
		return $rtype;
	}
	
	final public static function gender_rule($getp) {
		if (gettype($getp) == gettype('string')) {
			$rtype = ($getp == 'male') ? 0 : 1;
		} elseif (gettype($getp) == gettype(2)) {
			$rtype = ($getp) ? 'female' : 'male';
		}
		return $rtype;
	}
	
	final public static function verified_rule($getp) {
		return $getp;
		if (gettype($getp) == gettype(TRUE)) {
			return($getp ? 1 : 0);
		} else {
			return(($getp == 1) ? true : false);
		}
	}
	
	// ---------------- VALIDATORS:
	final public static function validate_column_value(array $col_descriptor, $value) {
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
		return call_user_func($method, $value, $restrictions);
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
		if (filter_var($user_id, FILTER_VALIDATE_INT, $options) == FALSE) {
			throw new SKHR_Exception(self::TAG, Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function validate_link($link, $restrictions) {
		if (filter_var($link,FILTER_VALIDATE_URL) == FALSE) {
			throw new SKHR_Exception(self::TAG, Messages::INVALID_FIELD_VALUE);
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
	
	final public static function validate_string($value, $restrictions) {
		if (array_key_exists('max_length', $restrictions)) {
			if (strlen($value) > $restrictions['max_length']) {
				throw new SKHR_Exception(self::TAG.' value exceeds max length', Messages::INVALID_FIELD_VALUE);
			}
		}
		if (array_key_exists('min_length', $restrictions)) {
			if (strlen($value) > $restrictions['min_length']) {
				throw new SKHR_Exception(self::TAG.' value is shorter than min length', Messages::INVALID_FIELD_VALUE);
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

class TokenTable {
	
	final public static function columns() {
	
		$columns = array(
				'token_id' => array(
						'validate' => 'token_id_validate'
				),
				'user_id' => array(
						'presented_name' => 'skhr_id',
						'validate' => 'user_id_validate'
				),
				'token' => array(
						'validate' => 'token_validate'
				),
				'expiry' => array(
						'validate' => 'expiry_validate'
				)
		);
		return $columns;
	}
}


class TableDataManager {

	const TAG = 'tables.php, TableDataManager:';

	public static function render_data(array $data, $table) {
		$arr_client_mode = array();
		$columns =  call_user_func($table.'::columns');
		foreach ($data as $key => $val) {
			$pkey = (array_key_exists('presented_name', $columns[$key])) ? $columns[$key]['presented_name'] : $key;
			if (array_key_exists('rule', $columns[$key])) {
				$data[$pkey] = call_user_func($table.'::'.$columns[$key]['rule'],$data[$key]);
			}
			$arr_client_mode[$pkey] = $data[$key];
		}
		return $arr_client_mode;
	}

	public static function accept_data(array $data, $table) {
		$arr_db_mode = array();
		$columns =  call_user_func($table.'::columns');
		foreach ($columns as $key => $val) {
			$pkey = (array_key_exists('presented_name', $columns[$key])) ? $columns[$key]['presented_name'] : $key;
			if (array_key_exists($pkey, $data)) {
				// Validate presented value:
				if (array_key_exists('validate', $columns[$key])) {
// 					call_user_func($table.'::'.$columns[$key]['validate'],$data[$pkey]);
				}
				// Convert presented to stored value:
				if (array_key_exists('rule', $columns[$key])) {
					$data[$pkey] = call_user_func($table.'::'.$columns[$key]['rule'],$data[$pkey]);
				}
				$arr_db_mode[$key] = $data[$pkey];
			}
		}
		return $arr_db_mode;
	}
}
?>


