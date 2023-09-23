<?php

require '../../config.php';
require '../../functions.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("userId", $_GET)) {

    $Search = $_GET["userId"];

    $sql = "SELECT * FROM `agent` where userId = '$Search' ";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $Balance = $conn->query("SELECT lastAmount FROM `agent_ledger` where userId = '$Search' ORDER BY id DESC LIMIT 1")->fetch_assoc();
            $response = $row;
            if (!empty($Balance)) {
                $response['balance'] = $Balance['lastAmount'];

            }else if(empty($Balance)){
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

            $userId = $_POST["userId"];
            $newPassword = $_POST["newpassword"];

            $updatesql = "UPDATE `agent` SET `password`='$newPassword' WHERE userId='$userId'";

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
    }
}
else if(array_key_exists("edit",$_GET)){
    
    $userId = $_POST['userId'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $address = $_POST['address'];
    $fileName=$_FILES["userImg"]["name"];
    $imagename="userImg";
    $acceptablesize=5000000;
    $folder="User/$userId/myAccount";
    $newFileName=$userId.$name;
    
    $link=uploadImage($imagename, $acceptablesize, $folder, $fileName, $newFileName);

    if(isset($userId)){
        $checker = $conn->query("SELECT * FROM agent WHERE userId = '$userId'")->fetch_assoc();
        if(!empty($checker)){
                $sql = "UPDATE agent SET companyImage='$link' , name = '$name', email='$email', phone='$phone', password='$password', userAddress='$address' WHERE userId ='$userId'";
                if($conn->query($sql)){
                    $response['status'] = "success";
                    $response['message'] ="User Details updated successfully";
                }else{
                    $response['status'] = "error";
                    $response['message'] = "Query failed";
                }
        }else {
            $response['status'] ='error';
            $response['message'] ="User Not Found";
        }
        echo json_encode($response);

    }
}
