<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if(array_key_exists("id", $_GET)){

    $id = $_GET["id"];
    $sql = "UPDATE  `groupfare` SET seat ='0' WHERE id='$id'";
             
    if ($conn->query($sql) === TRUE) {
        $response["action"] = "success";
        $response["message"] = "Group Edited Successfully";
    }else{
        $response["action"] = "error";
        $response["message"] = "Group Edited Failed";
    }
    echo json_encode($response);
                           
}



?>