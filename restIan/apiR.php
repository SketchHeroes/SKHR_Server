<?php
require_once("rest_skelaton.php");
$data = RestUtils::processRequest();
$x = $data->getMethod();
echo 'Method is:', $x;
echo 'HTTP_REQUEST:', $_SERVER['HTTP_REQUEST'];
echo 'REQUEST_File:', $_SERVER['REQUEST_FILENAME'];
switch($data->getMethod)
{
	// this is a request for all users, not one in particular
	case 'get':
		$user_list = array(
    "name" => "rrrrronnnennn",
    "password" => "1234566766666",
); // assume this returns an array

		if($data->getHttpAccept == 'json')
		{
			RestUtils::sendResponse(200, json_encode($user_list), 'application/json');
		}
		else if ($data->getHttpAccept == 'xml')
		{
			// using the XML_SERIALIZER Pear Package
			$options = array
			(
					'indent' => '     ',
					'addDecl' => false,
					'rootName' => $fc->getAction(),
					XML_SERIALIZER_OPTION_RETURN_RESULT => true
			);
			$serializer = new XML_Serializer($options);

			RestUtils::sendResponse(200, $serializer->serialize($user_list), 'application/xml');
		}

		break;
		// new user create
	case 'post':
		$user = new User();
		$user->setFirstName($data->getData()->first_name);  // just for example, this should be done cleaner
		// and so on...
		$user->save();

		// just send the new ID as the body
		RestUtils::sendResponse(201, $user->getId());
		break;
}
