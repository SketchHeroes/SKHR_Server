<?php
class Messages 
{
	// User Registration:
	const REGISTRATION_SUCCEDED = 100;
	const UNKNOWN_ACCOUNT_TYPE = 101; 
	const CREDENTIALS_ALREADY_IN_USE = 102;
	const REGISTRATION_FAILED = 103; 
	const REGISTRATION_TOKEN_FAILED = 104;
	const CREDENTIALS_INVALID_EMAIL = 105;
	const CREDENTIALS_INVALID_FB_ID = 106;
	const CREDENTIALS_EMAIL_IS_MANDATORY = 107;
	const CREDENTIALS_FB_ID_IS_MANDATORY = 108;
	
	// Field Validation messages
	const INVALID_FIELD_VALUE = 120;
	
	// Token
	const TOKEN_INSERTION_FAILED = 121;
	
	// MYSQLi failures
	const FAILED_TO_CONNECT_DB = 130;
	const PREPARE_QUARY_FAILED = 131;
	
	public static $Exceptions = array(
		// User Registration:
			self::UNKNOWN_ACCOUNT_TYPE => 'Unknown registration type. Should be 0 for skhr or 1 for facebook',
			self::CREDENTIALS_ALREADY_IN_USE => 'The supplied credentials are already used by an exist user',
			self::REGISTRATION_FAILED => 'Insert new user data row failed',
			self::REGISTRATION_TOKEN_FAILED => 'Insert register token failed',		
			self::CREDENTIALS_INVALID_EMAIL => 'Can\'t register user without valid Email address',
			self::CREDENTIALS_INVALID_FB_ID => 'Can\'t register user without valid FB id ',	
			self::CREDENTIALS_EMAIL_IS_MANDATORY => 'Email is a mandatory field for registration',
			self::CREDENTIALS_FB_ID_IS_MANDATORY => 'Facebook id is a mandatory field for registration',
		// Fields Validation messages:
			self::INVALID_FIELD_VALUE => 'Field value is invalid. Reason: Out of range or Wrong data type.',
		// Token
			self::TOKEN_INSERTION_FAILED => 'Failed to insert token to the db',
		// MYSQLi failures
			self::FAILED_TO_CONNECT_DB => 'Failed to connect to mysql db',
			self::PREPARE_QUARY_FAILED => 'Failed to prepare quary'
	);
	
	public static $Success = array(
			self::REGISTRATION_SUCCEDED => 'Registration succeded'
	);
	
}

class SKHR_Exception extends Exception {
	public function __construct ($message = null, $code = null, $previous = null) {
		$this->message = Messages::$Exceptions[$code].':'. "\n" .$message;
		$this->code = $code;
	}
}

?>