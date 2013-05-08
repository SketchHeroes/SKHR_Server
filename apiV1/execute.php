<?php
foreach (glob("controllers/*/*.php") as $filename) {require_once $filename;}
// require_once

class Executor {
	
	private $exit_code = 0;
	private $body = 'Empty';
	private $content_type = 'text/html';
	private $additional_info = '';
	
	public function __construct($rest_request) 
	{
		$rsrc = $rest_request->getResource();
		$http_method = $rest_request->getMethod();
		$api_ver = $rsrc[1];
		$resource = $rsrc[2];
		$command = $rsrc[3];
		$data = $rest_request->getRequestVars();
		
		$method_name = $http_method.'_'.$resource.'_'.$command;
		
		try 
		{
			call_user_func('self::'.$method_name, $data);
		} 
		catch (SKHR_Exception $she) 
		{
			$this->exit_code = $she->getCode();
			$this->body = '';
			$this->additional_info = $she->getMessage();
			$this->content_type = 'text/html';
		} 
		catch (Exception $e) 
		{
			$this->exit_code = -1;
			$this->body = '';
			$this->additional_info = $e->getMessage();
			$this->content_type = 'text/html';
		}
		
	}
	
	public function getResults() 
	{
		return(
				array(
						$this->exit_code,
						$this->body,
						$this->content_type,
						$this->additional_info
		));
	}
	
	private function post_user_register($data) 
	{
		$ddata = json_decode($data, TRUE);
		//verify the data
		
		$ur = new UserRegister($ddata);
		$this->exit_code = $ur->result['code'];
		$this->body= json_encode($ur->result['data']);
		$this->content_type = 'application/json';
	}
	
	private function post_user_verify($data)
	{
		$ddata = json_decode($data, TRUE);
		//verify the data
	
		$ur = new UserVerifiy($ddata);
		$this->exit_code = $ur->result['code'];
		$this->body= json_encode($ur->result['data']);
		$this->content_type = 'application/json';
	}
	
	
	private function post_user_login($data) 
	{
		$ddata = json_decode($data, TRUE);
		$ul = new UserLogin($ddata);
		$this->exit_code = $ul->result['code'];
		$this->body= json_encode($ul->result['data']);
		$this->content_type = 'application/json';
	}
	
	
}


?>