<?php

require_once("messages.php");
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
			echo self::TAG.$e;
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

class UserToken {
	const TAG = 'user.php, UserToken:';
	
	public static function new_token_for_user($user_id, $user_info = '') {
// 		echo 'user id: '.$user_id. "\n";
		//	Create token
		$expiry = date(DATE_ATOM,time() + 30*24*60*60);
		$characters= serialize(array('user_info'=> $user_info, 'expiry'=>$expiry, 'user_id'=>$user_id));
		$token = sha1(md5(substr(str_shuffle($characters), 0, 30)));
		// Insert token
		$token_data_store = array('token' => $token, 'expiry' => $expiry,'user_id' => $user_id);
		$db_token_id = DBAPI::add_row_with_values(Token::TABLE, $token_data_store);
// 		echo 'token: '.$token. "\n";
// 		echo 'db_token_id: '.$db_token_id. "\n";
		return(($db_token_id) ? $token : Messages::TOKEN_INSERTION_FAILED);
	}
}

?>