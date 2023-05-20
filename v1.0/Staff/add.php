<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");


if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);

        $agentId  = $_POST['agentId'];
        $Name  = $_POST['Name'];
        $Email  = $_POST['Email'];
        $Designation  = $_POST['Designation'];
        $Phone  = $_POST['Phone'];
        $Role  = $_POST['Role'];      
        $Password = $_POST['Password'];

        $Date = date("Y-m-d H:i:s");
        $DateTime = date("D d M Y h:i A");

        $StaffId ="";
        $sql = "SELECT * FROM staffList where agentId='$agentId' ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["staffId"]); 
                $number= (int)$outputString + 1;
                $StaffId = "STST$number"; 								
            }
        } else {
            $StaffId ="STST1000";
        }

        $checkAgent="SELECT * FROM agent WHERE agentId='$agentId'";
        $agentResultInto=mysqli_query($conn,$checkAgent);
        $agentData = mysqli_fetch_array($agentResultInto);

        if(!empty($agentData)){ 
          $agentName = $agentData['name'];
          $companyName = $agentData['company'];
        }

        $checkUser="SELECT * FROM staffList WHERE email='$Email'";
        $result=mysqli_query($conn,$checkUser);

        $checkAgent="SELECT * FROM agent WHERE email = '$Email'";
        $resultAgent=mysqli_query($conn,$checkAgent);

        if(mysqli_num_rows($result) <= 0 && mysqli_num_rows($resultAgent)> 0){                   
              $response['status']="error";
  	          $response['message']="User Already Exists as Agent";

        }else if(mysqli_num_rows($result)> 0){ 
            $response['status']="error";
  	        $response['message']="Staff Already Exists";       
        }else{
            $sql = "INSERT INTO `staffList`(
                `staffId`,
                `agentId`,
                `name`,
                `email`,
                `password`,
                `phone`,
                `status`,
                `designation`,
                `role`,
                `created`
              )
            VALUES(
                '$StaffId',
                '$agentId',
                '$Name',
                '$Email',
                '$Password',               
                '$Phone',
                'Active',
                '$Designation',
                '$Role',
                '$Date'
            )";

            if ($conn->query($sql) === TRUE) {

                $response['status']="success";
                $response['message']="Staff Added Successful";
          
            } else {
                $response['status']="error";
  	            $response['message']="Added failed";
            }
        }
        
        echo json_encode($response);
    }else{
         echo json_encode("Data Missing");
    }


?>