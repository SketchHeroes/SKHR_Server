
<?php
// TODO
// - return token for verification request
// - complete data base scripts for more tables
// - complete data base api class
// - complete verify class
// - convert the user classes to one userRutiens class?
// - new excptions / exit-codes  list

// At the controllers part - use third party


// phpinfo();


// Client side GET:
// curl -v -H "Accept: application/json" -H "Content-type: application/json" -X GET 'http://localhost:80/rest/?request=users&ddd=123&6666=dfgh'
// Client side POST:
// curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d @b.json http://localhost:80/rest/api/user/123/

require_once("rest_request.php");
require_once("rest_response.php");
require_once("exceptions.php");

class ServerGate 
{
	public function __construct()
	{
		
		list($exit_code, $body, $content_type, $additional_info) = array(-1, 'Empty', 'text/html', 'No Additional Info');
		
		$rest_request = AcceptRequest::processRequest();
		$api_ver = 'V1';
		try {
			require_once("api".$api_ver."/execute.php");
		} catch (Exception $e) {
			$exit_code = Messages::UNSUPPORTED_API_VERSION;
			$body = 'Unsupported api version: '.$api_ver;
			$additional_info = $e->getMessage();
			$content_type = 'text/html';
		}
		
		$executor = new Executor($rest_request);
		list($exit_code, $body, $content_type, $additional_info) = $executor->getResults();
		
		echo 'exit_code: '.$exit_code."\n";
		echo 'body: '.$body."\n";
		echo 'content_type: '.$content_type."\n";
		echo 'additional_info: '.$additional_info."\n";
		
		SendResponse::send($exit_code, $body, $content_type, $additional_info);
	}
}

$SG = new ServerGate();


?>

