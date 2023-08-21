<?php

require '../../config.php';
require '../../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $id = $_POST['id'];
    $agentId = $_POST['agentId'];
    $actionBy = $_POST['actionBy'];
    $reason = $_POST['reason'];

    //Agent Info
    $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where agentId='$agentId' AND platform='B2B'"));
    $companyName = $agentdata['company'];
    $companyEmail = $agentdata['email'];

    //Last Amount
    $amountsql = "SELECT lastAmount, deposit FROM `agent_ledger` WHERE agentId='$agentId' AND platform='B2B' ORDER BY id DESC LIMIT 1";
    $result1 = mysqli_query($conn, $amountsql);
    $data1 = mysqli_fetch_array($result1);

    $lastAmount = $data1['lastAmount'];
    $deposit = $data1['deposit'];
    $afterDeposit = $lastAmount + $deposit;

    //Data
    $fetch = "SELECT * FROM deposit_request WHERE id='$id' AND platform='B2B'";
    $result = mysqli_query($conn, $fetch);
    $data = mysqli_fetch_array($result);

    $id = $data['id'];
    $agentId = $data['agentId'];
    $staffId = $data['staffId'];

    $depositId = $data['depositId'];
    $transactionId = $data['transactionId'];
    $ref = $data['ref'];
    $amount = $data['amount'];
    $paymentwaymethod = $data['paymentmethod'];
    $paymentway = $data['paymentway'];

    $newAmount = $lastAmount + $amount;

    $Time = date("Y-m-d H:i:s");
    $DateTime = date("D d M Y h:i A");

    $staffName = "";
    $staffsql = mysqli_query($conn, "SELECT * FROM `staffList` where staffId='$staffId'");
    $staffdata = mysqli_fetch_array($staffsql);
    if (!empty($staffdata)) {
        $staffName = $staffdata['name'];
        $Message = "Dear $companyName, Your Deposit Request has been
        Rejected amount of $amount BDT on $Time";

        $Message1 = "Dear Flyway International,  Our
              requested deposit request amount of $amount BDT on $Time which has been rejected";
    } else {
        $Message = "Dear $companyName, Your Deposit Request has been
              Rejected amount of $amount BDT on $DateTime";

        $Message1 = "Dear Flyway International, Our Stuff $staffName has been
              requested for deposit request amount of $amount BDT on $Time which has been rejected";
    }

    $sql1 = "UPDATE deposit_request SET status='rejected', remarks='$reason',rejectBy='$actionBy',actionAt='$Time' WHERE id='$id' AND platform='B2B'";

    if ($conn->query($sql1) === true) {

        $subject = $header = "Deposit Request Rejected";
        $property = "Deposit ID: ";
        $data = $depositId;
        $adminMessage = "We Send you deposit request amount of $amount BDT, 
        Which has been Rejected";
        $agentMessage = "Your new deposit request amount of  $amount BDT 
        has been rejected, due to lack of valid infromation. Please try again";
        sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
        sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);

        $response['status'] = "success";
        $response['message'] = "Deposit Rejected Successful";
    }

    echo json_encode($response);
}
$conn->close();
