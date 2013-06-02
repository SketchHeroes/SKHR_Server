<?php
require_once("exceptions.php");
require_once("constants.php");
require_once("database/dbapi.php");
require_once("database/tables.php");
require_once("controllers/utilities.php");

class User {
// 	type
	const TYPE_FACEBOOK = 1;
	const TYPE_GOOGLE = 2;
	const TYPE_SKHR = 0;
	
// 	unique
	const UNIQUE_IDENTIFIER_FACEBOOK = 'fb_id';
	const UNIQUE_IDENTIFIER_GOOGLE = 'google_id';
	const UNIQUE_IDENTIFIER_SKHR = 'email';
	const UNIQUE_IDENTIFIER_ALL = 'user_id';
	
// 	identified
	const NOT_IDENTIFIED = 0;
	const IDENTIFIED = 1;
	const AMBIVALENT = 2;
	
	
// 	per type
	private $type = null;
	private $unique_field = null;
	private $stored_user_fields = array();
	
// 	Status
// 	private $registered = null;
	private $verified = null;
	private $logged_in = null;
// 	private $identified = self::NOT_IDENTIFIED;
	
	private $user_id = null;
	private $stored_user_data = array();
	
	public function __construct($type) 
	{
		$this->type = $type;
	}
	
// 	Getters

	public function get_user_type() 
	{
		return $this->type;
	}
	
	public function get_unique_field() 
	{
		$this->set_unique_field();
		return $this->unique_field;
	}
	
	public function get_stored_user_fields()
	{
		$this->set_stored_user_fields();
		return $this->stored_user_fields;
	}
	
	public function get_verified($user_id) 
	{
		$this->set_verified($user_id);
		return $this->verified;
	}
	
	public function get_logged_in($user_id, $token) {
		$this->set_logged_in($user_id, $token);
		return $this->logged_in;
	}
	
	public function get_user_id() {
		return $this->user_id;
	}
	
	public function get_stored_user_data($user_id) {
		$this->set_stored_user_data($user_id);
		return $this->stored_user_data;
	}
	
// 	Setters
	
	private function set_unique_field()
	{
		if ($this->unique_field != null) {return;}
		switch ($this->type) {
			case self::TYPE_SKHR:
				$this->unique_field = self::UNIQUE_FIELD_SKHR;
				break;
			case self::TYPE_FACEBOOK:
				$this->unique_field = self::UNIQUE_FIELD_FACEBOOK;
				break;
			case self::TYPE_GOOGLE:
				$this->unique_field = self::UNIQUE_FIELD_GOOGLE;
				break;
			default:
				throw new SKHR_Exception('Account type: '.$this->type.' is not supported.', ExitCode::UNKNOWN_ACCOUNT_TYPE);
				break;
		}
	}
	
	
	
	private function set_verified($user_id) {
		if ($this->verified != null) {return;}
		$this->set_stored_user_data($user_id);
		if (array_key_exists('verified', $this->stored_user_data)) {
			if ($this->stored_user_data['verified'] == 1) {
				$this->verified = true;
			} else {
				$this->verified = false;
			}
		}
	} 
	
	private function set_stored_user_data($user_id) {
		if ($this->stored_user_data == array()) {
			$this->set_stored_user_fields();
			$quary_res = DBAPI::get_row_values(Table::USER,array('user_id'=>$user_id), $this->stored_user_fields);
			if ($quary_res!= array()) {
				$this->stored_user_data = $quary_res[0];
			}	
		}
	}
	
	private function set_logged_in($user_id, $token) {
		if ($this->logged_in == null) {
			$this->logged_in = true;
			$res = DBAPI::get_row_value(Table::TOKEN, array('user_id'=>$user_id, 'token'=>$token), 'expiry');
			if ($res == array()) {
				$this->logged_in = false;
			} elseif (date(DATE_ATOM,time()) >= $res[o]) {
				$this->logged_in = false;
			}
		}
	}
	
	public function set_user_id($user_id) {
		if ($this->user_id == null) {
			$this->user_id = $user_id;
		}
	}
	
// Utils	
// 	private function set_registered() {
// 		if ($this->registered == null) {
// 			$this->identify_user();
// 			switch ($this->identified) {
// 				case self::IDENTIFIED:
// 					$this->registered = true;
// 					break;
// 				case self::NOT_IDENTIFIED:
// 					$this->registered = false;
// 					break;
// 				case self::AMBIVALENT:
// 					throw new SKHR_Exception('More than 1 registered users with: '.$this->unique_field.' = '.$this->unique_value, ExitCode::USER_DATABASE_INTERNAL_ERROR);
// 					break;
// 				default:
// 					throw new SKHR_Exception('Invalid value '.$this->identified.' for User.', ExitCode::USER_CONTROLLER_INTERNAL_ERROR);
// 					break;
// 			}
// 		}
// 	}
// 	private function get_user_value($user_id, $field_name)
// 	{
// 		if ($user_id > 0) {
// 			$quary_res = DBAPI::get_row_value(Table::USER, array('user_id' => $user_id), $field_name); 
// 			if ($quary_res != array()) {
// 				return $quary_res[0];
// 			}
// 		}
// 		return ;
// 	}
	
