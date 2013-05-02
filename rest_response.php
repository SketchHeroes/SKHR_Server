<?php

class SendResponse 
{
	public static function send($exit_code = 0, $data = '', $content_type = 'application/json', $additional_info = '')
	{
		$status_codes = parse_ini_file("exceptions2Status.ini");
		$status = (isset($status_codes[$exit_code])) ? $status_codes[$exit_code] : 500;
		$status_header = 'HTTP/1.1 ' . $status . ' ' . self::getStatusCodeMessage($status);
		header($status_header);
		header('Content-type: ' . $content_type);
		
		switch($content_type) {
			case 'application/json':
				$body = json_encode($data);
				break;
			case 'application/xml':
				break;
			default:
				$body = json_encode($data);
		}
		
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

?>