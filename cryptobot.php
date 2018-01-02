<?php

require_once('vendor/autoload.php');
date_default_timezone_set('UTC');
$coin = 'LTC';
    $timestamp_day = strtotime('-1 day', time());
    $timestamp_week = strtotime('-1 week', time());
    $timestamp_month = strtotime('-1 month', time());
    
    $current = currentPrice($coin);
    
    $historical_day = priceForTimestamp($coin, $timestamp_day);
    $historical_week = priceForTimestamp($coin, $timestamp_week);
    $historical_month = priceForTimestamp($coin, $timestamp_month);
var_dump($historical_day->USD);    
    $change_day = ($current->USD/$historical_day->USD - 1) * 100;
    $change_week = ($current->USD/$historical_week->USD - 1) * 100;
    $change_month = ($current->USD/$historical_month->USD - 1) * 100;
    
    $change_day = formatPercentage($change_day);
    $change_week = formatPercentage($change_week);
    $change_month = formatPercentage($change_month);
    
//    $attachmentBuilder = new AttachmentBuilder();
 var_dump($change_day);   
    // 🏦
    // 💸
    
    $color_plus = '#9bf442';
    $color_minus = '#f45c41';
    
    $day_color = $change_day[0] == '+' ? $color_plus : $color_minus;
    $week_color = $change_week[0] == '+' ? $color_plus : $color_minus;
    $month_color = $change_month[0] == '+' ? $color_plus : $color_minus;
 /*   
    return $client->getMessageBuilder()
                    ->setText(sprintf('💸 Current *%s* price $%.3f (%.6f BTC)', $coin, $current->USD, $current->BTC))
                    ->setChannel($channel)
                    ->addAttachment(new Attachment('Past 24h', "$change_day%", 'build failed', $day_color))
                    ->addAttachment(new Attachment('Past week', "$change_week%", 'build failed', $week_color))
                        ->addAttachment(new Attachment('Past month', "$change_month%", 'build failed', $month_color))
                    ->create();
*/
 die;

function priceForTimestamp($coin, $timestamp) {
    $response = file_get_contents('https://min-api.cryptocompare.com/data/pricehistorical?fsym=' . $coin . '&tsyms=BTC,USD,EUR&ts=' . $timestamp . '&extraParams=cryptobot');
    $json = json_decode($response);
    
    return reset($json);
}

function currentPrice($coin) {
    $response = file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=' . $coin . '&tsyms=BTC,USD,EUR');
    $json = json_decode($response);
    
    return $json;
}

function formatPercentage($percentageFloat) {
    $percentageString = sprintf('%.2f', $percentageFloat);
    
    if ($percentageString[0] != '-') {
        $percentageString = "+$percentageString";
    }
    
    return $percentageString;
}

//responseForCoin('GNT');
//exit;

$coinlistFile = file_get_contents('coinlist.json');
$coinlistJson = json_decode($coinlistFile);

$coinKeys = array();

foreach($coinlistJson as $key => $value) {
    array_push($coinKeys, $key);
}

$loop = \React\EventLoop\Factory::create();

$client = new Slack\RealTimeClient($loop);
$client->setToken('xoxb-186422746422-cHNgnsYDJR14Q3FbB2Ll99Qh');

$message = '';

$client->on('message', function ($data) use ($client) {
    global $coinKeys, $message;
    
    $message = $data['text'];
    $channelName = $data['channel'];
    
    if (in_array($message, $coinKeys)) {
        $client->getChannelById($channelName)->then(function (\Slack\Channel $channel) use ($client) {
            global $message, $client;
            
            $message = buildMessageForCoin($message, $channel, $client);
            $client->postMessage($message);
        });
    }
});

$client->connect()->then(function () {
    echo "Connected!\n";
});

$loop->run();

?>

