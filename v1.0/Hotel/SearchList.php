<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.worldota.net/api/b2b/v3/search/serp/region/',
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
    "residency": "bd",
    "language": "en",
    "guests": [
        {
            "adults":2,
            "children": []
        }
    ],
    "region_id": 6053839,
    "hotels_limit": 1,
    "currency": "BDT"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic NDk0NjowNjk1ZTZjOS1hZDlmLTQxOTUtOTNjMy1mNTY4YTkzMmY1Zjc=',
    'Content-Type: application/json',
    'Cookie: uid=TfTb8GQRVsdBEXjDA6ZxAg=='
  ),
));

$Hotelresponse = curl_exec($curl);
//echo $Hotelresponse;
$result = json_decode($Hotelresponse, true);
//print_r($result);

curl_close($curl);

if(isset($result['data']['hotels'])){
  $AllHotels = $result['data']['hotels'];

  $AllHotelList = array();
  foreach($AllHotels as $hotels){
    $id = $hotels['id'];
    $daily_prices = $hotels['rates'][0]['daily_prices'][0];
    $meal = $hotels['rates'][0]['meal'];
    $room_name = $hotels['rates'][0]['room_name'];
    $amenities_data = $hotels['rates'][0]["amenities_data"];
    $guest = $hotels['rates'][0]['rg_ext']["capacity"];

    $SingleHotel = array("id" =>  $id ,
                          "price"=> $daily_prices,
                          "meal" => $meal,
                          "roomname"=> $room_name,
                          "amenities_data"=> $amenities_data,
                          "guest"=> $guest);
    

    array_push($AllHotelList, $SingleHotel);    
  }


  echo json_encode($AllHotelList);
  
}