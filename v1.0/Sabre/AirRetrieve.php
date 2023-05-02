<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("BookingID",$_GET)){
    $BookingID = $_GET['BookingID'];

try{

	$client_id= base64_encode("V1:396724:FD3K:AA");
	//$client_secret = base64_encode("280ff537"); //cert
	$client_secret = base64_encode("FlWy967"); //prod

	$token = base64_encode($client_id.":".$client_secret);
	$data='grant_type=client_credentials';

		$headers = array(
			'Authorization: Basic '.$token,
			'Accept: /',
			'Content-Type: application/x-www-form-urlencoded'
		);

		$ch = curl_init();
		//curl_setopt($ch,CURLOPT_URL,"https://api-crt.cert.havail.sabre.com/v2/auth/token");
		curl_setopt($ch,CURLOPT_URL,"https://api.platform.sabre.com/v2/auth/token");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		$resf = json_decode($res,1);
		$access_token = $resf['access_token'];

		//print_r($resf);

	}catch (Exception $e){
		
	}



$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.platform.sabre.com/v1/trip/orders/getBooking',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "confirmationId": "'.$BookingID.'",
    "retrieveBooking": true,
    "cancelAll": true,
    "errorHandlingPolicy": "ALLOW_PARTIAL_CANCEL"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Conversation-ID: 2021.01.DevStudio',
    "Authorization: Bearer $access_token"
  ),
));

$response = curl_exec($curl);
$data = json_decode($response, true);


curl_close($curl);
echo $response;


}else{
    echo json_encode("Error");
}