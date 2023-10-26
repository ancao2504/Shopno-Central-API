<?php

use Firebase\JWT\JWK;

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//require "../../vendor/autoload.php";

//include_once '../../authorization.php';

// todo:CHECK IF THE REQUEST METHOD IS PUT

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Todo:CHECK AUTHORIZATION
        $_POST = json_decode(file_get_contents("php://input"), true);
        $bookingId = $_POST['bookingId'];
        $staffEmail = $_POST['staffemail'];
        $assign = $_POST['assign'];
        $assignBy = $_POST['assignby'];
        $currentDate = date('Y-m-d H:i:s');
        
        $sql = "SELECT email FROM staffList WHERE email = '$staffEmail'";
        $bookingSql = "SELECT bookingId FROM booking WHERE bookingId = '$bookingId'";
        if($conn->query($sql) && $conn->query($bookingSql)){
            $sql = "update booking set assign= '$assign', assignBy='$assignBy', assign_time='$currentDate' WHERE bookingId = '$bookingId'";
            if($conn->query($sql)){
                $response['status'] = "success";
                $response['message'] =$assign . " Assign To This Booking";
                echo json_encode(($response));
            }else{
                $response['status'] = "error";
                $response['message']= "Assign Failed";
                echo json_encode(($response));
             }
        }else{
            $response['status'] ='error';
            $response['message'] ="Staff Not Found";
            echo json_encode(($response));
            exit;
        }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("message" => "Method not allowed."));
}
?>
