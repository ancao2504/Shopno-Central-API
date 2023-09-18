<?php

require '../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $jsonData = json_decode(file_get_contents('php://input'), true);

    $userId = $jsonData['user_id'];
    $bankName = $jsonData['bankName'];
    $accountNo = $jsonData['accountNo'];
    $mfsNumber = $jsonData['mfsNumber'];
    $bankHolderName = $jsonData['bankHolderName'];
    $amount = $jsonData['amount'];
    $reason = $jsonData['reason'];
    $withdrawType = $jsonData['withdrawType'];

    $userExists = $conn->query("SELECT * FROM `agent` WHERE `userId`='$userId'");

    if ($userExists->num_rows <= 0) {
        echo json_encode(
            [
                "status" => "error",
                "message" => "Agent Does Not Exist"
            ]
        );
        $conn->close();
        exit;
    }

    $lastAmount = $conn->query("SELECT `lastAmount` FROM `agent_ledger` WHERE `userId`='$userId' ORDER BY id DESC LIMIT 1")->fetch_assoc();

    if ( empty($lastAmount) || $lastAmount['lastAmount'] < $amount) {
        echo json_encode(
            [
                "status" => "error",
                "message" => "Withdraw Amount Is Not Available In Ledger"
            ]
        );
        $conn->close();
        exit;
    }

    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT withdrawId FROM withdraw_req ORDER BY id DESC LIMIT 1"));
    if (!empty($data)) {
        $number = (int) filter_var($data["withdrawId"], FILTER_SANITIZE_NUMBER_INT);
        $withDrawId = "STUWR" . ($number + 1);
    } else {
        $withDrawId = "STUWR10000";
    }

    $insertRequest = "INSERT INTO `withdraw_req` 
    (
        `userId`, 
        `senderBankName`, 
        `senderAccountNo`, 
        `senderMfsNumber`, 
        `bankHolderName`, 
        `amount`, 
        `senderReason`, 
        `withdrawType`,
        `status`,
        `withdrawId`
    )
    VALUES
    (
        '$userId',
        '$bankName',
        '$accountNo',
        '$mfsNumber',
        '$bankHolderName',
        '$amount',
        '$reason',
        '$withdrawType',
        'pending',
        '$withDrawId'
    )
    ";
    
    if(!$conn->query($insertRequest))
    {
        echo json_encode(
            [
                "status" => "error",
                "message" => "Insert Query Failed"
            ]
        );
        $conn->close();
        exit;
    }

    echo json_encode(
        [
            "status" => "success",
            "message" => "Withdraw Request Successful"
        ]
    );
}
