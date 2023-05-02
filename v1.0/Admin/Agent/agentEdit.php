<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $agentId  = $_POST['agentId'];
    $Name  = $_POST['name'];
    $Email  = $_POST['email'];
    $Phone  = $_POST['phone'];
    $Password  = $_POST['password'];
    $Address  = $_POST['address'];

    $Date = date("Y-m-d H:i:s");
    $DateTime = date("D d M Y h:i A");

    $sql = "UPDATE agent SET 
            name='$Name',
            email='$Email',
            password='$Password',
            companyadd='$Address',
            phone='$Phone',updated_at='$Date' where  agentId='$agentId'";

    if ($conn->query($sql) === TRUE) {

        $response['status']="success";
        $response['message']="Updated Successfully";
    }else{
        $response['status']="error";
           $response['message']="Update Failed";
    }

    echo json_encode($response);
}