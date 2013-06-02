<?php
foreach (glob("controllers/*.php") as $filename) {require_once $filename;}
require_once 'interface_data_manager.php';

class Executor {
	
	private $exit_code = 0;
	private $body = 'Empty';
	private $content_type = 'text/html';
	private $additional_info = '';
	
	private $type = 'skhr';
	private $user_class = array('skhr' => 'SKHR', 'facebook'=>'FB', 'google'=>'GOOGLE');
	private $requests_path = 'apiV1/request_data/';
	private $responses_path = 'apiV1/response_data/';
	private $user_ini_suffix = array('skhr' => '', 'facebook'=>'_facebook', 'google'=>'_google');
	
	public function __construct($rest_request) 
	{
		$rsrc = $rest_request->getResource();
		$http_method = $rest_request->getMethod();
		$api_ver = $rsrc[1];
		$resource = $rsrc[2];
		$command = $rsrc[3];
		$data = $rest_request->getRequestVars();
		
		$method_name = $http_method.'_'.$resource.'_'.$command;
		
		$ddata = json_decode($data, TRUE);
		$this->type = $ddata['type'];
		try 
		{
			call_user_func('self::'.$method_name, $ddata);
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
	
	public function setResults($res, $ini)
	{
		if (gettype($res['data']) == gettype(array())) {
			if ($res['data'] != array()) {
				$this->body= json_encode(DataManager::process_data($res['data'], $this->responses_path.$ini));
			}
		}
		$this->exit_code = $res['code'];
		$this->content_type = 'application/json';
		$this->additional_info = $res['additional_info'];
	}
	
	private function post_user_register($data) 
	{
		$ini = 'post_user_register'.$this->user_ini_suffix[$this->type].'.ini';
		$user_class = 'User'.$this->user_class[$this->type];
		$user = new $user_class(DataManager::process_data($data, $this->requests_path.$ini));
		$user->register();
		$res = $user->get_result();
		$this->setResults($res, $ini);
	}
	
	private function post_user_verify($data)
	{
		$ini = 'post_user_verify'.$this->user_ini_suffix[$this->type].'.ini';
		$user_class = 'User'.$this->user_class[$this->type];
		$user = new $user_class(DataManager::process_data($data, $this->requests_path.$ini));
		$user->verify();
		$res = $user->get_result();
		$this->setResults($res, $ini);
	}
	
	
	private function post_user_login($data) 
	{
		$ini = 'post_user_login'.$this->user_ini_suffix[$this->type].'.ini';
		$user_class = 'User'.$this->user_class[$this->type];
		$user = new $user_class(DataManager::process_data($data, $this->requests_path.$ini));
		$user->login();
		$res = $user->get_result();
		$this->setResults($res, $ini);
	}
	
	
}


?>