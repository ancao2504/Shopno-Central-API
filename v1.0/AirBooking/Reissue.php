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

  $reissueId = "";
  $sql1 = "SELECT * FROM reissue ORDER BY reissueId DESC LIMIT 1";
  $result = $conn->query($sql1);
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $outputString = preg_replace('/[^0-9]/', '', $row["reissueId"]);
      $number = (int)$outputString + 1;
      $reissueId = "STRI$number";
    }
  } else {
    $reissueId = "STRI1000";
  }

  $paxDetails = $_POST['passengerData'];

  $agentId = $_POST["agentId"];
  $bookingId = $_POST["bookingId"];
  $staffId = $_POST["staffId"];
  $Pax = count($_POST['passengerData']);
  $passengerData = $_POST["passengerData"];
  $requestedBy = $_POST["requestedBy"];
  $reissuedate = $_POST["date"];

  $PaxData = array();
  $passData = array();
  foreach ($paxDetails as $paxDet) {
    $name = $paxDet['name'];
    $ticket = $paxDet['ticket'];
    // $gender = $paxDet['gender'];
    // $passenType = $paxDet['type'];

    $passengerDataHTML = '
    <tr>
      <td
        style="
          padding-left: 20px;
          padding-bottom: 5px;
          vertical-align: top;
        "
      >
      ' . $name . '
      </td>     
      <td
        style="
          padding-left: 20px;
          padding-bottom: 5px;
          vertical-align: top;
        "
      >
        ' . $ticket . '
      </td>
    </tr>';

    $data = "($name-$ticket)";
    array_push($PaxData, $data);
    array_push($passData, $passengerDataHTML);
  }

  $dataPax = implode('', $PaxData);
  $passengerDataTable = implode('', $passData);

  $createdTime = date('Y-m-d H:i:s');

  $DateTime = date("D d M Y h:i A");


  if (isset($bookingId)) {
    $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
    $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

    if (!empty($rowTravelDate)) {
      $travelDate = $rowTravelDate['travelDate'];
      $pax = $rowTravelDate['pax'];
      $Type = $rowTravelDate['tripType'];
      $Airlines = $rowTravelDate['airlines'];
      $gds = $rowTravelDate['gds'];
      $pnr = $rowTravelDate['pnr'];
      $TicketId = $rowTravelDate['ticketId'];
      $TicketCost = $rowTravelDate['netCost'];
      $arriveTo = $rowTravelDate['arriveTo'];
      $deptFrom = $rowTravelDate['deptFrom'];
      $tripType = $rowTravelDate['tripType'];
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
  $staffsql2 = mysqli_query($conn, "SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
  $staffrow2 = mysqli_fetch_array($staffsql2, MYSQLI_ASSOC);

  if (!empty($staffrow2)) {
    $staffName = $staffrow2['name'];
    $reissueBy = $staffrow2['name'];
    $reissuetextBy = "Reissue Request By: $reissueBy, $companyname";
  } else {

    $reissuetextBy = "Reissue Request By: $companyname";
  }

  $sql = "INSERT INTO `reissue`(`reissueId`, `agentId`, `bookingId`, `ticketId`,`passengerDetails`,`reissueDate`,`status`, `requestedBy`, `requestedAt`)
             VALUES ('$reissueId','$agentId','$bookingId','$TicketId','$dataPax','$reissuedate','pending','$requestedBy','$createdTime')";


  if ($conn->query($sql) === TRUE) {
    $conn->query("UPDATE `booking` SET `status`='Reissue In Processing',`reissueId`='$reissueId',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
    $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionRef`,`actionBy`,`actionAt`)
            VALUES ('$bookingId','$agentId','Reissue In Processing',' ','$reissueId','$requestedBy','$createdTime')");


    $subject = $header = "New Booking Reissue Request";
    $property = "Booking ID: ";
    $data = $bookingId;
    $adminMessage = "We have placed a new Reissue Request.";
    $agentMessage = "Your Booking Reissue Request has been placed please wait for a while.";

    sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);
    sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);


    $response['status'] = "success";
    $response['InvoiceId'] = "$reissueId";
    $response['message'] = "Ticket Reissue Request Successfully";

    echo json_encode($response);
  }
}
