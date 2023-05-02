<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
        $_POST = json_decode(file_get_contents('php://input'), true);
            $key = $_POST['key'];
            $value = $_POST['value'];
                         
        $updatesql = "UPDATE `control` SET `$key`='$value' WHERE id='1'";
    
        if ($conn->query($updatesql) === TRUE) {
            $response['status']="success";
            $response['message']="Updated Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Updated failed";
        }
            
    echo json_encode($response);
}else if(array_key_exists("gdsPrice",$_GET) && array_key_exists("farePrice",$_GET)){
            $gdsPrice = $_GET['gdsPrice'];
            $farePrice = $_GET['farePrice'];
                         
        $updatesql = "UPDATE `control` SET `gdsPrice`='$gdsPrice',`farePrice`='$farePrice' WHERE id='1'";
    
        if ($conn->query($updatesql) === TRUE) {
            $response['status']="success";
            $response['message']="Updated Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Updated failed";
        }
    echo json_encode($response);
}



?>