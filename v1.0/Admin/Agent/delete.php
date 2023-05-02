<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("agentId",$_GET) && array_key_exists("actionBy",$_GET)){
    
    $agentId = $_GET['agentId'];
    $actionBy = $_GET['actionBy'];           
    $sql = "DELETE FROM `agent` WHERE agentId='$agentId'";

    $Time = date("Y-m-d H:i:s");


    if ($conn->query($sql) === TRUE){
        $conn->query("DELETE FROM `staffList` WHERE agentId='$agentId'");
        $sqlActivity = "INSERT INTO `activitylog`(
                `agentId`,
                `reference`,
                `name`,
                `activity`,             
                `created`)
            VALUES(
                '$agentId',
                '$agentId',
                '$actionBy', 
                'Agent Id Deleted',              
                '$Time'
            )";

            if ($conn->query($sqlActivity) === TRUE) {
                $response['status']="success";
                $response['message']="Agent Deleted Successfully";   
                echo json_encode($response);   
            }else{
                $response['status']="error";
                $response['message']="Deleted Failed Successfully";
                echo json_encode($response);
            }
                             
    }
         
    
    
}



?>