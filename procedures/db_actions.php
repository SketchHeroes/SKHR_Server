<?php
require_once("dbapi.php");

class DBRead {
	
	// Credentials and Token
	
	public static function token_by_user_id($user_id) {
		$col_val_array = array('user_id' => $user_id);
		return(DBAPI::get_row_value('tokens', $col_val_array, 'token'));
	}
	
	public static function token_expired_date($token) {
		$col_val_array = array('token' => $token);
		return(DBAPI::get_row_value('tokens', $col_val_array, 'expired'));
	}
	
	public static function is_token_exist($token) {
		$col_val_array = array('token' => $token);
		$user_id = DBAPI::get_row_value('users', $col_val_array, 'user_id');
		return ($user_id != '')?TRUE:FALSE;
	}
	
	// $credntials = array('username'=>'myemail@email.com', 'password'=>'mypass')
	public static function is_credentials_exist($credntials) {
		$col_val_array = $credntials;
		$user_id = DBAPI::get_row_value('users', $col_val_array, 'user_id');
		return ($user_id != null)?TRUE:FALSE;
	}
	
	public static function user_profile_by_id($user_id) {
		$col_val_array = array('user_id'=>$user_id);
		$cols = 'ALL';
		$user_profile = DBAPI::get_row_values('users', $col_val_array, $cols);
		return $user_profile;
	}
	
}

?>