<?php

require "../../emailfunction.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__ . "/lib/SslCommerzNotification.php");
include_once(__DIR__ . "/db_connection.php");
include_once(__DIR__ . "/OrderTransaction.php");

use SslCommerz\SslCommerzNotification;

$sslc = new SslCommerzNotification();
$tran_id = $_POST['tran_id'];
$amount = $_POST['amount'];
$currency = $_POST['currency'];

$ot = new OrderTransaction();
$sql = $ot->getRecordQuery($tran_id);
$result = $conn->query($sql);
$row = $result->fetch_array(MYSQLI_ASSOC);
$agentId = $row['agentId'];


if ($row['status'] == 'Pending' || $row['status'] == 'Processing') {
  $validated = $sslc->orderValidate($_POST, $tran_id, $amount, $currency);

  if ($validated) {
    $sql = $ot->updateTransactionQuery($tran_id, 'Success');

    if ($conn->query($sql) === TRUE) {
      $tran_id = $_POST['tran_id'];
      $card_issuer = $_POST['card_issuer'];
      $bank_trxId = $_POST['bank_tran_id'];
      $amount = $_POST['amount'];

      DepositRequest($conn, $tran_id, $amount, $agentId, $bank_trxId);

    } else {

    }

  } else {

    echo 'Payment was not valid. Please contact with the merchant';

  }

} else {

  echo 'Invalid Information1';

}
function DepositRequest($conn, $tran_id, $amount, $agentId, $bank_trxId)
{

  $DuplicateItem = $conn->query("SELECT * from deposit_request where transactionId='$tran_id'")->num_rows;

  // print($DuplicateItem);
  if ($DuplicateItem == 0) {

    $DepositId = "";
    $sql = "SELECT * FROM deposit_request ORDER BY depositId DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $outputString = preg_replace('/[^0-9]/', '', $row["depositId"]);
        $number = (int) $outputString + 1;
        $DepositId = "STD$number";
      }
    } else {
      $DepositId = "STD1000";
    }

    $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'"), MYSQLI_ASSOC);

    if (!empty($agentdata)) {
      $CompanyName = $agentdata["company"];
      $CompanyEmail = $agentdata["email"];
    }

    $createdAt = date('Y-m-d H:i:s');

    //Last Amount     
    $amountsql = "SELECT lastAmount FROM `agent_ledger` WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1";
    $result1 = mysqli_query($conn, $amountsql);
    $data1 = mysqli_fetch_array($result1);

    $lastAmount = 0;
    if (!empty($data1['lastAmount'])) {
      $lastAmount = $data1['lastAmount'];
    } else {
      $lastAmount = 0;
    }

    $lessamount = $amount * 0.978;

    $newAmount = $lastAmount + $lessamount;


    $sql = "INSERT INTO `deposit_request`(
                    `agentId`,
                    `depositId`,
                    `sender`,
                    `reciever`,
                    `paymentway`,
                    `paymentmethod`,
                    `transactionId`,
                    `amount`,
                    `ref`,
                    `status`,
                    `createdAt`)
                    VALUES( 
                    '$agentId',
                    '$DepositId',
                    '$CompanyName',
                    'FWT Marchant',
                    'sslcommerce',
                    'SSL',
                    '$tran_id',
                    '$lessamount',
                    '$bank_trxId',
                    'approved',
                    '$createdAt')";

    if ($conn->query($sql) === TRUE) {
      $conn->query("INSERT INTO `agent_ledger`(`agentId`,`deposit`, `lastAmount`,`details`, `transactionId`,`reference`,`createdAt`)
                        VALUES ('$agentId','$lessamount','$newAmount','$lessamount TK Deposit successfully SSL Commerce - PaymentId-$bank_trxId','$tran_id','$DepositId','$createdAt')");

      //send email
      // $adminMessage = "We sent you new deposit request amount of $amount BDT, Which has been approved.";
      // $agentMessage = "Your new deposit request amount of $amount BDT has been accepeted, Thank you";
      // $subject = "Deposit Request Approved";
      // $header = $subject;
      // $property = "Deposit ID: ";
      // $data = $DepositId;

      // sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
      // sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);
      ///////////////////////////

      $response['status'] = 'success';
      $response['message'] = 'Deposit Successfully Done';
      echo json_encode($response);

      //redirect
      header("Location: https://b2b.shopnotour.com/dashboard/depositreq/successful");
      //header("Location: http://localhost:3001/dashboard/account/deposite/successful");
      ////////////////////////
    }
  } else {
    header("Location:  https://b2b.shopnotour.com/dashboard/depositreq/fail");
    exit();
  }

}






?>