<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/chrisbot-slack.log");

if (isset($_POST['token']) && in_array($_POST['token'], array('Jyd2sdNfwr1ZuI5E5VtLIxlz'))) {

	$user = $_POST['user_name'];
	chdir('/home/pi/chrisbot');
	$comArr = explode(' ', $_POST['text']);
	
	switch ($comArr[0]) {
		case 'destroy':
			postOk("Destruction coming up, Sir! (in about 20 seconds)");
			$file = file_get_contents($comArr[1]);
			$filename = "/tmp/bild1-".date('Y-m-d_h:i:s').".jpg";
			$filename2 = "/tmp/bild2-".date('Y-m-d_h:i:s').".jpg";
			file_put_contents($filename, $file);
			chdir("/home/pi/go/src/github.com/zikes/chrisify");
			if (isset($comArr[2]) && $comArr[2] == 'adnan') {
				$facesPath = "/home/pi/chrisbot/adnan";
			} elseif (isset($comArr[2]) && $comArr[2] == 'presidents') {
				$facesPath = "/home/pi/chrisbot/presidents";
			} else {
				$facesPath = "/home/pi/chrisbot/banjoboys";
			}
			shell_exec("./chrisify --faces $facesPath $filename > $filename2");
			shell_exec("php /home/pi/plantbot/dropboxUploadFile.php $filename2");
			$link = shell_exec("php /home/pi/plantbot/dropboxShareLink.php $filename2");
			shell_exec("php /home/pi/chrisbot/postToSlack.php 'Boo-yah!' $link");

			//shell_exec("php postToSlack.php 'Recent photo coming right up, sir $user!'");	
			//shell_exec('php postMostRecentPhotoToSlack.php');
		break;
		case 'status':
			postOk();	
			//shell_exec('php postMostRecentPhotoToSlack.php');
			//shell_exec("php postToSlack.php 'Here is some stats for you, $user!'");	
			//shell_exec('php plantDataToSlack.php');	
		break;
		case 'admin':
			if ($user == 'fredrik') {
				echo " admin-function for you!";	
			} else {
				echo " no no, you did not say the magic word!";
			}
		break;
		default:
			//echo "Tjenare $user ({$_POST['text']})!, testa 'status' eller 'photo' istälet.";
		break;
	}
	error_log(serialize($_POST));
} else {
	header("HTTP/1.0 404 Not Found");
}

function postOk($msg = false) {
	ignore_user_abort(true);
	if (!$msg) $msg = "Checking, please wait...";
	ob_start();
	echo('{"response_type": "in_channel", "text": "'.$msg.'"}');
	header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
	header("Content-Type: application/json");
	header('Content-Length: '.ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();
}
