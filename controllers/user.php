<?php
require_once("exceptions.php");
require_once("constants.php");
require_once("database/dbapi.php");
require_once("database/tables.php");
require_once("controllers/utilities");

class User {
	const FACEBOOK_USER = 1;
	const GOOGLE_USER = 2;
	const SKHR_USER = 0;
	
	const UNIQUE_FIELD_FACEBOOK = 'fb_id';
	const UNIQUE_FIELD_GOOGLE = 'google_id';
	const UNIQUE_FIELD_SKHR = 'email';
	
	public $result = array('code' => 0, 'data' => array());
	
	public $type;
	
	private $user_id = -1;
	private $unique_field;
	
	function __construct($type) {
		$this->type = $type;
	}
	
	function set_user_id($user_data) {
		$this->user_id = -1;
	}
	
	function get_user_id($user_data) {
		if ($this->user_id == -1) {
			$this->set_user_id($user_data);
		}
		return $this->user_id;
	}
	
	function set_unique_field() {
		switch ($this->type) {
			case self::SKHR_USER:
				$this->unique_field = self::UNIQUE_FIELD_SKHR;
			break;
			case self::FACEBOOK_USER:
				$this->unique_field = self::UNIQUE_FIELD_FACEBOOK;
			break;
			case self::GOOGLE_USER:
				$this->unique_field = self::UNIQUE_FIELD_GOOGLE;
			break;
			default:
				throw new SKHR_Exception('Account type: '.$this->type.' is not supported.', ExitCode::UNKNOWN_ACCOUNT_TYPE);
			break;
		}
	}
	
	function get_unique_field() {
		if ($this->unique_field == '') {
			$this->set_unique_field();
		}
		return $this->unique_field;
	}
	
	
}
class UserRegister extends User {
	
	const TAG = 'user.php, UserRegister:'; 
	
	private $data_to_store = array();
	
	function __construct(array $data) {
		$this->data_to_store = $data;
		$this->register();
	}
	
	private function register() {
		try {
			$this->verify_credentials_uniqueness();
		} catch (SKHR_Exception $e) {
			echo self::TAG.$e;
		}
		
		$this->create_new_user();
		$this->result['data']['user_id'] = $this->data_to_store['user_id'];
		
		if (!$this->data_to_store['verified']) {
			self::send_verification_mail($this->data_to_store['user_id'], $this->data_to_store['email']);
		}
		
	}
	
	private function verify_credentials_uniqueness() {
		switch ($this->data_to_store['type']) {
			case 0:
				$unique_field_name = 'email';
				break;
			case 1:
				$unique_field_name = 'fb_id';
			default:
				throw new SKHR_Exception(self::TAG, ExitCode::UNKNOWN_ACCOUNT_TYPE);;
				break;
		}
		$quary_res = DBAPI::get_row_value(Table::USER,array($unique_field_name=>$this->data_to_store[$unique_field_name]), 'user_id');
		if ($quary_res!= array()) {
			throw new SKHR_Exception(self::TAG, ExitCode::CREDENTIALS_ALREADY_IN_USE);
		}
	}
		
	private function create_new_user() {
		
		// Insert the data
		$this->data_to_store['created_time'] = date(DATE_ATOM,time());
		$res = DBAPI::add_row_with_values(Table::USER, $this->data_to_store);
		$this->data_to_store['user_id'] = $res;
		if ($this->data_to_store['user_id']==0) {
			$this->result['code'] = ExitCode::REGISTRATION_FAILED;
		}
		$this->result['code'] = ExitCode::REGISTRATION_SUCCEDED;
	}
	
	private function send_verification_mail($user_id, $user_email) {
		
		$verification_code = UTILS::genRandomString();
		$verification_code_data = array (
				'code' => $verification_code,
				'user_id' => $user_id
		);
		$verify_code_id = DBAPI::add_row_with_values(Table::VERIFICATION_CODES, $verification_code_data);
		UTILS::send_mail_via_gmail_account($verification_code, $user_email);
	}
	
}

class UserLogin {

	const TAG = 'user.php, UserLogin:';
	
	public $result = array('code' => 0, 'data' => array());
	private $data_to_store = array();
	
	const MANDATORY_FIELDS = 'type password email';
	const MANDATORY_FIELDS_FACEBOOK = 'type fb_id token';
	
	function __construct(array $data) 
	{	
		$request_token = (isset($data['token'])) ? $data['token'] : null;
		$this->data_to_store = TableDataManager::render_server_data($data, Table::USER_TABLE_INI_FILE);
		$this->data_to_store['token'] = $request_token;
		if (!isset($this->data_to_store['type'])) {
			throw new SKHR_Exception(self::TAG.' type is mandatory for login action ', ExitCode::MANDATORY_FIELD_MISSING);
		}
		$this->login();
	}

	private function login() {
		$fields_list = ($this->data_to_store['type']) ? self::MANDATORY_FIELDS_FACEBOOK : self::MANDATORY_FIELDS;
		RequestData::verify_mandatory_fields($fields_list, $this->data_to_store);
		
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
		$qres = DBAPI::get_row_value(Table::USER, $by_col_values, 'user_id');
		if ($qres == array()) {
			throw new SKHR_Exception(self::TAG.' User is not registered.', ExitCode::LOGIN_FAILED);
		} elseif (count($qres) > 1) {
			throw new SKHR_Exception(self::TAG.' More than one user own these credentials.', ExitCode::LOGIN_FAILED);
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
		if (!$db_token_id) {
			throw new SKHR_Exception(self::TAG.'Failed to insert new user token',ExitCode::TOKEN_INSERTION_FAILED);	
		}
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