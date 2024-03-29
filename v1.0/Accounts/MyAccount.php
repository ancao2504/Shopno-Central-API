<?php

require '../config.php';
require '../functions.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("agentId", $_GET)) {

    $Search = $_GET["agentId"];

    $sql = "SELECT * FROM `agent` where agentId = '$Search' ";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $Balance = $conn->query("SELECT lastAmount FROM `agent_ledger` where agentId = '$Search' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
            $response = $row;
            if (!empty($Balance)) {
                $response['balance'] = $Balance[0];
            } else if (empty($Balance)) {
                $response['balance'] = 0;
            }
            array_push($return_arr, $response);
        }
    }

    echo json_encode($return_arr);
} else if (array_key_exists("action", $_GET)) {

    $action = $_GET['action'];
    if ($action == 'update') {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $_POST = json_decode(file_get_contents('php://input'), true);

            $agentId = $_POST["agentId"];
            $name = $_POST["name"];
            $phone = $_POST["phone"];
            $company = $_POST["company"];
            $companyadd = $_POST["companyadd"];

            $sql = "UPDATE `agent` SET `name`='$name',`phone`='$phone'
        ,`company`='$company',`companyadd`='$companyadd' WHERE agentId ='$agentId'";

            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Updated Successfully";
            } else {
                $response['status'] = "error";
                $response['message'] = "Updated failed";
            }

            echo json_encode($response);
        }
    } else if ($action == 'changepassword') {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $_POST = json_decode(file_get_contents('php://input'), true);

            $agentId = $_POST["agentId"];
            $oldPassword = $_POST["oldpassword"];
            $newPassword = $_POST["newpassword"];

            $sql = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

            if (!empty($row)) {
                $currentPassword = $row['password'];

                if ($currentPassword == $oldPassword) {

                    $updatesql = "UPDATE `agent` SET `password`='$newPassword' WHERE agentId='$agentId'";

                    if ($conn->query($updatesql) === true) {
                        $response['status'] = "success";
                        $response['message'] = "Password Updated Successfully";
                    } else {
                        $response['status'] = "error";
                        $response['message'] = "Updated failed";
                    }
                } else {
                    $response['status'] = "error";
                    $response['message'] = "Current Password Wrong";
                }
            }
            echo json_encode($response);
        }
    } else if ($action == 'resetpassword') {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $_POST = json_decode(file_get_contents('php://input'), true);

            $agentId = $_POST["agentId"];
            $newPassword = $_POST["newpassword"];

            $updatesql = "UPDATE `agent` SET `password`='$newPassword' WHERE agentId='$agentId'";

            if ($conn->query($updatesql) === true) {
                $response['status'] = "success";
                $response['message'] = "Password Updated Successfully";
            } else {
                $response['status'] = "error";
                $response['message'] = "Updated failed";
            }

            echo json_encode($response);
        }
    } else if ($action == 'changestaffpassword') {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $_POST = json_decode(file_get_contents('php://input'), true);

            $agentId = $_POST["agentId"];
            $staffId = $_POST["staffId"];
            $oldPassword = $_POST["oldpassword"];
            $newPassword = $_POST["newpassword"];

            $sql = mysqli_query($conn, "SELECT * FROM staffList WHERE agentId='$agentId' AND staffId='$staffId'");
            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

            if (!empty($row)) {
                $currentPassword = $row['password'];

                if ($currentPassword == $oldPassword) {

                    $updatesql = "UPDATE `staffList` SET `password`='$newPassword' WHERE agentId='$agentId' AND staffId='$staffId'";

                    if ($conn->query($updatesql) === true) {
                        $response['status'] = "success";
                        $response['message'] = "Password Updated Successfully";
                    } else {
                        $response['status'] = "error";
                        $response['message'] = "Updated failed";
                    }
                } else {
                    $response['status'] = "error";
                    $response['message'] = "Current Password Wrong";
                }
            }
            echo json_encode($response);
        }
    } else if ($action == 'updateimage') {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $agentId=$_POST['agentId'];

            $files = ['tinImg', 'nid', 'bankStatement'];
            $fileKey = array_keys($_FILES)[0];

            if (!in_array($fileKey, $files)) {
                echo json_encode(
                    [
                        "status" => "error",
                        "message" => "File Name Mismatched"
                    ]
                );
                exit;
            }
            $agentRow=$conn->query("SELECT `$fileKey` FROM `agent` WHERE `agentId` = '$agentId'")->fetch_assoc();
            
            if(empty($agentRow))
            {
                echo json_encode(
                    [
                        "status" => "error",
                        "message" => "Agent Not Found"
                    ]
                );
                exit;
            }
            deleteFile($agentRow[$fileKey]);
            $newFileName = $imagename = $fileKey;
            $acceptablesize = 5000000;
            $folder = "Agent/$agentId/$fileKey";
            $fileName = $_FILES[$fileKey]["name"];

            $fileUrl = uploadImage($imagename, $acceptablesize, $folder, $fileName, $newFileName);

            $updateQuery = "UPDATE `agent` SET `$fileKey`='$fileUrl' WHERE `agentId`='$agentId';";

            if ($conn->query($updateQuery)) {
                $response["status"] = "success";
                $response["message"] = "$fileKey Updated Successfully";
            } else {
                $response["status"] = "error";
                $response["message"] = "$fileKey Update Failed";
                
            }

            echo json_encode($response);
        } else {
            echo json_encode(
                [
                    "status" => "error",
                    "message" => "Wrong Request Method"
                ]
            );
        }
    }
}