	private function set_identify() {
		$this->get_unique_field();
		$quary_res = DBAPI::get_row_values(Table::USER,array($this->unique_field=>$this->uniqe_value), 'user_id');
		if ($quary_res!= array()) {
			if (array_sum($quary_res) > $quary_res[0]) {
				$this->identified = self::AMBIVALENT;
				return;
			}
			$this->user_id = $quary_res[0];
			$this->identified = self::IDENTIFIED;
		}
	}
}

class UserRoutines {
	
	private $result = null;
	
	private $request_data = null;
	private $type = null;
	private $user = null;
	
	public function __construct($request_data)
	{
		$this->request_data = $request_data;
		$this->type = $request_data['type'];
		$this->user = new User($this->type);
	}
	
	public function get_result() 
	{
		return $this->result;
	}
	
	public function set_result($code, array $data, $additional_info = '') 
	{
		$this->result = array('code' => 0, 'data' => array(), 'additional_info' => $additional_info);
	}
	
	// Returns:
	// 0 Success
	// 1 General Error
	// 2 User is already exist
	// 3 Failed to create new user
	// 4 Failed to send verification code
	public function register() {
		$code = 0;
		$info = '';
		$data = array();
		if ($this->is_user_exist() == 0) {
			if ($this->create_new_user() > 0) {
				$user_id = $this->user->get_user_id();
				$data = $this->user->get_stored_user_data($user_id);
				if ($this->send_verification_mail($user_id) < 1) {
					$code = ExitCode::FAILED_TO_SEND_VERIFICATION_MAIL;
					$info = 'Failed to create new user';
				}
			} else {
				$code = ExitCode::FAILED_TO_CREATE_NEW_USER;
				$info = 'Failed to create new user';
			}
		} else {
			$code = ExitCode::USER_ALREADY_EXIST;
			$info = 'User with the supplied '.$this->user->get_unique_field().' is already registered.';
		}
		$this->set_result($code, $data, $info);
		return $code;
		
	}
	
	// Returns:
	// 0 Success
	// 1 General Error
	// 2 User is not exist
	// 3 User is not verified
	// 4 Failed to create or store token
	public function login() {
		$code = 0;
		$info = '';
		$data = array();
		if ($this->identify_user_by_credentials() > 0) {
			$user_id = $this->user->get_user_id();
			$user_data = $this->user->get_stored_user_data($user_id);
			if ($this->user->get_verified($user_id) == 1) {
				try {
					$token = UserToken::new_token_for_user($user_id, $user_data);
				} catch (Exception $e) {
					$code = ExitCode::FAILED_TO_CREATE_TOKEN;
					$info = 'Failed to login, failed to create token';
				}
			} else {
				$code = ExitCode::USER_IS_NOT_VERIFIED;
				$info = 'Failed to login, user is not verified.';
			}
		} else {
			$code = ExitCode::USER_NOT_EXIST;
			$info = 'Failed to login, User with these credentials is not exist.';
		}
		$this->set_result($code, $data, $info);
		return $code;
	}
	
	// Returns:
	// 0 Success
	// 1 General Error
	// 2 User is not exist
	// 3 User is not verified
	// 4 Failed to create or store token
	public function login_via_facebook() {
		$code = 0;
		$info = '';
		$data = array();
		if ($this->is_user_exist() == 0) {
			if ($this->create_new_user() > 0) {
				$user_id = $this->user->get_user_id();
				$data = $this->user->get_stored_user_data($user_id);
			} else {
				$code = ExitCode::FAILED_TO_CREATE_NEW_USER;
				$info = 'Failed to create new user';
			}
		} else {
			
		}
		$this->set_result($code, $data, $info);
		return $code;
	}
	
