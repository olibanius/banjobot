<?php
$coin = strtoupper($argv[1]);
$uri = "https://min-api.cryptocompare.com/data/price?fsym=$coin&tsyms=USD";
$stuff = file_get_contents($uri);
$arr = json_decode($stuff, true);
$rateNow = $arr['USD'];

date_default_timezone_set('Europe/Stockholm');
$timestamp= strtotime('-1 week', time());
$response = file_get_contents('https://min-api.cryptocompare.com/data/pricehistorical?fsym=' . $coin . '&tsyms=BTC,USD,EUR&ts=' . $timestamp . '&extraParams=cryptobot');
$json = json_decode($response);
$stuff = reset($json);
$rateBeforeWeek = $stuff->USD;
$upWeek = round(100-($rateBeforeWeek/$rateNow*100), 2);
if ($upWeek > 0) $upWeek = "+$upWeek";

$timestamp= strtotime('-1 day', time());
$response = file_get_contents('https://min-api.cryptocompare.com/data/pricehistorical?fsym=' . $coin . '&tsyms=BTC,USD,EUR&ts=' . $timestamp . '&extraParams=cryptobot');
$json = json_decode($response);
$stuff = reset($json);
$rateBeforeDay = $stuff->USD;
$upDay = round(100-($rateNow/$rateBeforeDay*100), 2);
$upDay = round(($rateNow/$rateBeforeDay - 1) * 100, 2);
if ($upDay > 0) $upDay = "+$upDay";

echo "Current $coin-rate is $$rateNow ($upDay% since 24h and $upWeek% since last week) ";
