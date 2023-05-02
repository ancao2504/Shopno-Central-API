<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $_POST = json_decode(file_get_contents('php://input'), true);

    
        $id = $_POST['id'];
        $agentId  = $_POST['agentId'];
        $accname  = $_POST['accname'];
        $name  = $_POST['bankname'];
        $accno  = $_POST['accno'];
        $branch  = $_POST['branch'];
        $swift  = $_POST['swift'];
        $routing = $_POST['routing'];
        $address = $_POST['address'];


        $sql = "UPDATE `bank_accounts` SET 
        `agentId` = '$agentId',
        `accname` = '$accname',
        `bankname` = '$name',
        `accno` = '$accno',
        `branch` = '$branch',
        `swift` = '$swift',
        `address` = '$address',
        `routing` = '$routing' WHERE id='$id'";

        if($conn->query($sql)===TRUE){
            $response['status']='Success';
            $response['message']="Updated Successfully";
        }else{
             $response['status']='Success';
            $response['message']="Updated failed Successfully";
        }
        
    echo json_encode($response);
}   

?>