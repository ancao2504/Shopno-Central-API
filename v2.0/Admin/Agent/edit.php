<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){ 
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $_POST = json_decode(file_get_contents("php://input"), true);
        $agentId = $_POST['agentId'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $checker = $conn->query("SELECT * FROM agent WHERE agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
        if(!empty($checker)){
            $sql = "UPDATE agent SET phone='$phone', email='$email' WHERE agentId='$agentId'";
            if($conn->query($sql) == true){
                $response['status'] = "success";
                $response['message'] = "Agent Data Updated";
            }else{
                $response['status'] = "error";
                $response['message'] = "Query Failed";
            }
        }else{
            $response['status'] = "error";
            $response['message'] = "Agent not found";
        }

        echo json_encode($response);
    }
}else{
  authorization($conn);
}