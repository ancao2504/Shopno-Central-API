<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$_POST = json_decode(file_get_contents('php://input'), true);

if(isset($_POST['SearchID']) && isset($_POST['ResultID'])){
  
}

$SearchID = $_POST['SearchID'];
$ResultID = $_POST['ResultID'];

$FlyHubRequest ='{
  "SearchID": "'.$SearchID.'",
  "ResultID": "'.$ResultID.'"
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
  CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirPrice',
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

$flyhubresponse = curl_exec($curl);

curl_close($curl);

$flyhubResult = json_decode($flyhubresponse, true);
  $status = $flyhubResult['Error'];

  if(isset($status)){
    $FlyHubRes['status']= "error";
    $FlyHubRes['message']= $flyhubResult['Error']['ErrorMessage'];
    echo json_encode($FlyHubRes);
      
      
  }else{
      echo $flyhubresponse;           
  }
    