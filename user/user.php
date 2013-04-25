<?php

// class UserTable {
// 	const TAG = 'tables.php, UserTable:';
// 	const TABLE_NAME = 'users';
// 	const USER_ID_OFFSET = 320000200020001;
// 	const STANDARD_STRING_LEN = 50;
// 	const USER_TYPES_LIST = 'skhr facebook';

// 	final public static function columns() {
// 		return();
// 	}


// }

require_once("exceptions.php");
require_once("mysql_scripts/constants.php");
require_once("dbapi.php");
require_once("tables.php");

class User extends TableDataManager {
	const TAG = 'user.php, User:';
	const TABLE_CLASS = 'UserTable';
	const TABLE = 'users';	
}

class Token extends TableDataManager {
	const TAG = 'user.php, Token:';
	const TABLE_CLASS = 'TokenTable';
	const TABLE = 'tokens';
}

class UserRegister {
	
	const TAG = 'user.php, UserRegister:'; 
	public $result = array('code' => 0, 'data' => array());
	private $data_to_store = array();
	
	function __construct(array $data) {
// 		parent::__construct();
		$this->data_to_store = User::accept_data($data, User::TABLE_CLASS);
// 		echo '------------ REGISTER --------------'."\n";
// 		echo "\n".print_r($this->data_to_store). "\n";
// 		echo '------------ ******** --------------'."\n";
		$this->register();
	}
	
	private function register() {
		try {
			$this->verify_credentials_uniqueness();
		} catch (SKHR_Exception $e) {
// 			echo self::TAG.$e;
		}
		
		$this->create_new_user();
		$this->result['data'] = $this->data_to_store;
		$tk = UserToken::new_token_for_user($this->data_to_store['user_id'], $this->data_to_store);
// 		echo 'token: '.$tk. "\n"; 
		$this->result['data']['token'] = $tk;
	}
	
	private function verify_credentials_uniqueness() {
		if (!array_key_exists('type', $this->data_to_store)) {
			throw new SKHR_Exception(self::TAG.' facebook or skhr must be set for type index. ', Messages::INVALID_FIELD_VALUE);
		}
		switch ($this->data_to_store['type']) {
			case 0:
				if (!array_key_exists('email', $this->data_to_store)) {
					throw new SKHR_Exception(self::TAG, Messages::CREDENTIALS_EMAIL_IS_MANDATORY);
				}
				$quary_res = DBAPI::get_row_value(User::TABLE,array('email'=>$this->data_to_store['email']), 'user_id');
				if ( $quary_res!= array()) {
					throw new SKHR_Exception(self::TAG, Messages::CREDENTIALS_ALREADY_IN_USE);
				}
				break;
			case 1:
				if (!array_key_exists('fb_id', $this->data_to_store)) {
					throw new SKHR_Exception(self::TAG, Messages::CREDENTIALS_FB_ID_IS_MANDATORY);
				}
				if (DBAPI::get_row_value(User::TABLE, array('fb_id'=>$this->data_to_store['fb_id']), 'user_id') != array()) {
					throw new SKHR_Exception(self::TAG, Messages::CREDENTIALS_ALREADY_IN_USE);
				}
			default:
				throw new SKHR_Exception(self::TAG, Messages::UNKNOWN_ACCOUNT_TYPE);;
				break;
		}
	}
		
	private function create_new_user() {
		
		// Insert the data
		$this->data_to_store['created_time'] = date(DATE_ATOM,time());
		$res = DBAPI::add_row_with_values(User::TABLE, $this->data_to_store);
		$this->data_to_store['user_id'] = $res;
		if ($this->data_to_store['user_id']==0) {
			$this->result['code'] = Messages::REGISTRATION_FAILED;
		}
		$this->result['code'] = Messages::REGISTRATION_SUCCEDED;
	}
	
}

class UserLogin {

	const TAG = 'user.php, UserLogin:';
	public $result = array('code' => 0, 'data' => array());
	private $data_to_store = array();
	const MANDATORY_FIELDS = 'type password email';
	const MANDATORY_FIELDS_FACEBOOK = 'type fb_id token';
	function __construct(array $data) {
		// 		parent::__construct();
		$request_token = (isset($data['token'])) ? $data['token'] : null; 
		$this->data_to_store = User::accept_data($data, User::TABLE_CLASS);
		$this->data_to_store['token'] = $request_token;
		// 		echo '------------ REGISTER --------------'."\n";
		// 		echo "\n".print_r($this->data_to_store). "\n";
		// 		echo '------------ ******** --------------'."\n";
		$this->login();
	}

