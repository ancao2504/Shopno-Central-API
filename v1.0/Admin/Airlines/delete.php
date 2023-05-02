<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(isset($_GET['id'])){
    
    $id = $_GET['id'];          
    $sql = "DELETE FROM `airlines` WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        $response['status']="sucess";
        $response['message']="Airlines Deleted Successfully";                     
    }else{
        $response['status']="error";
        $response['message']="Deleted Failed Successfully";
    }
         
    echo json_encode($response);
    
}



?>