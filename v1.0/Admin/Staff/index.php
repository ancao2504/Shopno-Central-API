<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists('add', $_GET)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_POST = json_decode(file_get_contents('php://input'), true);

        $Name = $_POST['name'];
        $Email = $_POST['email'];
        $Designation = $_POST['designation'];
        $Phone = $_POST['phone'];
        $Role = $_POST['role'];
        $Password = $_POST['password'];

        $Date = date("Y-m-d H:i:s");
        $DateTime = date("D d M Y h:i A");

        $StaffId = "";
        $sql = "SELECT * FROM admin_stafflist ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["staffId"]);
                $number = (int) $outputString + 1;
                $StaffId = "STAST$number";
            }
        } else {
            $StaffId = "STAST1000";
        }

        $checkUser = "SELECT * FROM admin_stafflist WHERE email='$Email'";
        $result = mysqli_query($conn, $checkUser);

        $checkAgent = "SELECT * FROM agent WHERE email = '$Email'";
        $resultAgent = mysqli_query($conn, $checkAgent);

        if (mysqli_num_rows($result) <= 0 && mysqli_num_rows($resultAgent) > 0) {
            $response['status'] = "error";
            $response['message'] = "User Already Exists as Agent";

        } else if (mysqli_num_rows($result) > 0) {
            $response['status'] = "error";
            $response['message'] = "Staff Already Exists";
        } else {
            $sql = "INSERT INTO `admin_stafflist`(
                `staffId`,
                `name`,
                `email`,
                `password`,
                `phone`,
                `status`,
                `designation`,
                `role`,
                `createdAt`
              )
            VALUES(
                '$StaffId',
                '$Name',
                '$Email',
                '$Password',
                '$Phone',
                'Active',
                '$Designation',
                '$Role',
                '$Date'
            )";

            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Staff Added Successful";

            } else {
                $response['status'] = "error";
                $response['message'] = "Added failed";
            }
        }
        echo json_encode($response);

    }
} else if (array_key_exists('edit', $_GET)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_POST = json_decode(file_get_contents('php://input'), true);

        $Id = $_POST['id'];
        $StaffId = $_POST['staffId'];
        $Name = $_POST['name'];
        $Email = $_POST['email'];
        $Designation = $_POST['designation'];
        $Phone = $_POST['phone'];
        $Role = $_POST['role'];
        $Password = $_POST['password'];

        $Date = date("Y-m-d H:i:s");
        $DateTime = date("D d M Y h:i A");
        $checker = mysqli_query($conn, "SELECT * FROM admin_stafflist WHERE id='$Id' AND staffId='$StaffId'")->fetch_all(MYSQLI_ASSOC);
        if (!empty($checker)) {

            $sql = "UPDATE `admin_stafflist` SET
                    name = '$Name',
                    email = '$Email',
                    password = '$Password',
                    phone ='$Phone',
                    designation = '$Designation',
                    role = '$Role',
                    updateAt  ='$Date'
                WHERE id='$Id' AND staffId='$StaffId'";

            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Staff Update Successful";

            } else {
                $response['status'] = "error";
                $response['message'] = "Update failed";
            }
        } else {
            $response['status'] = "error";
            $response['message'] = "Id or StaffId Not Found";
        }

        echo json_encode($response);

    }
} else if (array_key_exists('delete', $_GET)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_POST = json_decode(file_get_contents('php://input'), true);
         

        $checker = mysqli_query($conn, "SELECT * FROM admin_stafflist WHERE id='$Id' AND staffId='$StaffId'")->fetch_all(MYSQLI_ASSOC);

        if (!empty($checker)) {
            $sql = "DELETE FROM `admin_stafflist` WHERE id='$Id' AND staffId='$StaffId'";

            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Staff Delete Successful";

            } else {
                $response['status'] = "error";
                $response['message'] = "Delete failed";
            }
        } else {
            $response['status'] = "error";
            $response['message'] = "Id or StaffId Not Found";
        }
        echo json_encode($response);

    }
} else if(array_key_exists("all", $_GET)){
    $data = mysqli_query($conn, "SELECT * FROM admin_stafflist")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
        $response['data'] = $data;

    } else {
        $response['status'] = "error";
        $response['message'] = "Data Not Found";
    }
    echo json_encode($response);
}else if(array_key_exists("staffId", $_GET)){
    $StaffId = $_GET['staffId'];
    $data = mysqli_query($conn, "SELECT * FROM admin_stafflist WHERE staffId='$StaffId'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
        $response['data'] = $data;

    } else {
        $response['status'] = "error";
        $response['message'] = "Data Not Found";
    }
    echo json_encode($response);
}
