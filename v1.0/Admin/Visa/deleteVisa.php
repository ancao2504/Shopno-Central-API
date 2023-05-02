<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("all",$_GET) && array_key_exists("visaId",$_GET)){
    
    $visaId = $_GET['visaId'];
          
    $sql = "DELETE FROM `visa_info` WHERE visaId='$visaId'";
    $sql1 = "DELETE FROM `visa_check_list` WHERE visaId='$visaId'";

    if ($conn->query($sql) === TRUE) {
        $conn->query($sql1);
        $response['status']="success";
        $response['message']="Visa Deleted Successfully";                     
    }else{
        $response['status']="error";
        $response['message']="Deleted Failed Successfully";
    }
         
    echo json_encode($response);
}else if(array_key_exists("ck",$_GET) && array_key_exists("id",$_GET)){
    
    $Id = $_GET['id'];
    
    $sql = "DELETE FROM `visa_check_list` WHERE id='$Id'";
     if ($conn->query($sql) === TRUE) {
        $response['status']="success";
        $response['message']="Visa Deleted Successfully";                     
    }else{
        $response['status']="error";
        $response['message']="Deleted Failed Successfully";
    }

    echo json_encode($response);
    
}


?>