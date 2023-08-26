<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json_data = json_decode(file_get_contents("php://input"), true);

    $gfId = $json_data["gfId"];
    $bookingId = $json_data["bookingId"];
    $updatedAt=date("Y-m-d H:i:s");

    $sql = "UPDATE `gf_booking` SET `status`='approved', `updatedAt`='$updatedAt' WHERE `groupFareId`='$gfId' AND `bookingId`='$bookingId'";

    if ($conn->query($sql)) {
        $response["status"] = "success";
        $response["message"] = "Booking Approved Successfully";
        echo json_encode($response);
    } else {
        $response["status"] = "error";
        $response["message"] = "status update failed";
        echo json_encode($response);
    }
} else {
    $response["status"] = "error";
    $response["message"] = "Method not allowed";
    echo json_encode($response);
}
