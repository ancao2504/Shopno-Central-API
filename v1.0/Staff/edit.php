<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $_POST = json_decode(file_get_contents('php://input'), true);

    $agentId = $_POST["agentId"];
    $staffId = $_POST["staffId"];
    $Name  = $_POST['Name'];
    $Email  = $_POST['Email'];
    $Designation  = $_POST['Designation'];
    $Phone  = $_POST['Phone'];
    $Role  = $_POST['Role'];
    $Status  = $_POST['Status'];
    $Password = $_POST['Password'];


    $sql = "UPDATE staffList SET 
            name='$Name',
            email='$Email',
            password='$Password',
            phone='$Phone',
            status='$Status',
            designation='$Designation',
            role='$Role' where staffId='$staffId' AND agentId='$agentId'";

    if ($conn->query($sql) === TRUE) {

        $response['status']="success";
        $response['message']="Updated Successfully";
    }else{
        $response['status']="error";
        $response['message']="Updated Query Failed";
    }

    echo json_encode($response);
}

?>