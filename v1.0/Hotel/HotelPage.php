<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.worldota.net/api/b2b/v3/search/hp/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "checkin": "2023-09-25",
    "checkout": "2023-09-26",
    "residency": "gb",
    "language": "en",
    "guests": [
        {
            "adults": 2,
            "children": []
        }
    ],
    "id": "radisson_blu_residence_dubai_marina",
    "currency": "BDT"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic NDk0NjowNjk1ZTZjOS1hZDlmLTQxOTUtOTNjMy1mNTY4YTkzMmY1Zjc=',
    'Content-Type: application/json',
    'Cookie: uid=TfTb8GQRVsdBEXjDA6ZxAg=='
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;