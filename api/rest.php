<?php
class RestUtils
{
	const TAG = 'RestUtils:';
	const APIV = 'v1';
	public static function processRequest()
	{
		// get our verb
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
		$return_obj		= new RestRequest();
		// we'll store our data here
		$data			= array();
		// Check api request structure:
		$request_resource = $return_obj->getResource();
		if ($request_resource[0] != 'api') {
			self::sendResponse(400,'API request should start with: api/');
		}
		if ($request_resource[1] != self::APIV) {
			self::sendResponse(400,'API request should use api version: '.self::APIV);
		}
		switch ($request_method)
		{
			case 'get':
				$data = json_encode($_GET);
				break;
			case 'post':
				$_POST = file_get_contents("php://input");
				$data = $_POST;
				break;
			case 'put':
				self::sendResponse(501,'PUT method is not implemented:  ' . $request_method);
				parse_str(file_get_contents('php://input'), $put_vars);
				$data = $put_vars;
				break;
			default:
				self::sendResponse(405,'Unknown method:  ' . $request_method);
		}
	
		$return_obj->setMethod($request_method);
		$return_obj->setRequestVars($data);
	
		if(isset($data)) {
			$return_obj->setData(json_decode($data));
		}
		return $return_obj;
	}
	
	public static function sendResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		$status_header = 'HTTP/1.1 ' . $status . ' ' . self::getStatusCodeMessage($status);
		header($status_header);
		header('Content-type: ' . $content_type);
	
		if($body != '') {
			echo $body;
			exit;
		}
		else {
			// servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
			$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];
			echo self::getStatusCodeMessage($status). "\n" . $signature;
			exit;
		}
	}
	
	public static function getStatusCodeMessage($status)
	{
		$codes = parse_ini_file("status_messages.ini");
		return (isset($codes[$status])) ? $codes[$status] : '';
	}
}

class RestRequest
{
	private $request_vars;
	private $data;
	private $http_accept;
	private $method;
	private $resource;

	public function __construct()
	{
		$this->request_vars		= array();
		$this->data				= '';
		$this->http_accept		= (strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'xml';
		$this->method			= 'get';
		$this->resource         = array_values(array_filter(explode('/', $_SERVER['REDIRECT_URL']), 'strlen'));
	}
	
	public function setData($data)
	{
		$this->data = $data;
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function setRequestVars($request_vars)
	{
		$this->request_vars = $request_vars;
	}
	
	public function getData()
	{
		return $this->data;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getHttpAccept()
	{
		return $this->http_accept;
	}

	public function getRequestVars()
	{
		return $this->request_vars;
	}
	
	public function getResource()
	{
		return $this->resource;
	}
}

?>