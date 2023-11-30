<?php
include '../../config.php';
include './utils.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if(array_key_exists('BookingID', $_GET)){
    $Booking_ID = $_GET['BookingID'];
    $accessToken = getProdToken();

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.platform.sabre.com/v1/trip/orders/cancelBooking',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "confirmationId": "'.$Booking_ID.'",
    "retrieveBooking": true,
    "cancelAll": true,
    "errorHandlingPolicy": "ALLOW_PARTIAL_CANCEL"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Conversation-ID: 2021.01.DevStudio',
    "Authorization: Bearer $accessToken"
  ),
));

$SabreResponse = curl_exec($curl);
$SabreResponseResult  = json_decode($SabreResponse, true);
curl_close($curl);

	$conn->query("UPDATE `hotel_booking`
	SET `status`='Cancelled' where pnr='$Booking_ID'");

echo $SabreResponse;

}