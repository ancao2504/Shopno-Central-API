<?php

$client_id= base64_encode("V1:351640:27YK:AA");
$client_secret = base64_encode("spt5164");

$token = base64_encode($client_id.":".$client_secret);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.platform.sabre.com/v2/auth/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'grant_type=client_credentials',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded',
    "Authorization: Basic $token"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

?>