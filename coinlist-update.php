<?php

$coinlist = file_get_contents('https://www.cryptocompare.com/api/data/coinlist/');
$json = json_decode($coinlist);

$fp = fopen('coinlist.json', 'w');
fwrite($fp, json_encode($json->Data));
fclose($fp);

?>
