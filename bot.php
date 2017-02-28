<?php
ini_set("log_errors", 1);
ini_set("error_log", "/var/log/banjobot.log");

chdir("/home/pi/banjobot");			
if (!(is_file(getcwd().'/settings.txt'))) die('settings.txt does not exist');
$ini = parse_ini_file(getcwd().'/settings.txt');

require_once('vendor/autoload.php');
$loop = React\EventLoop\Factory::create();

$client = new Slack\RealTimeClient($loop);
$client->setToken($ini['slack_token']);

$client->on('message', function ($data) use ($client) {
    error_log("Someone typed a message: ".$data['text']);

	if (substr($data['text'], 1, 4) == 'http' && in_array(strtolower(substr($data['text'], -5, -1)), array('.jpg', '.png', '.gif'))) {
		$client->getChannelGroupOrDMByID($data['channel'])->then(function ($channel) use ($client, $data) {
			/*
            $msg = "Oh that's an interesting image..";
			$message = $client->getMessageBuilder()
						->setText($msg)
						->setChannel($channel)
						->create();
			$client->postMessage($message);
            */

			$file = file_get_contents(substr($data['text'], 1, -1));
			$filename = "/tmp/bild1-".date('Y-m-d_h:i:s').".jpg";
			$filename2 = "/tmp/bild2-".date('Y-m-d_h:i:s').".jpg";
			file_put_contents($filename, $file);
			error_log("\tSize: ".filesize_formatted($filename));
			error_log("Banjofying: ".substr($data['text'], 1, -1));
			chdir("/home/pi/go/src/github.com/zikes/chrisify");
			shell_exec("./chrisify --faces /home/pi/banjobot/banjoboys $filename > $filename2");
			error_log("\tDropboxing $filename");
			shell_exec("php /home/pi/plantbot/dropboxUploadFile.php $filename2");
			$url = shell_exec("php /home/pi/plantbot/dropboxShareLink.php $filename2");
            if ($url) {
                unlink($filename);
                unlink($filename2);
            }
			
			error_log("\tGoogle-urling..");
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key=AIzaSyDpJpbZ2s3Gftdephw1HSB1Mk00PLzx_I0');
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode(array("longUrl"=>$url)));
			curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json"));
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			$result = curl_exec($ch);
			$arr = json_decode($result, true);
			if ($arr['id']) {
				$shortUrl = $arr['id'];
			} else {
				error_log('Url-shortening FAILED');
			}
			error_log("\tGot this url for $filename: $shortUrl");
			
			error_log("\tSaving to db");
			$connection = mysqli_connect('localhost', 'root', '');
			if ($connection === false) {
				die (mysqli_error());
			} elseif ($shortUrl) {
				mysqli_select_db($connection, 'banjobot');
				$channelName = $channel->getName();
				$query = "insert into links set url='$shortUrl', shared=0, channel='$channelName';";
				$result = mysqli_query($connection, $query);
				if (!$result) {
					die(mysqli_error($connection));
				}
			}
    	});
	} elseif (strpos('ping', $data['text']) === 0) {
		$client->getChannelGroupOrDMByID($data['channel'])->then(function ($channel) use ($client, $data) {
			error_log("Ping? Pong!");
			$message = $client->getMessageBuilder()
						->setText('Pong motherfucker!')
						->setChannel($channel)
						->create();
			$client->postMessage($message);
		});
	} elseif (strpos('https://goo.gl/', $data['text']) === false && strpos("Oh that's an interesting image..", $data['text']) === false && strpos('Pong motherfucker!', $data['text']) === false && !empty($data['text'])) {
		$client->getChannelGroupOrDMByID($data['channel'])->then(function ($channel) use ($client, $data) {
			$connection = mysqli_connect('localhost', 'root', '');
			if ($connection === false) {
				die (mysqli_error());
			} else {
				mysqli_select_db($connection, 'banjobot');
				$query = "select id, url from links where channel='".$channel->getName()."' and shared=0 order by id asc limit 1";
				$result = mysqli_query($connection, $query);
				if ($result && $result->num_rows > 0 ) {
					$row = mysqli_fetch_assoc($result);
					echo $row['url']."\n";
					error_log("Slacking the slack with ".$row['url']);
					$message = $client->getMessageBuilder()
						->setText($row['url'])
						->setChannel($channel)
						->create();
					$client->postMessage($message);
					$query = "update links set shared=1 where id=".$row['id'];
					$result = mysqli_query($connection, $query);
				}
			}
		});
	}
});

function filesize_formatted($path)
{
    $size = filesize($path);
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

$client->connect()->then(function () {
    error_log("Connected!");
});

$loop->run();

