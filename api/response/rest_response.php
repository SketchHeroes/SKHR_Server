<?php

class SendResponse 
{
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
		$codes = parse_ini_file("api/response_data/status_messages.ini");
		return (isset($codes[$status])) ? $codes[$status] : '';
	}
}

class RestResponse
{
	private $data;
	private $method;
	private $device;
	private $format;
	public function __construct()
	{
		$this->data		= '';
		$this->method	= 'get';
		$this->device   = '';
		$this->format   = 'json';
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