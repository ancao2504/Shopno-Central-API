<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId=$_POST["userId"];
    $withdrawalId=$_POST["withdrawalId"];
    $actionBy=$_POST["actionBy"];
    $remarks=$_POST["remarks"];


    $updateWithdrawal=$conn->query(
        "UPDATE `withdraw_req` SET
        `status`= 'rejected',
        `actionBy`= '$actionBy',
        `updatedAt`= CURDATE()
        WHERE 
        `id`= '$withdrawalId'
        AND
        `userId`='$userId'
        "
    );

    if(!$updateWithdrawal)
    {
        echo json_encode(
            [
                "status" => "error",
                "message" => "Withdraw Update Failed"
            ]
        );
        $conn->close();
        exit;

    }
    echo json_encode(
        [
            "status" => "success",
            "message" => "Withdraw Request Rejected Successfully"
        ]
    );
    $conn->close();
    exit;


}