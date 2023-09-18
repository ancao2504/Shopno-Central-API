<?php

require '../../config.php';
require '../../functions.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    
    $userId=$_POST["userId"];
    $id=$_POST["withdrawalId"];
    $actionBy=$_POST["actionBy"];
    $remarks=$_POST["remarks"];
    $platform=$_POST["platform"];
    $fileName = $_FILES["attachment"]["name"];
    
    $imagename="attachment";
    $acceptablesize=5000000;
    $folder="User/$userId/WithdrawMoney";
    $dFFile=date('YmdHis');
    $newFileName= "withdraw$userId".$actionBy.$dFFile;
    $createdAt=date("Y-m-d H:i:s");

    $amountQuery= $conn->query("SELECT `withdrawId` ,`status`, `amount`, `withdrawType` FROM `withdraw_req` WHERE id='$id'")->fetch_assoc();

    if(empty($amountQuery))
    {
        echo json_encode(
            [
                "status" => "error",
                "message" => "Request Does Not Exist"
            ]
        );
        $conn->close();
        exit;
    }
    if($amountQuery["status"]=="approved" || $amountQuery["status"]=="rejected")
    {   $status=$amountQuery['status'];
        echo json_encode(
            [
                "status" => "error",
                "message" => "Request Is Already $status"
            ]
        );
        $conn->close();
        exit;
    }

    $amount=$amountQuery["amount"];
    $withdrawType=$amountQuery["withdrawType"];
    $withdrawalId=$amountQuery["withdrawId"];
   

    $userExists = $conn->query("SELECT * FROM `agent` WHERE `userId`='$userId'");

    if ($userExists->num_rows <= 0) {
        echo json_encode(
            [
                "status" => "error",
                "message" => "User Does Not Exist"
            ]
        );
        $conn->close();
        exit;
    }

    $lastAmountResult = $conn->query("SELECT `lastAmount` FROM `agent_ledger` WHERE `userId`='$userId' ORDER BY id DESC LIMIT 1")->fetch_assoc();


    if ( empty($lastAmountResult) || $lastAmountResult['lastAmount'] < $amount) {
        echo json_encode(
            [
                "status" => "error",
                "message" => "Withdraw Amount Is Not Available In Ledger"
            ]
        );
        $conn->close();
        exit;
    }

    $attachLink=uploadImage($imagename, $acceptablesize, $folder, $fileName, $newFileName);

    $updateWithdrawal=$conn->query(
        "UPDATE `withdraw_req` SET
        `status`= 'approved',
        `attachment`='$attachLink', 
        `actionBy`= '$actionBy',
        `updatedAt`= '$createdAt'
        WHERE 
        `id`= '$id'
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

    $newAmount=$lastAmountResult["lastAmount"] - $amount; 

    $ledgerQuery=$conn->query("INSERT INTO `agent_ledger`
    (
        `userId`,
        `platform`,
        `withdraw`,
        `transactionId`,
        `details`,
        `reference`,
        `actionBy`,
        `createdAt`,
        `lastAmount`
    )VALUES
    (
        '$userId',
        '$platform',
        '$amount',
        '$id',
        '$amount Withdrawal Request Through $withdrawType Approved By $actionBy',
        '$withdrawalId',
        '$actionBy',
        '$createdAt',
        '$newAmount'
    )
    ");
    if(!$ledgerQuery)
    {
        echo json_encode(
            [
                "status" => "error",
                "message" => "Ledger Query Failed"
            ]
        );
        $conn->close();
        exit; 
    }

    echo json_encode(
        [
            "status" => "success",
            "message" => "Withdraw Request Accepted Successfully"
        ]
    );
    $conn->close();

}
