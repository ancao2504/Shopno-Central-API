<?php

require '../../config.php';
require '../../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

    
if(array_key_exists("agentId",$_GET) && array_key_exists("actionBy",$_GET)){
    
        $agentId = $_GET['agentId'];
        $actionBy = $_GET['actionBy'];
         
        $data = $conn->query("SELECT * FROM agent WHERE agentId='$agentId' AND platform='B2B'")->fetch_all(MYSQLI_ASSOC);
if (!empty($data)) {
    $companyname = $data[0]['company'];
    $agentEmail = $data[0]['email'];
    $agentId = $data[0]['agentId'];
    $password =$data[0]['password'];
    $companyadd =$data[0]['companyadd'];
    $agentName = $data[0]['name'];
    $Status = $data[0]['status'];


    $createdTime = date("Y-m-d H:i:s");

    if($Status == 'deactive'){
      $response['status']="error";                     
      $response['message']=" Agent Already Deactivated";
    }else {
    $sql="UPDATE `agent` SET `status`='deactive', `actionBy`='$actionBy', `updated_at`='$createdTime'  WHERE agentId='$agentId' AND platform='B2B'";

    if ($conn->query($sql) === true) {
        $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
            VALUES ('$agentId','$agentId','Deactive','','B2B','$actionBy','$createdTime')");


      $header = $subject = "Agent Deactivate";
      $property = "Agent: ";
      $data = $agentName . ", " . $companyname;
      $adminMessage = "Our Agent Acccount is deactivated.";
      $agentMessage = " Your Agent Account is deactivate.";


      sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
      sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);


        $response['status']="success";
        $response['message']="Agent Deactivated Successfully";
    } else {
        $response['status']="error";
        $response['message']=" Agent Deactivated Failed";
    }
}

  }else{
    $response['status']="error";                     
    $response['message']="Agent Not Found";
  }

 echo json_encode($response);
 }

        
?>