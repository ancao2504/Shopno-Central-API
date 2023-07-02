<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $jsonData = json_decode(file_get_contents('php://input'), true);
    $passengerData=$jsonData["flightPassengerData"];
    


}
else
{
    $response["status"] = "Failed";
    $response["message"] = "Wrong Request Method";
        
    echo json_encode($response);
}


?>