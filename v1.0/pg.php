<?php

require "config.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if (array_key_exists("allStatus", $_GET)) {
    echo json_encode($conn->query("SELECT DISTINCT(`status`) FROM booking")->fetch_all(MYSQLI_ASSOC));
} else if (array_key_exists("bookingId", $_GET) && array_key_exists("status", $_GET)) {
    $bkId = $_GET["bookingId"];
    $status = $_GET["status"];
    if($bkId==""&&$status=="")
    {exit;}
    $conn->query("UPDATE `booking` SET `status`='$status' WHERE `bookingId`='$bkId'");
    echo json_encode($conn->query("SELECT * FROM booking WHERE `bookingId`='$bkId'")->fetch_assoc());
}
