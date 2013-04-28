<?php
require_once("api/response/rest.php");
require_once("api/request/rest.php");
require_once("procedures/user.php");


class APIExecutor {
	public $response = array(
			'status_code' => 200,
			'body' => '{}',
			'content_type' => 'application/json'
	);
	
	public function __construct($rest_request) {
// 		echo "request vars:" .$rest_request->getRequestVars(). PHP_EOL;
// 		echo "http accept:" .$rest_request->getHttpAccept(). PHP_EOL;
// 		echo "request resource:" .print_r($rest_request->getResource(),true). PHP_EOL;
// 		echo "request method:" .$rest_request->getMethod(). PHP_EOL;
// 		echo "request data:   " . print_r($rest_request->getData(), true). PHP_EOL;
		
		$rsrc = $rest_request->getResource();
		$http_method = $rest_request->getMethod();
		$api_ver = $rsrc[1];
		$resource = $rsrc[2];
		$command = $rsrc[3];
		$data = $rest_request->getRequestVars();
		
		switch ($api_ver) {
			case 'v1': $apiVer = 'V1';
			break;
			case 'v2': $apiVer = 'V2';
			break;
			default: RestUtils::sendResponse(400,'Unsupported api version: '.$api_ver);
			break;
		}
		
		$obj = 'APIExecutor'.$apiVer;
		$method_name = $http_method.'_'.$resource.'_'.$command;
		$this -> response = call_user_func($obj.'::'.$method_name, $data);
		
		RestUtils::sendResponse($this->response['status_code'], $this->response['body'], $this->response['content_type']);
	}
	
}
	
class APIExecutorV1 extends APIExecutor {
	
	public static function post_user_register($data) {
		$ddata = json_decode($data, TRUE);
		try {
			$ur = new UserRegister($ddata);
			$body = json_encode($ur->result);
			$status = 200;
			$content_type = 'application/json';
		} catch (Exception $e) {
			$status = SKHR_Exception::exception2Status($e->getCode());
			$body = $e->getMessage();
			$content_type = 'text/html';
			echo 'Exception Catched: '.$e;
		}
		
		return array('status_code' => $status, 'body' => $body, 'content_type' => $content_type);
		
	}
	
	public static function post_user_login($data) {
		$ddata = json_decode($data, TRUE);
		try {
			$ur = new UserLogin($ddata);
			$body = json_encode($ur->result);
			$status = 200;
			$content_type = 'application/json';
		} catch (Exception $e) {
			$status = SKHR_Exception::exception2Status($e->getCode());
			$body = $e->getMessage();
			$content_type = 'text/html';
			echo 'Exception Catched: '.$e;
		}
		
		return array('status_code' => $status, 'body' => $body, 'content_type' => $content_type);
	}
	
	
}


?>