	private function login() {
		$this->verify_mandatory_fields();
		if (!$this->data_to_store['type']) {
			$by_col_values = array(
					'type' => $this->data_to_store['type'],
					'password' => $this->data_to_store['password'],
					'email' => $this->data_to_store['email']
			);
		} else {
			$by_col_values = array(
					'type' => $this->data_to_store['type'],
					'fb_id' => $this->data_to_store['fb_id']
			);
		}
		$qres = DBAPI::get_row_value(User::TABLE,$by_col_values, 'user_id');
		if ($qres == array()) {
			throw new SKHR_Exception(self::TAG.' User is not registered.', Messages::LOGIN_FAILED);
		} elseif (count($qres) > 1) {
			throw new SKHR_Exception(self::TAG.' More than one user own these credentials.', Messages::LOGIN_FAILED);
		}
		$user_id = $qres[0];
		
		
		$this->result['data']['user_id'] = $user_id; 
		if ($this->data_to_store['type']) {
			$tk = UserToken::new_token_for_user_facebook($user_id, $this->data_to_store['token']);
			
		} else {
			$tk = UserToken::new_token_for_user($user_id, $this->data_to_store);
		}
		// 		echo 'token: '.$tk. "\n";
		$this->result['data']['token'] = $tk;
	}

	private function verify_mandatory_fields() {
		if (!array_key_exists('type', $this->data_to_store)) {
			throw new SKHR_Exception(self::TAG.' type is mandatory for login action ', Messages::MANDATORY_FIELD_MISSING);
		}
		$mandatory_fields = (!$this->data_to_store['type']) ? self::MANDATORY_FIELDS : self::MANDATORY_FIELDS_FACEBOOK;
		$fields_to_verify = preg_split('/[\s]+/', $mandatory_fields);
		foreach ($fields_to_verify as $field_name) {
			if (!array_key_exists($field_name, $this->data_to_store)) {
				throw new SKHR_Exception(self::TAG.' '.$field_name.' is mandatory for login action ', Messages::MANDATORY_FIELD_MISSING);
			}
		}
		
	}

}

class UserToken {
	const TAG = 'user.php, UserToken:';
	
	public static function new_token_for_user($user_id, $user_info = '') {
		list($token, $expiry) = self::generate_new_token($user_id, $user_info);		
		self::insert_token($user_id, $token, expiry);
		return $token;
	}
	
	public static function update_token_for_user($user_id, $user_info = '', $token = null, $expiry = null) {
		if ($token == null) {
			list($token, $expiry) = self::generate_new_token($user_id, $user_info);
		}
		self::update_token($user_id, $token, expiry);
		return $token;
	}
	
	private function generate_new_token($user_id, $user_info = '') {
		$expiry = date(DATE_ATOM,time() + 30*24*60*60);
		$characters = serialize(array('user_info'=> $user_info, 'expiry'=>$expiry, 'user_id'=>$user_id));
		$res = array(
				sha1(md5(substr(str_shuffle($characters), 0, 30))),
				$expiry
		);
		return($res);
	}
	
	private function insert_token($user_id, $token, $expiry) {
		$token_data_store = array('token' => $token, 'expiry' => $expiry,'user_id' => $user_id);
		$db_token_id = DBAPI::add_row_with_values(Token::TABLE, $token_data_store);
		if ($db_token_id) {
			throw new SKHR_Exception(self::TAG.'Failed to insert new user token',Messages::TOKEN_INSERTION_FAILED);	
		}
	}
	
	private function update_token($user_id, $token, $expiry) {
		$token_data_store = array('token' => $token, 'expiry' => $expiry);
		$db_token_id = DBAPI::update_row_with_values(Token::TABLE, $token_data_store, $user_id);
		if ($db_token_id) {
			throw new SKHR_Exception(self::TAG.'Failed to update user\'s token',Messages::TOKEN_UPDATE_FAILED);	
		}
	}
		
	
	public static function is_token_valid($token) {
		$qres = DBAPI::get_row_value(Token::TABLE, array('token' => $token), 'expiry');
		$current = date(DATE_ATOM,time());
		return ($qres[0] > $current) ? true : false;
	}
}

?>