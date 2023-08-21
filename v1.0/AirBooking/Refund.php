<?php

require '../config.php';
require '../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $_POST = json_decode(file_get_contents('php://input'), true);

  $refundId = "";
  $sql1 = "SELECT * FROM refund ORDER BY refundId DESC LIMIT 1";
  $result = $conn->query($sql1);
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $outputString = preg_replace('/[^0-9]/', '', $row["refundId"]);
      $number = (int)$outputString + 1;
      $refundId = "STRF$number";
    }
  } else {
    $refundId = "STRF1000";
  }

  $agentId = $_POST["agentId"];
  $bookingId = $_POST["bookingId"];
  $staffId = $_POST["staffId"];

  $paxDetails = $_POST['passengerData'];

  $passData = array();
  foreach ($paxDetails as $paxDet) {
    $name = $paxDet['name'];
    $ticket = $paxDet['ticket'];

    $data = "($name-$ticket)";
    array_push($passData, $data);
  }

  $dataPax = implode('', $passData);

  $createdTime = date('Y-m-d H:i:s');

  $DateTime = date("D d M Y h:i A");


  if (isset($bookingId)) {
    $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
    $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

    if (!empty($rowTravelDate)) {
      $travelDate = $rowTravelDate['travelDate'];
      $pax = $rowTravelDate['pax'];
      $pnr = $rowTravelDate['pnr'];
      $gds = $rowTravelDate['gds'];
      $bookingId = $rowTravelDate['bookingId'];
      $Type = $rowTravelDate['tripType'];
      $Airlines = $rowTravelDate['airlines'];
      $TicketId = $rowTravelDate['ticketId'];
      $TicketCost = $rowTravelDate['netCost'];
      $arriveTo = $rowTravelDate['arriveTo'];
      $deptFrom = $rowTravelDate['deptFrom'];
    }
  }


  if (isset($agentId)) {
    $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
    $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

    if (!empty($row1)) {
      $agentEmail = $row1['email'];
      $companyname = $row1['company'];
    }
  }

  $staffName;
  $refundBy;
  $staffsql2 = mysqli_query($conn, "SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
  $staffrow2 = mysqli_fetch_array($staffsql2, MYSQLI_ASSOC);


  if (!empty($staffrow2)) {
    $staffName = $staffrow2['name'];

    $refundBy = $staffrow2['name'];
    $refundtextBy = "Refund Request By: $refundBy, $companyname";
  } else {
    $refundtextBy = "Refund Request By: $companyname";
    $staffName = "Agent";
    $refundBy = "Agent";
  }




  $sql = "INSERT INTO `refund`(`refundId`, `agentId`, `bookingId`, `ticketId`, `passengerDetails`,`status`, `requestedBy`, `requestedAt`)
             VALUES ('$refundId','$agentId','$bookingId','$TicketId','$dataPax','pending','$staffName','$createdTime')";


  if ($conn->query($sql) === TRUE) {
    $conn->query("UPDATE `booking` SET `status`='Refund In Processing',`refundId`='$refundId',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
    $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionRef`,`actionBy`, `actionAt`)
            VALUES ('$bookingId','$agentId','Refund In Processing',' ','$refundId','$refundBy','$createdTime')");


    $subject = $header = "New Booking Refund Request";
    $property = "Booking ID: ";
    $data = $bookingId;
    $adminMessage = "We requested for a refund.";
    $agentMessage = "Your Booking Refund Request has been placed please wait for a while.";

    sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
    sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);

    
    $response['status'] = "success";
    $response['InvoiceId'] = "$refundId";
    $response['message'] = "Refund Request Successfully";
    $response['error'] = "Refund Request Successfully";


    echo json_encode($response);
  }
}
