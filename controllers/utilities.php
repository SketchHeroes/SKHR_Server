<?php
class UTILS {
	
	public static function send_mail_via_gmail_account($code, $email) {
		echo 'Verification Mail to: '.$email.' with code: '.$code."/n";
		return;
		require_once "Mail.php";
		 
		$from = "<skhrServe@gmail.com>";
		$to = "<".$email.">";
		$subject = "Hi!";
		$body = "Welcome to SketchHeroes.\n\ We are very glad to have you with us.\n\
				To cpmplete the registration procedure, please insert the verification code
				to the application: ".$code;
		
		$host = "ssl://smtp.gmail.com";
		$port = "465";
		$username = "ronen@sketchheroes.com";
		$password = "skhr^d78-$";
		
		$headers = array ('From' => $from,
				'To' => $to,
				'Subject' => $subject);
		$smtp = Mail::factory('smtp',
				array ('host' => $host,
						'port' => $port,
						'auth' => true,
						'username' => $username,
						'password' => $password));
		
		$mail = $smtp->send($to, $headers, $body);
		
// 		if (PEAR::isError($mail)) {
// 			throw new SKHR_Exception('Send verification mail error: '.$mail->getMessage(), ExitCode::SEND_MAIL_FAILED);
// 		} else {
// 			return true;
// 		}
		return true;
	}
	
	public static function genRandomString($length = 8) {
// 		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~!@#$%^&*()_+\|]}[{;:<,>.?/';
		$characters = '0123456789';
		$string = '';
		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters))];
		}
		return $string;
	}
	
	
}

?>