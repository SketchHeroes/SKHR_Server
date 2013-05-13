<?php
foreach (glob("controllers/*.php") as $filename) {require_once $filename;}
require_once 'interface_data_manager.php';

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
	
		$ini = ($ddata['type'] == 'skhr') ? 'post_user_register.ini' : 'post_user_register_facebook.ini';
		DataManager::process_data($ddata, $ini);
		
		$ur = new UserRegister($ddata);
		$this->exit_code = $ur->result['code'];
		$this->body= json_encode($ur->result['data']);
		$this->content_type = 'application/json';
	}
	
	private function post_user_verify($data)
	{
		$ddata = json_decode($data, TRUE);
		
		$ini = 'post_user_verify.ini';
		DataManager::process_data($ddata, $ini);
		$ur = new UserVerifiy($ddata);
		$ini = 'post_user_verify_response.ini';
		DataManager::process_data($ur->result['data'], $ini);
		
		$this->exit_code = $ur->result['code'];
		$this->body= json_encode($ur->result['data']);
		$this->content_type = 'application/json';
	}
	
	
	private function post_user_login($data) 
	{
		$ddata = json_decode($data, TRUE);
		
		$ini = ($ddata['type'] == 'skhr') ? 'post_user_login.ini' : 'post_user_login_facebook.ini';
		DataManager::process_data($ddata, $ini);
		$ul = new UserLogin($ddata);
		$ini = ($ddata['type'] == 'skhr') ? 'post_user_login_response.ini' : 'post_user_login_facebook_response.ini';
		DataManager::process_data($ul->result['data'], $ini);
		
		$this->exit_code = $ul->result['code'];
		$this->body= json_encode($ul->result['data']);
		$this->content_type = 'application/json';
	}
	
	
}


?>