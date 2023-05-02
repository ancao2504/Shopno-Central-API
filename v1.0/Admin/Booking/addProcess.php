<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



    if (array_key_exists("bookingId", $_GET) && array_key_exists("name", $_GET) ){

        $bookingId = $_GET["bookingId"];
        $name= $_GET["name"];
        
        $sql = "UPDATE `booking` SET `maker`='$name' where bookingId='$bookingId'";
        
        if($conn->query($sql) == TRUE) {
            $response['status'] = "success";
            $response['message'] = "Added";
        }else{
            $response['status'] = "success";
            $response['message'] = "Added";
        }
        
        echo  json_encode( $response);
    }
        
    
        
    
?>