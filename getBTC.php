<?php
$btc = "";
$uri = "https://blockchain.info/ticker";

try {
  ob_start();
  $curl = 'curl -s POST '.$uri;
  passthru($curl);
  $response = ob_get_contents();
  ob_end_clean();

  $retArr = json_decode($response, true);
  $btc = $retArr['USD']['symbol'].$retArr['USD']['last'];
} catch (Exception $e) {
  throw($e);
}

echo $btc;
