<?php
class UserTable { 
	const TAG = 'tables.php, UserTable:'; 
	const TABLE = 'users';
	const USER_ID_OFFSET = 320000200020001;
	const USER_NORMAL_STRING_LEN = 50;
	const USER_TYPES_LIST = 'skhr facebook';
	
	final public static function columns() {
		$columns = array(
				'user_id' => array(
						'presented_name' => 'skhr_id',
						'validate' => 'user_id_validate'
				),
				'type' => array(
						'presented_name' => 'type',
						'rule' => 'type_rule',
						'validate' => 'type_validate'
				),
				'email' => array(
						'presented_name' => 'email',
						'validate' => 'email_validate'
				),
				'fb_id' => array(
						'presented_name' => 'id',
						'validate' => 'fb_id_validate'
				),
				'username' => array(
						'presented_name' => 'username',
						'validate' => 'username_validate'
				),
				'password' => array(
						'presented_name' => 'password',
						'validate' => 'password_validate'
				),
				'name' => array(
						'presented_name' => 'name',
						'validate' => 'name_validate'
				),
				'first_name' => array(
						'presented_name' => 'first_name',
						'validate' => 'first_name_validate'
				),
				'last_name' => array(
						'presented_name' => 'last_name',
						'validate' => 'last_name_validate'
				),
				'fb_link' => array(
						'presented_name' => 'fb_link',
						'validate' => 'fb_link_validate'
				),
				'birthday' => array(
						'presented_name' => 'birthday',
						'validate' => 'birthday_validate'
				),
				'gender' => array(
						'presented_name' => 'gender',
						'rule' => 'gender_rule',
						'validate' => 'gender_validate'
				),
				'timezone' => array(
						'presented_name' => 'timezone',
						'validate' => 'timezone_validate'
				),
				'locale' => array(
						'presented_name' => 'locale',
						'validate' => 'locale_validate'
				),
				'verified' => array(
						'presented_name' => 'verified',
						'validate' => 'verified_validate',
						'rule' => 'verified_rule'
				),
				'updated_time' => array(
						'presented_name' => 'updated_time',
						'validate' => 'updated_time_validate'
				),
				'created_time' => array(
						'presented_name' => 'created_time',
						'validate' => 'created_time_validate'
				)
		);
		return $columns;
	}
	
	// ---------------- RULES:
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
	final public static function email_validate($email) {
		if (filter_var($email,FILTER_VALIDATE_EMAIL) == FALSE) {
			throw new SKHR_Exception(self::TAG.'Field: email. ', Messages::INVALID_FIELD_VALUE);
		} 
	}
	
	final public static function user_id_validate($user_id) {
		$options = array(
				'options' => array(
						'min_range' => self::USER_ID_OFFSET,
						'max_range' => self::USER_ID_OFFSET * 2
				)
		);
		if (filter_var($user_id, FILTER_VALIDATE_INT, $options) == FALSE) {
			throw new SKHR_Exception(self::TAG.'Field: user_id. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function fb_link_validate($link) {
		if (filter_var($link,FILTER_VALIDATE_URL) == FALSE) {
			throw new SKHR_Exception(self::TAG.'Field: fb_link. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function username_validate($field_str) {
		$sanitize = filter_var($field_str,FILTER_SANITIZE_STRING);
		if (strlen($sanitize) !== $field_str || empty($field_str) || strlen($field_str) > self::USER_NORMAL_STRING_LEN) {
			throw new SKHR_Exception(self::TAG.'Field: username. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function name_validate($field_str) {
		$sanitize = filter_var($field_str,FILTER_SANITIZE_STRING);
		if (strlen($sanitize) !== $field_str || empty($field_str) || strlen($field_str) > self::USER_NORMAL_STRING_LEN) {
			throw new SKHR_Exception(self::TAG.'Field: name. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function first_name_validate($field_str) {
		$sanitize = filter_var($field_str,FILTER_SANITIZE_STRING);
		if (strlen($sanitize) !== $field_str || empty($field_str) || strlen($field_str) > self::USER_NORMAL_STRING_LEN) {
			throw new SKHR_Exception(self::TAG.'Field: first_name. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function last_name_validate($field_str) {
		$sanitize = filter_var($field_str,FILTER_SANITIZE_STRING);
		if (strlen($sanitize) !== $field_str || strlen($field_str) > self::USER_NORMAL_STRING_LEN) {
			throw new SKHR_Exception(self::TAG.'Field: last_name. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function type_validate($field_str) {
		$types = preg_split('/[\s]+/', self::USER_TYPES_LIST);
		if (!in_array($field_str, $types)) {
			throw new SKHR_Exception(self::TAG.'Field: type. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function fb_id_validate($fb_id) {
		//$options = array('options' => array('min_range' => 12000000,'max_range' => self::USER_ID_OFFSET * 2));
		if (filter_var($fb_id, FILTER_VALIDATE_INT) == FALSE) {
			throw new SKHR_Exception(self::TAG.'Field: fb_id. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function birthday_validate($field_str) {
		$sanitize = filter_var($field_str,FILTER_SANITIZE_STRING);
		if (strlen($field_str) > self::USER_NORMAL_STRING_LEN) {
			throw new SKHR_Exception(self::TAG.'Field: birthday. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	final public static function gender_validate($field_str) {
		$sanitize = filter_var($field_str,FILTER_SANITIZE_STRING);
		if (strlen($sanitize) !== $field_str || empty($field_str) || strlen($field_str) > 10) {
			throw new SKHR_Exception(self::TAG.'Field: gender. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function password_validate($password) {
		$options = array('options' => array('min_range' => 6,'max_range' => 12));
		if (filter_var(strlen($password), FILTER_VALIDATE_INT, $options) == FALSE) {
			throw new SKHR_Exception(self::TAG.'Field: password length limits 6-12. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function locale_validate($field_str) {
		$sanitize = filter_var($field_str,FILTER_SANITIZE_STRING);
		if (strlen($sanitize) !== $field_str || strlen($field_str) > 10) {
			throw new SKHR_Exception(self::TAG.'Field: locale. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function verified_validate($verified) {
// 		$options = array('options' => array('min_range' => 0,'max_range' => 1));
		if (filter_var($verified, FILTER_VALIDATE_BOOLEAN) == FALSE) {
				throw new SKHR_Exception(self::TAG.'Field: verified should be boolean. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function timezone_validate($link) {
	$options = array('options' => array('min_range' => 0,'max_range' => 24));
		if (filter_var($verified, FILTER_VALIDATE_INT, $options) == FALSE) {
				throw new SKHR_Exception(self::TAG.'Field: timezone. ', Messages::INVALID_FIELD_VALUE);
		}
	}
	
	final public static function updated_time_validate($time) {
// 		$t = gettype(date(DATE_ATOM,time()));
		return;
	}
	
	final public static function created_time_validate($time) {
// 		$t = gettype(date(DATE_ATOM,time()));
		return;
	}
	
}

class TokenTable {
	const MYSQL_CREATE_CODE ='CREATE TABLE `tokens` (
								`token_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								`user_id` INT(10) UNSIGNED NOT NULL,
								`token` BLOB NOT NULL,
								`expiry` TIMESTAMP NULL DEFAULT NULL,
								PRIMARY KEY (`token_id`),
								INDEX `FK__users` (`user_id`)
							)';
	
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
				if (array_key_exists('vaidate', $columns[$key])) {
					call_user_func($table.'::'.$columns[$key]['validate'],$data[$pkey]);
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


