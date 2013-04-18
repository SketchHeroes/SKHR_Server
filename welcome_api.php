
<?php
// Client side GET:
// curl -v -H "Accept: application/json" -H "Content-type: application/json" -X GET 'http://localhost:80/rest/?request=users&ddd=123&6666=dfgh'
// Client side POST:
// curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d @b.json http://localhost:80/rest/api/user/123/

require_once("api/rest_api.php");
require_once 'api/api_actions.php';
$rest_request = RestUtils::processRequest();
$api_action_result = new APIExecutor($rest_request);


// echo '--------------------------------------------- TRY ---------------------------------------------', "\n";

// require_once("user.php");
// require_once("tables.php");

// '{
// 	"id": 3020001,
//  	"name": "yuval eziger",
//  	"first_name": "el",
// 	"last_name": "33yubalxl",
// 	"fb_link": "****ook.com/yner.ddo",
// 	"username": "jenifer.byebye",
// 	"birthday": "21/11/1953",
// 	"gender": "male ",
// 	"email": "ee@y4458.com",
// 	"timezone": "0",
// 	"locale": "en_US",
// 	"verified": "true",
// 	"type": "skhr",
// 	"password": "aadfdh4 r 4r 4fr5f 5f5f 5gf5g$%^^&^"
// }';
// $data = json_decode($json_user,TRUE);
// $ur = new UserRegister($data);
// echo 'Registration Result: '.print_r(json_encode($ur->result)). "\n";



//echo 'Result: '.$userRegister->result. "\n";
?>

