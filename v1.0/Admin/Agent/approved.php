<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists('agentId', $_GET) && array_key_exists('actionBy', $_GET)) {

    $agentId = $_GET['agentId'];
    $actionBy = $_GET['actionBy'];

    $data = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
        $companyname = $data[0]['company'];
        $agentEmail = $data[0]['email'];
        $agentId = $data[0]['agentId'];
        $password = $data[0]['password'];
        $companyadd = $data[0]['companyadd'];
        $agentName = $data[0]['name'];

        $createdTime = date("Y-m-d H:i:s");

        $sql = "UPDATE `agent` SET `status`='active',`bonus`='0' WHERE agentId='$agentId'";

        if ($conn->query($sql) === true) {
            $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                  VALUES ('$agentId','$agentId','Approved',' ','$actionBy','$createdTime')");
            $response['action'] = "success";
            $response['message'] = "Agent Approved Successfully";
        }

    } else {
        $response['error'] = "error";
        $response['message'] = "Agent Not Found";
    }

    echo json_encode($response);
}
