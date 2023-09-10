<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include '../../config.php';


if($_SERVER["REQUEST_METHOD"] == 'POST')
{
    $bookingId=$_POST["bookingId"];
    $newTimeLimit=$_POST["newTimeLimit"];


    if($conn->query("UPDATE booking SET `timeLimit` = '$newTimeLimit' WHERE bookingId = '$bookingId'"))
    {
        echo json_encode(
            array(
                "status" => "success",
                "message" => "$bookingId's Time Limit is updated successfully to $newTimeLimit"
            )
        );
    }
    else
    {
        echo json_encode(
            array(
                "status" => "error",
                "message" => "update failed"
            )
        );
    }
} else
{
    echo json_encode(
        array(
            "status" => "error",
            "message" => "wrong request method"
        )
    );
}


?>