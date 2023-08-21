<?php

require '../config.php';
require '../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");

if (array_key_exists("email", $_GET)) {

  $email = $_GET["email"];

  $sql = mysqli_query($conn, "SELECT * FROM agent WHERE email='$email'");
  $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

  if (!empty($row)) {

    $agentId = $row['agentId'];
    $companyName = $row['company'];

    $encryption = substr(md5(mt_rand()), 0, 50);
    $link = "https://b2b.shopnotour.com/resetpassword/$encryption";




    $sql = "INSERT INTO `forgetpassword`(`agentId`, `email`, `link`, `isClick`, `expire`)
                         VALUES ('$agentId','$email','$encryption','0','36000')";
    if ($conn->query($sql) === TRUE) {


      $header = $subject = "Forget Password";
      $property = "Password: ";
      $data = $encryption;
      $message = "Please use this temporary password, 
      Once after login please change this password with your own.";

      sendToAgent($subject, $message, $agentId, $header, $property, $data);

      
      $response['status'] = "success";
      $response['message'] = "Password Reset Link Send To Your Email";
    } else {
      $response['status'] = "error";
      $response['message'] = "Attempt failed";
    }
  } else {
    $response['status'] = "error";
    $response['message'] = "Email Not Found";
  }

  echo json_encode($response);
}
