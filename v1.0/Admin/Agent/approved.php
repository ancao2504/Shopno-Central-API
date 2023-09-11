<?php

require '../../config.php';
require '../../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists('agentId', $_GET) && array_key_exists('actionBy', $_GET)) {

    $agentId = $_GET['agentId'];
    $actionBy = $_GET['actionBy'];

    $data = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId' AND platform='B2B'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
        $companyname = $data[0]['company'];
        $agentEmail = $data[0]['email'];
        $agentId = $data[0]['agentId'];
        $password = $data[0]['password'];
        $companyadd = $data[0]['companyadd'];
        $agentName = $data[0]['name'];

        $createdTime = date("Y-m-d H:i:s");

        $sql = "UPDATE `agent` SET `status`='active',`bonus`='0', `actionBy`='$actionBy', `updated_at`='$createdTime' WHERE agentId='$agentId' AND platform='B2B'";

        if ($conn->query($sql) === true) {
            $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `platform`,`actionAt`)
                  VALUES ('$agentId','$agentId','Approved',' ','$actionBy','B2B','$createdTime')");
            
            
            $subject = "Agent Request Approved";
            $headerAdmin = "Welcome $companyname";
            $headerAgent = "Agent Request Approved";
            $property = $data = "";
            $agentProperty = "Username: $agentEmail <br>";
            $agentData = "Password: $password";
            $adminMessage = "Thank you for accepting our agent request, It will be great journey for us.";
            $agentMessage = "Congratulation you are now our authorized agent.";
      
      
            sendToAdmin($subject, $adminMessage, $agentId, $headerAdmin, $property, $data);
            sendToAgent($subject, $agentMessage, $agentId, $headerAgent, $agentProperty, $agentData);
      
            
            $response['action'] = "success";
            $response['message'] = "Agent Approved Successfully";
        }

    } else {
        $response['error'] = "error";
        $response['message'] = "Agent Not Found";
    }

    echo json_encode($response);
}
