<?php
include "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){ 
    if (array_key_exists("add", $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $_POST = json_decode(file_get_contents("php://input"), true);
            $name = $_POST['name'];
            $date = date("Y-m-d H:i:s");

            $sql = "INSERT INTO `agency` (`name`,created_at) VALUES ('$name','$date')";

            if ($conn->query($sql)) {
                $response['status'] = "success";
                $response['message'] = "New agency created successfully";
            } else {
                $response['status'] = "error";
                $response['message'] = "Query failed";
            }
            echo json_encode($response);
        }
    } else if (array_key_exists("edit", $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $_POST = json_decode(file_get_contents("php://input"), true);
            $id = $_POST['id'];
            $name = $_POST['name'];
            $date = date("Y-m-d H:i:s");
            $sql = "UPDATE `agency` SET `name`='$name', `updated_at`='$date' WHERE id='$id'";

            if ($conn->query($sql)) {
                $response['status'] = "success";
                $response['message'] = "Agency Updated Successfully";
            }else {
                $response['status'] = "error";
                $response['message'] = "Query failed";
            }
            echo json_encode($response);
        }
    } else if (array_key_exists("delete", $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $_POST = json_decode(file_get_contents("php://input"), true);
            $id = $_POST['id'];
            $sql = "DELETE FROM `agency` WHERE id='$id'";
            if ($conn->query($sql)) {
                $response['status'] = "success";
                $response['message'] = "Agency Deleted Successfully";
            } else {
                $response['status'] = "error";
                $response['message'] = "Query Failed";
            }
            echo json_encode($response);
        }
    } else if (array_key_exists("all", $_GET)) {
        $data = $conn->query("SELECT * FROM agency")->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
    }
}else{
  authorization($conn);
}