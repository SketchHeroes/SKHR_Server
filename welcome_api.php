
<?php
// Client side GET:
// curl -v -H "Accept: application/json" -H "Content-type: application/json" -X GET 'http://localhost:80/rest/?request=users&ddd=123&6666=dfgh'
// Client side POST:
// curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d @b.json http://localhost:80/rest/api/user/123/

echo "\n" . print_r(parse_ini_file('./DataBase/tables/user.ini',true)) . "\n";
require_once("api/rest.php");
require_once 'api/execute_request.php';

try {
	$rest_request = RestUtils::processRequest();
	$execute_result = new APIExecutor($rest_request);
} catch (Exception $e) {
// 	send response with right status, status message and exception code and message
}

?>

