<?php

require "../../emailfunction.php";
require "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__ . "/lib/SslCommerzNotification.php");

include_once(__DIR__ . "/OrderTransaction.php");

use SslCommerz\SslCommerzNotification;

$sslc = new SslCommerzNotification();
$ot = new OrderTransaction();

$tran_id = $_POST['tran_id'];
$amount = $_POST['store_amount'];
$currency = $_POST['currency'];


$sql = $ot->getRecordQuery($tran_id);
$result = $conn->query($sql);

//if b2b $row will have agentId,
//if b2c $row will have userId
$row = $result->fetch_array(MYSQLI_ASSOC);


if ($row['status'] == 'Pending' || $row['status'] == 'Processing') {
  $validated = $sslc->orderValidate($_POST, $tran_id, $amount, $currency);

  if ($validated) {
    $sql = $ot->updateTransactionQuery($tran_id, 'Success');

    if ($conn->query($sql) === TRUE) {
      $tran_id = $_POST['tran_id'];
      $card_issuer = $_POST['card_issuer'];
      $bank_trxId = $_POST['bank_tran_id'];
      $amount = $_POST['amount'];

     /* The code block is checking if the key 'agentId' exists in the array. If it
     does, it means that the transaction is a B2B transaction. Otherwise it is a B2C transaction */
     
      if (isset($row['agentId'])) {

        $_POST['agentId'] = $row['agentId'];
        $ot->saveB2BTransaction($conn, $_POST);

      } else if (isset($row['userId'])) {

        $_POST['userId'] = $row['userId'];
        $ot->saveB2CTransaction($conn, $_POST);

      }
    } else {
    }
  } else {

    echo 'Payment was not valid. Please contact with the merchant';
  }
} else {

  echo 'Invalid Information1';
}
