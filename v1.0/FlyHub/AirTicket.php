<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if (array_key_exists("BookingID",$_GET)){
  $BookingID = $_GET['BookingID'];

$FlyHubRequest ='{
  "BookingID": "'.$BookingID.'"
}';

//echo $FlyHubRequest;


//Fly Hub

$curlflyhubauth = curl_init();

curl_setopt_array($curlflyhubauth, array(
CURLOPT_URL => 'https://api.flyhub.com/api/v1/Authenticate',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS =>'{
"username": "ceo@flyfarint.com",
"apikey": "ENex7c5Ge+0~SGc1t71iccr1xXacDPdK51g=iTm9SlL+de39HF"
}',
CURLOPT_HTTPHEADER => array(
'Content-Type: application/json'
),
));

$response = curl_exec($curlflyhubauth);

$TokenJson = json_decode($response,true);

$FlyhubToken = $TokenJson['TokenId'];

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirRetrieve',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $FlyHubRequest,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    "Authorization: Bearer $FlyhubToken"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
}