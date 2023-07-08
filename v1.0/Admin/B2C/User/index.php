<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("user", $_GET)){
    if($_SERVER['REQUEST_METHOD'] =="POST"){
        $Option = $_POST['option'];
        if($Option == "all"){
            $getData = $conn->query("SELECT * FROM agent WHERE platform = 'B2C'")->fetch_all(MYSQLI_ASSOC);
            if(!empty($getData)){
                echo json_encode($getData);
            }else{
                $response=[];
                echo json_encode($response);
            }

        }else if($Option =="status"){
                $Status = $_POST['status'];
                $UserId = $_POST['userId'];
                $checker = $conn->query("SELECT userId FROM agent WHERE userId='$UserId' AND platform='B2C'")->fetch_all(MYSQLI_ASSOC);
                if(!empty($checker)){
                    $sql ="UPDATE agent SET status='$Status' WHERE userId='$UserId'AND platform='B2C'";
                    if($conn->query($sql)){
                        $response['status'] ="success";
                        $response['message'] ="User $Status";
                    }
                }else{
                    $response['status'] ="error";
                    $response['message'] ="User Not Found";
                    
                }
                echo json_encode($response);
        }
    }else{
        $response['status'] ="error";
        $response['message'] ="Wrong Url";
        echo json_encode($response);
    }
}
$conn->close();
?>