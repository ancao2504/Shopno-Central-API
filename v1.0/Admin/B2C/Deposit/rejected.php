<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $id = $_POST['id'];
    $userId = $_POST['userId'];
    $actionBy = $_POST['actionBy'];
    $reason = $_POST['reason'];

    //Agent Info
    $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where userId='$userId' AND platform='B2C'"));
    $Name = $agentdata['name'];
    $companyEmail = $agentdata['email'];

    //Last Amount
    $amountsql = "SELECT lastAmount, deposit FROM `agent_ledger` WHERE userId='$userId' AND platform='B2C' ORDER BY id DESC LIMIT 1";
    $result1 = mysqli_query($conn, $amountsql);
    $data1 = mysqli_fetch_array($result1);

    $lastAmount = $data1['lastAmount'];
    $deposit = $data1['deposit'];
    $afterDeposit = $lastAmount + $deposit;

    //Data
    $fetch = "SELECT * FROM deposit_request WHERE id='$id' AND platform='B2C'";
    $result = mysqli_query($conn, $fetch);
    $data = mysqli_fetch_array($result);

    $id = $data['id'];
    $userId = $data['userId'];
    $depositId = $data['depositId'];
    $transactionId = $data['transactionId'];
    $ref = $data['ref'];
    $amount = $data['amount'];
    $paymentwaymethod = $data['paymentmethod'];
    $paymentway = $data['paymentway'];

    $newAmount = $lastAmount + $amount;

    $Time = date("Y-m-d H:i:s");
    $DateTime = date("D d M Y h:i A");

    
    $sql1 = "UPDATE deposit_request SET status='rejected', rejectBy='$actionBy', remarks='$reason',actionAt='$Time' WHERE id='$id' AND platform='B2C'";

    if ($conn->query($sql1) === true) {
                $response['status'] = "success";
                $response['message'] = "Deposit Rejected Successful";
    }

    echo json_encode($response);

}
$conn->close();
