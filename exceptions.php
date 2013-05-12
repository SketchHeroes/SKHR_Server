<?php
abstract class StatusCode
{
	const CONTINUE_STATUS = 100;
	const SWITCHING_PROTOCOLS = 101;
	const OK = 200;
	const CREATED = 201;
	const ACCEPTED = 202;
	const NON_AUTHORITATIVE_INFORMATION = 203;
	const NO_CONTENT = 204;
	const RESET_CONTENT = 205;
	const PARTIAL_CONTENT = 206;
	const MULTIPLE_CHOICES = 300;
	const MOVED_PERMANENTLY = 301;
	const FOUND = 302;
	const SEE_OTHER = 303;
	const NOT_MODIFIED = 304;
	const USE_PROXY = 305;
	const UNUSED = 306;
	const TEMPORARY_REDIRECT = 307;
	const BAD_REQUEST = 400;
	const UNAUTHORIZED = 401;
	const PAYMENT_REQUIRED = 402;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const NOT_ACCEPTABLE = 406;
	const PROXY_AUTHENTICATION_REQUIRED = 407; 
	const REQUEST_TIMEOUT = 408;
	const CONFLICT = 409;
	const GONE = 410;
	const LENGTH_REQUIRED = 411;
	const PRECONDITION_FAILED = 412;
	const REQUEST_ENTITY_TOO_LARGE = 413;
	const REQUEST_URI_TOO_LONG = 414;
	const UNSUPPORTED_MEDIA_TYPE = 415;
	const 416 => 'Requested Range Not Satisfiable',
	const 417 => 'Expectation Failed',
	const 500 => 'Internal Server Error',
		const 501 => 'Not Implemented',
	const 502 => 'Bad Gateway',
	const 		503 => 'Service Unavailable',
	const 		504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
	
	public static $status_messages = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Unused',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
	);
	
}

abstract class ExitCodes 
{
	// User Registration:
	const REGISTRATION_SUCCEDED_CODE = 100;
	
	const UNKNOWN_ACCOUNT_TYPE = 101; 
	const CREDENTIALS_ALREADY_IN_USE = 102;
	const REGISTRATION_FAILED = 103; 
	const REGISTRATION_TOKEN_FAILED = 104;
	const CREDENTIALS_INVALID_EMAIL = 105;
	const CREDENTIALS_INVALID_FB_ID = 106;
	const CREDENTIALS_EMAIL_IS_MANDATORY = 107;
	const CREDENTIALS_FB_ID_IS_MANDATORY = 108;
	
	const LOGIN_SUCCEDED = 110;
	const LOGIN_FAILED = 111;
	// Field Validation messages
	const INVALID_FIELD_VALUE = 120;
	const MANDATORY_FIELD_MISSING = 121;
	// Token
	const TOKEN_INSERTION_FAILED = 122;
	const TOKEN_UPDATE_FAILED = 123;
	// MYSQLi failures
	const FAILED_TO_CONNECT_DB = 130;
	const PREPARE_QUARY_FAILED = 131;
	
	const UNSUPPORTED_API_VERSION = 140;
}

abstract class ExitMessages {
	
	public static $exceptions = array(
		// User Registration:
			self::REGISTRATION_SUCCEDED => 'Registration succeded',
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
			self::PREPARE_QUARY_FAILED => 'Failed to prepare quary',
			self::TOKEN_UPDATE_FAILED => 'Failed to update token'
	);
}

class SKHR_Exception extends Exception {
	
	public function __construct ($message = null, $code = null, $previous = null) {
		$this->message = Messages::$exceptions[$code].':'. "\n" .$message;
		$this->code = $code;
	}
	
	public static function exception2Status ($code) {
		$a = array(
				100 => 201,
				101 => 400,
				102 => 400,
				103 => 500,
				104 => 500,
				105 => 206,
				106 => 206,
				107 => 206,
				108 => 206,
				120 => 400,
				121 => 500,
				130 => 500,
				131 => 500
		);	
		return $a[$code];
	}
}

?>