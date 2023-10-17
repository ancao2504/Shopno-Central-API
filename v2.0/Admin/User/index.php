<?php

require '../../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../../authorization.php';
if (authorization($conn) == true){  

    if (array_key_exists("all", $_GET)) {
        $getAllData = $conn->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);

        if (!empty($getAllData)) {
            echo json_encode($getAllData);
        } else {
            $response['status'] = "error";
            $response['message'] = "Data not found";
            echo json_encode($response);
        }
    } else if (array_key_exists("add", $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $_POST = json_decode(file_get_contents("php://input"), true);

            $fName = $_POST['fname'];
            $lName = $_POST['lname'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $role = $_POST['role'];

            $EmpId = "";
            $sql = "SELECT * FROM users ORDER BY EMP_ID DESC LIMIT 1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outString = preg_replace('/[^0-9]/', '', $row['EMP_ID']);
                    $number = (int) $outString + 1;
                    $EmpId = "EMP-$number";
                }
            } else {
                $EmpId = "EMP-1000";
            }
            $checker = $conn->query("SELECT email FROM users WHERE email = '$email'")->fetch_all(MYSQLI_ASSOC);
            if (empty($checker)) {

                $sql = "INSERT INTO `users` (`EMP_ID`, `username`, `status`,`fname`, `lname`, `email`, `password`, `role`) VALUES ('$EmpId', CONCAT('$fName','$lName'),'Active', '$fName', '$lName', '$email', '$password', '$role')";

                if ($conn->query($sql) == true) {
                    $response['status'] = "success";
                    $response['message'] = "New Employee Created Successfully";
                } else {
                    $response['status'] = "error";
                    $response['message'] = "Query Failed";
                }
            } else if (!empty($checker)) {
                $response['status'] = "error";
                $response['massage'] = "Email Already Existing";
            }
            echo json_encode($response);

        }

    } else if (array_key_exists("edit", $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $_POST = json_decode(file_get_contents("php://input"), true);
            $id = $_POST['id'];
            $fName = $_POST['fname'];
            $lName = $_POST['lname'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            $checker = $conn->query("SELECT * FROM users WHERE id = '$id'")->fetch_all(MYSQLI_ASSOC);
            if (!empty($checker)) {

                $sql = "UPDATE `users` SET `username`= CONCAT('$fName','$lName'), `fname`='$fName', `lname`='$lName', `email`='$email', `password`='$password', `role`='$role' WHERE id='$id'";

                if ($conn->query($sql) == true) {
                    $response['status'] = "success";
                    $response['message'] = "Employee Details Update Successfully";
                } else {
                    $response['status'] = "error";
                    $response['message'] = "Query Failed";
                }
            } else {
                $response['status'] = "error";
                $response['message'] = "Id not found";
            }
            echo json_encode($response);

        }
    } else if (array_key_exists("delete", $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $_POST = json_decode(file_get_contents("php://input"), true);

            $id = $_POST["id"];

            $checker = $conn->query("SELECT * FROM users WHERE id = '$id'")->fetch_all(MYSQLI_ASSOC);
            if (!empty($checker)) {
                $sql = "DELETE FROM users WHERE id = '$id'";
                if ($conn->query($sql) == true) {
                    $response['status'] = "success";
                    $response['message'] = "Employee Deleted Successfully";
                } else {
                    $response['status'] = "error";
                    $response['message'] = "Query Failed";
                }
            } else {
                $response['status'] = "error";
                $response['message'] = "Id not found";
            }
            echo json_encode($response);
        }

    } else if (array_key_exists("status", $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = json_decode(file_get_contents("php://input"), true);
            $EmpId = $_POST['empId'];
            $Status = $_POST['status'];
            $checker = $conn->query("SELECT * FROM users WHERE EMP_ID = '$EmpId'")->fetch_all(MYSQLI_ASSOC);
            if (!empty($checker)) {
                $sql = "UPDATE users SET status='$Status' WHERE EMP_ID = '$EmpId'";
                if ($conn->query($sql)) {
                    $response['status'] = "success";
                    $response['message'] = "User $Status";
                } else {
                    $response['status'] = "error";
                    $response['message'] = "Query failed";
                }

            } else {
                $response['status'] = "error";
                $response['message'] = "User not found";
            }

            echo json_encode($response);
        }

    }

}else{
  authorization($conn);
}