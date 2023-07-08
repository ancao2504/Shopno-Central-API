<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("all", $_GET)){
    $getData = $conn->query("SELECT * FROM booking WHERE platform = 'B2C'")->fetch_all(MYSQLI_ASSOC);
    if(!empty($getData)){
        echo json_encode($getData);
    }else{
        $response['status'] ="error";
        $response['message'] ="Data Not Found";
        echo json_encode($response);
    }
}