	// Returns:
	// 0 Success
	// 1 General Error
	// 2 User is not exist
	// 3 User is not verified
	// 4 Failed to create or store token
	public function login_via_google() {
		$code = 0;
		$info = '';
		if ($this->identify_user_by_credentials() < 1) {
			$code = ExitCode::USER_NOT_EXIST;
			$info = 'Failed to login, User with these credentials is not exist.';
		}
	
	
		$this->set_result($code, $this->stored_user_data, $info);
	}
	
	// Returns:
	// 0 Success
	// 1 General Error
	// 2 User is not exist
	// 3 User is not verified
	// 4 Failed to create or store token
	public function verify($user_id, $code) {
		$code = 0;
		$info = '';
		$data = array();
		
		if ($this->identify_user_by_credentials() < 1) {
			$code = ExitCode::USER_NOT_EXIST;
			$info = 'Failed to login, User with these credentials is not exist.';
		}
	
		$this->set_result($code, $this->stored_user_data, $info);
	}
	
	// Returns:
	// 0 Success
	// 1 General Error
	// 2 User is not exist
	// 3 User is not verified
	// 4 Failed to create or store token
	public function forgot_password($email) {
		$code = 0;
		$info = '';
		$data = array();
	
		if ($this->identify_user_by_credentials() < 1) {
			$code = ExitCode::USER_NOT_EXIST;
			$info = 'Failed to login, User with these credentials is not exist.';
		}
	
		$this->set_result($code, $this->stored_user_data, $info);
	}
	
// 	-----------------------------------------------------------------------------------

	// Returns:
	// 0 Not exist
	// 1 Exist
	private function is_user_exist() {
		$unique_field = $this->user->get_unique_field();
		$value = $this->request_data[$unique_field];
		$quary_res = DBAPI::get_row_value(Table::USER,array($unique_field=>$value),'user_id');
		return ($quary_res != array()) ? 1 : 0;
	}
	
	// Returns:
	// created user_id
	// 0 for failure 
	private function create_new_user()
	{
		$data_to_store = array();
		$data_to_store['created_time'] = date(DATE_ATOM,time());
		foreach ($this->user->get_stored_user_fields() as $field) {
			if (array_key_exists($field, $this->request_data)) {
				$data_to_store[$field] = $this->request_data[$field];
			}
		}
		$res = DBAPI::add_row_with_values(Table::USER, $data_to_store);
		if ($res > 0) {
			$this->user->set_user_id($res);
		}
		return $res;
	}
	
	// Return:
	// created Verification id
	// 0 for failure 
	private function send_verification_mail($user_id)
	{
		$user_email = $this->user->get_stored_user_data($user_id)['email'];
		$verification_code = UTILS::genRandomString();
		$verification_code_data = array (
				'code' => $verification_code,
				'user_id' => $user_id
		);
		$verify_code_id = DBAPI::add_row_with_values(Table::VERIFICATION_CODES, $verification_code_data);
		if ($verify_code_id > 0) {
			try {
				UTILS::send_mail_via_gmail_account($verification_code, $user_email);
			} catch (SKHR_Exception $e) {
				return 0;
			}
			return $verify_code_id;
		}
		return 0;
	}
	
	// Returns:
	// 0 Not exist
	// user_id 
	private function identify_user_by_credentials() {
		$credentials = array(
			'password' => $this->request_data['password'],
			'email' => $this->request_data['email']
		);
		$quary_res = DBAPI::get_row_value(Table::USER, $credentials, 'user_id');
		if ($quary_res != array()) {
			$this->user->set_user_id($quary_res[0]);
			return $this->user->get_user_id();
		}
		return 0;
	}
	
}



class UserToken {
	const TAG = 'user.php, UserToken:';
	
	public static function new_token_for_user($user_id, $user_info = '') {
		list($token, $expiry) = self::generate_new_token($user_id, $user_info);
		self::insert_token($user_id, $token, $expiry);
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
		$db_token_id = DBAPI::add_row_with_values(Table::TOKEN, $token_data_store);
		if ($db_token_id < 1) {
			return 0;	
		}
		return $db_token_id;
	}
	
	private function update_token($user_id, $token, $expiry) {
		$token_data_store = array('token' => $token, 'expiry' => $expiry);
		$db_token_id = DBAPI::update_row_with_values(Table::TOKEN, $token_data_store, $user_id);
		if ($db_token_id) {
			throw new SKHR_Exception(self::TAG.'Failed to update user\'s token',ExitCode::TOKEN_UPDATE_FAILED);	
		}
	}
		
	
	public static function is_token_valid($token) {
		$qres = DBAPI::get_row_value(Table::TOKEN, array('token' => $token), 'expiry');
		$current = date(DATE_ATOM,time());
		return ($qres[0] > $current) ? true : false;
	}
}

?>