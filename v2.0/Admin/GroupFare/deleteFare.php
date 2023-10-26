<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){  

    if(array_key_exists("groupId",$_GET)){
        
        $groupId = $_GET['groupId'];          
        $sql = "DELETE FROM `groupfare` WHERE groupId='$groupId'";

        if ($conn->query($sql) === TRUE) {
            $response['status']="success";
            $response['message']="Group Fare Deleted Successfully";                     
        }else{
            $response['status']="error";
            $response['message']="Deleted Failed Successfully";
        }
            
        echo json_encode($response);
        
    }
}else{
  authorization($conn);
}


?>