<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("id", $_GET)) {
    
    $id = $_GET["id"];
    $sql = "SELECT availableSeat FROM groupfare WHERE id='$id' AND deactivated='false' AND deleted='false'";
    $result = $conn->query($sql)->fetch_assoc();

    if (!empty($result)) {
        
        echo json_encode($result);

    } else {
        
        $response["status"] = "error";
        $response["message"] = "Data Not Found";
        echo json_encode($response);

    }
}

$conn->close();
