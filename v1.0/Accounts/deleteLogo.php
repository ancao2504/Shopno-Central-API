<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include '../config.php';

if(array_key_exists("agentId", $_GET)){
    $agentId = $_GET["agentId"];
    
    $sql = "UPDATE `agent` SET `companyImage`='' WHERE agentId='$agentId'";

    $file_pointer = "../../../cdn.flyfarint.com/Agent/$agentId/companylogo.png";
  
    if ($conn->query($sql) === TRUE) {
        if(file_exists($file_pointer)){
            unlink($file_pointer);
            $response['status']="success";
            $response['message']="Deleted Successfully"; 
        }else{
            $response['status']="success";
            $response['message']="Already Deleted Successfully"; 
        }
                 
    } else {
        $response['status']="error";
        $response['message']="Delete failed";
    }
   

    echo json_encode($response);
    
}