<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id = $_POST["groupfareid"];
    // $deactivatedStatus = $_POST["deactivatedStatus"];

    $sql = "UPDATE groupfare SET deactivated='true' WHERE groupFareId='$id'";

    if (empty($id)) {

        $response["status"] = "error";
        $response["message"] = "groupfareid Is Missing";
        echo json_encode($response);
        exit();

    } else if ($conn->query($sql)) {
        
        $response["status"] = "success";
        $response["message"] = "$id group fare is deactivated";
        echo json_encode($response);

    } else {
        
        $response["status"] = "error";
        $response["message"] = "Update Failed";
        echo json_encode($response);

    }
} else {

    $response["status"] = "error";
    $response["message"] = "Wrong Request Method";
    echo json_encode($response);

}

$conn->close();
