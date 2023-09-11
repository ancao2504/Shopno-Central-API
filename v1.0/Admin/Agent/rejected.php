<?php

require '../../config.php';
require '../../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if (array_key_exists("agentId", $_GET) && array_key_exists("actionBy", $_GET)) {
    $agentId = $_GET['agentId'];
    $actionBy = $_GET['actionBy'];

    $data = $conn->query("SELECT * FROM agent WHERE agentId='$agentId' AND platform='B2B'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
        $companyname = $data[0]['company'];
        $agentEmail = $data[0]['email'];
        $agentId = $data[0]['agentId'];
        $password = $data[0]['password'];
        $companyadd = $data[0]['companyadd'];
        $agentName = $data[0]['name'];
        $Status = $data[0]['status'];
        $createdTime = date("Y-m-d H:i:s");

        if ($Status == 'rejected') {
            $response['status'] = "error";
            $response['message'] = "Agent Already Rejected";
        } else {
            $sql = "UPDATE `agent` SET `status`='rejected',`actionBy`='$actionBy', `updated_at`='$createdTime' WHERE agentId='$agentId' AND platform='B2B'";

            if ($conn->query($sql) === true) {
                $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`,`platform`, `actionAt`)
                    VALUES ('$agentId','$agentId','Rejected',' ','$actionBy', 'B2B','$createdTime')");

                $header = $subject = "Agent Request Rejected";
                $property = $data = "";
                $adminMessage = "Our Registration has been cancelled.";
                $agentMessage = "Due to lack of valid information, your Agent Register Request has been cancelled.";


                sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
                sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);




                $response['action'] = "success";
                $response['message'] = "Agent Rejected Successfully";
            }
        }

    } else {
        $response['action'] = "error";
        $response['message'] = "Agent Not Found";
    }
    echo json_encode($response);
}
