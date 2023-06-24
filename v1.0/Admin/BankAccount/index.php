<?php
include "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("add", $_GET)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $_POST = json_decode(file_get_contents('php://input'), true);
        $accname = $_POST['accname'];
        $bankname = $_POST['bankname'];
        $accno = $_POST['accno'];
        $branch = $_POST['branch'];
        $swift = $_POST['swift'];
        $routing = $_POST['routing'];
        $address = $_POST['address'];

        $Date = date("Y-m-d H:i:s");

        $checkUser = "SELECT * FROM bank_accounts WHERE bankname='$bankname' AND accno='$accno' AND agentId='Admin'";
        $result = mysqli_query($conn, $checkUser);

        if (mysqli_num_rows($result) > 0) {
            $response['status'] = "error";
            $response['message'] = "Already Exists";
            echo json_encode($response);
        } else {
            $sql = "INSERT INTO `bank_accounts`(
                `agentId`,
                `accname`,
                `bankname`,
                `accno`,
                `branch`,
                `swift`,
                `address`,
                `routing`,
                `createdAt`
              )
            VALUES(
                'Admin',
                '$accname',
                '$bankname',
                '$accno',
                '$branch',
                '$swift',
                '$address',
                '$routing',
                '$Date'
            )";

            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Bank Account Added Successfull";

            } else {
                $response['status'] = "success";
                $response['message'] = "Bank Account Added Successfull";
            }
            echo json_encode($response);
        }

    }
}else if (array_key_exists("all", $_GET)) {

    $sql = "SELECT * FROM bank_accounts WHERE agentId ='Admin'";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response = $row;
            array_push($return_arr, $response);
        }
    }

    echo json_encode($return_arr);
}else if(array_key_exists("edit", $_GET)){
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $_POST = json_decode(file_get_contents('php://input'), true);


        $id = $_POST['id'];
        $accname  = $_POST['accname'];
        $name  = $_POST['bankname'];
        $accno  = $_POST['accno'];
        $branch  = $_POST['branch'];
        $swift  = $_POST['swift'];
        $routing = $_POST['routing'];
        $address = $_POST['address'];


        $sql = "UPDATE `bank_accounts` SET 
        `accname` = '$accname',
        `bankname` = '$name',
        `accno` = '$accno',
        `branch` = '$branch',
        `swift` = '$swift',
        `address` = '$address',
        `routing` = '$routing' WHERE id='$id' AND agentId='Admin'";

        if($conn->query($sql)===true) {
            $response['status']='Success';
            $response['message']="Updated Successfully";
        } else {
            $response['status']='Success';
            $response['message']="Updated failed Successfully";
        }

        echo json_encode($response);
    }
} else if(array_key_exists('delete', $_GET)) {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = json_decode(file_get_contents("php://input"), true);
            $Id = $_POST['Id'];
            $sql = "DELETE FROM `bank_accounts` WHERE id = '$Id'";
            if($conn->query($sql)){
                $response['status'] = 'success';
                $response['message'] = "Bank account deleted";
            }else {
                $response['status'] = 'error';
                $response['message'] = "Bank account query failed";
            }
            echo json_encode($response);
        }
}

$conn->close();
?>