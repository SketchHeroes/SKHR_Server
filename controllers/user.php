<?php
require_once("exceptions.php");
require_once("constants.php");
require_once("database/dbapi.php");
require_once("database/tables.php");
require_once("controllers/utilities.php");
require_once('packages/bcrypt.php');

class User {
	
	public $result = null;
	
	public function get_result()
	{
		return $this->result;
	}
	
	public function set_result($code, $data, $additional_info = '')
	{
		$this->result = array('code' => $code, 'data' => $data, 'additional_info' => $additional_info);
	}
	
	public $verified = null;
	public $logged_in = null;
	
	public $stored_user_fields = null;
	public $stored_user_data = null;
	
	public $user_id = null;
	
	public function __construct()
	{
		
	}
	
	public function set_stored_user_fields() {
		if ($this->stored_user_fields == null) {
			$this->stored_user_fields = array_keys(parse_ini_file(Table::USER_TABLE_INI_FILE, true));
		}
	}
	
	public function set_verified($user_id) {
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
	
	public function set_stored_user_data($user_id) {
		if ($this->stored_user_data === null) {
			$this->set_stored_user_fields();
			$quary_res = DBAPI::get_row_values(Table::USER,array('user_id'=>$user_id), $this->stored_user_fields);
			if ($quary_res!= array()) {
				$this->stored_user_data = $quary_res;
			}
		}
	}
	
	public function set_logged_in($user_id, $token) {
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
	
}


class UserSKHR extends User {
	const TYPE = 0;
	const UNIQUE_IDENTIFIER = 'email';

	private $request_data = null;
	
	public function __construct($request_data)
	{
		parent::__construct();
		$this->request_data = $request_data;
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
		if (UserFunctions::is_user_exist(self::UNIQUE_IDENTIFIER, $this->request_data[self::UNIQUE_IDENTIFIER]) == 0) {
			$this->set_stored_user_fields();
			$user_id = UserFunctions::create_new_user($this->stored_user_fields, $this->request_data);
			if ($user_id > 0) {
				$this->user_id = $user_id;
				$this->set_stored_user_data($user_id);
				$data = $this->stored_user_data;
				if (UserFunctions::send_verification_mail($user_id, $this->request_data['email']) < 1) {
					$code = ExitCode::FAILED_TO_SEND_VERIFICATION_MAIL;
					$info = 'Failed to create new user';
				}
			} else {
				$code = ExitCode::FAILED_TO_CREATE_NEW_USER;
				$info = 'Failed to create new user';
			}
		} else {
			$code = ExitCode::USER_ALREADY_EXIST;
			$info = 'User with the supplied '.self::UNIQUE_IDENTIFIER.' : '.$this->request_data[self::UNIQUE_IDENTIFIER].' is already registered.';
		}
		$this->set_result($code, $data, $info);
		return $code;
	}
	
	// Returns:
	// 0 Success
	// 1 General Error
	// 2 User is not exist (wrong identifier)
	// 3 wrong password
	// 4 User is not verified
	// 5 Failed to create or store token
	public function login() {
		$code = 0;
		$info = '';
		$data = array();
		$credentials = array(
				'email' => $this->request_data['email']
		);
		$user_id = UserFunctions::identify_user_by_credentials($credentials);
		
		if ($user_id > 0) {
			$this->set_stored_user_data($user_id);
			$user_data = $this->stored_user_data;
			
			$bcrypt = new Bcrypt(12);
			$hash = $user_data['password'];
			$isGood = $bcrypt->verify($this->request_data['password'], $hash);
			if ($isGood) {
				$this->set_verified($user_id);
				if ($this->verified) {
					try {
						$token = UserToken::token_for_user($user_id, $user_data);
						$data['token'] = $token;
					} catch (Exception $e) {
						$code = ExitCode::FAILED_TO_CREATE_TOKEN;
						$info = 'Failed to login, failed to create token: '.$e->getMessage()."\n";
					}
				} else {
					$code = ExitCode::USER_IS_NOT_VERIFIED;
					$info = 'Failed to login, user is not verified.';
				}
			} else {
				$code = ExitCode::WRONG_PASSWORD;
				$info = 'Failed to login, wrong password.';
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
	public function verify() {
		$code = 0;
		$info = '';
		$data = array();
		
		$user_id = $this->request_data['user_id'];
		$vcode = $this->request_data['code'];
		
		$verification_code = UserFunctions::get_verification_code($user_id);
		if ($verification_code != 0) {
			if ($vcode == $verification_code) {
				DBAPI::remove_row_identify_by_values(Table::VERIFICATION_CODES, array('user_id' => $user_id));
				DBAPI::update_row_with_values(Table::USER, array('verified' => 1), array('user_id' => $user_id));
				
				$this->set_stored_user_data($user_id);
				$user_data = $this->stored_user_data;
				try {
					$token = UserToken::token_for_user($user_id, $user_data);
					$data = array('user_id' => $user_id, 'token' => $token);
				} catch (Exception $e) {
					$code = ExitCode::FAILED_TO_CREATE_TOKEN;
					$info = 'Failed to create token, please login again';
				}
			} else {
				$code = ExitCode::WRONG_VERIFICATION_CODE;
				$info = 'Wrong verification code';
			}
		} else {
			$code = ExitCode::VERIFICATION_CODE_NOT_EXIST;
			$info = 'No verification code exist for this user';
		}
		
		$this->set_result($code, $data, $info);
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
	
}

class UserFB extends User  {
	const TYPE = 1;
	const UNIQUE_IDENTIFIER = 'fb_id';
	
	private $request_data = null;
	
	public function __construct($request_data)
	{
		parent::__construct();
		$this->request_data = $request_data;
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
		if (UserFunctions::is_user_exist(self::UNIQUE_IDENTIFIER, $this->request_data[self::UNIQUE_IDENTIFIER]) == 0) {
			$this->set_stored_user_fields();
			$user_id = UserFunctions::create_new_user($this->stored_user_fields, $this->request_data);
			if ( $user_id > 0) {
				$this->user_id = $user_id;
				$this->set_stored_user_data($user_id);
				$data = $this->stored_user_data;
			} else {
				$code = ExitCode::FAILED_TO_CREATE_NEW_USER;
				$info = 'Failed to create new user';
			}
		} else {
			$credentials = array(
					'fb_id' => $this->request_data['fb_id']
			);
			$user_id = UserFunctions::identify_user_by_credentials($credentials);
			if ($user_id > 0) {
				$this->set_stored_user_data($user_id);
				$user_data = $this->stored_user_data;
				if ($this->set_verified($user_id) == 1) {
					try {
						$token = UserToken::insert_token($user_id, $this->request_data['token'], $this->request_data['expiry']);
					} catch (Exception $e) {
						$code = ExitCode::FAILED_TO_CREATE_TOKEN;
						$info = 'Failed to login, failed to create token';
					}
				} else {
					$code = ExitCode::USER_IS_NOT_VERIFIED;
					$info = 'Failed to login, user is not verified.';
				}
			}
		}
		$this->set_result($code, $data, $info);
		return $code;
	}
}

class UserGOOGLE extends User {
	const TYPE = 2;
	const UNIQUE_IDENTIFIER = 'google_id';
	
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
}

class UserFunctions {
	// Returns:
	// 0 Not exist
	// 1 Exist
	public static function is_user_exist($unique_field, $unique_value) {
		$quary_res = DBAPI::get_row_value(Table::USER,array($unique_field=>$unique_value),'user_id');
		return ($quary_res != array()) ? 1 : 0;
	}
	
	// Returns:
	// created user_id
	// 0 for failure
	public static function create_new_user($stored_user_fields, $request_data)
	{
		$data_to_store = array();
		$data_to_store['created_time'] = date(DATE_ATOM,time());
		foreach ($stored_user_fields as $field) {
			if (array_key_exists($field, $request_data)) {
				$data_to_store[$field] = $request_data[$field];
			}
		}
		$res = DBAPI::add_row_with_values(Table::USER, $data_to_store);
		if ($res > 0) {
			return $res;
		} else {
			return 0; 
		}
		
	}
	
	// Return:
	// created Verification id
	// 0 for failure
	public static function send_verification_mail($user_id, $user_email)
	{
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
	public static function identify_user_by_credentials(array $credentials) 
	{
		$quary_res = DBAPI::get_row_value(Table::USER, $credentials, 'user_id');
		if ($quary_res != array()) {
			return $quary_res[0];
		}
		return 0;
	}
	
	// Returns:
	// 0 Not exist
	// code
	public static function get_verification_code($user_id) {
		$quary_res = DBAPI::get_row_value(Table::VERIFICATION_CODES, array('user_id' => $user_id), 'code');
		if ($quary_res != array()) {
			return $quary_res[0];
		}
		return 0;
	}
}


class UserToken {
	const TAG = 'user.php, UserToken:';

	public static function token_for_user($user_id, $user_info = '', $token = null, $expiry = null) {
		if ($token == null) {
			list($token, $expiry) = self::generate_new_token($user_id, $user_info);
		} elseif ($expiry == null) {
			list($dummy_token, $expiry) = self::generate_new_token($user_id, $user_info);
		}
		
		$token_id = self::is_token_exist($user_id);
		if ($token_id) {
			self::update_token($token_id, $token, $expiry);
		} else {
			self::insert_token($user_id, $token, $expiry);
		}
		return $token;
	}

	private static function generate_new_token($user_id, $user_info = '') {
		$expiry = date(DATE_ATOM,time() + 30*24*60*60);
		$characters = serialize(array('user_info'=> $user_info, 'expiry'=>$expiry, 'user_id'=>$user_id));
		$res = array(
				sha1(md5(substr(str_shuffle($characters), 0, 30))),
				$expiry
		);
		return($res);
	}
	
	public static function is_token_exist($user_id) {
		$db_token_id = DBAPI::get_row_value(Table::TOKEN, array('user_id' => $user_id), 'token_id');
		if ($db_token_id[0] < 1) {
			return 0;
		}
		return $db_token_id[0];
	}
	
	public static function insert_token($user_id, $token, $expiry) {
		$token_data_store = array('token' => $token, 'expiry' => $expiry,'user_id' => $user_id);
		$db_token_id = DBAPI::add_row_with_values(Table::TOKEN, $token_data_store);
		if ($db_token_id < 1) {
			return 0;
		}
		return $db_token_id;
	}

	private static function update_token($token_id, $token, $expiry) {
		$token_data_store = array('token' => $token, 'expiry' => $expiry);
		$db_token_updated = DBAPI::update_row_with_values(Table::TOKEN, $token_data_store, array('token_id' => $token_id));
		if ($db_token_updated < 1) {
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