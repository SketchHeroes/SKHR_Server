<?php
require_once("rest_api.php");
require_once("procedures/user.php");


class APIExecutor {
	
	public function __construct($rest_request) {
		echo "request vars:" .$rest_request->getRequestVars(). PHP_EOL;
		echo "http accept:" .$rest_request->getHttpAccept(). PHP_EOL;
		echo "request resource:" .print_r($rest_request->getResource(),true). PHP_EOL;
		echo "request method:" .$rest_request->getMethod(). PHP_EOL;
		echo "request data:   " . print_r($rest_request->getData(), true). PHP_EOL;
		
		$rrr = $rest_request->getResource();
		$http_method = $rest_request->getMethod();
		$api_ver = $rrr[1];
		$resource = $rrr[2];
		$command = $rrr[3];
		$data = $rest_request->getRequestVars();
		
		switch ($api_ver) {
			case 'v0.1': $apiVer = 'V01';
			break;
			case 'v0.2': $apiVer = 'V02';
			break;
			case 'v1.0': $apiVer = 'V10';
			break;
			default:
				throw new SKHR_Exception('Unsupported API: '.$api_ver, 140);
			break;
		}
		$obj = 'APIExecutor'.$apiVer;
		$method_name = $http_method.'_'.$resource.'_'.$command;
		echo 'call_user_method($method_name, $obj, $data): '.$method_name. '   '.$obj.'    '.$data. "\n";
		call_user_func($obj.'::'.$method_name, $data);
	}
}
	
class APIExecutorV01 extends APIExecutor {
	
	public static function post_user_register($data) {
		$ddata = json_decode($data,TRUE);
		$ur = new UserRegister($ddata);
		echo 'Registration Result: '.print_r(json_encode($ur->result)). "\n";
	}
	
	public static function post_user_login($data) {
		
	}
	
	
}


?>