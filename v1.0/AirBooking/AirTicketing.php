<?php

require '../config.php';
require '../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;

require "../vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $agentId = $_POST["agentId"];
    $bookingId = $_POST["bookingId"];
    $staffId = $_POST["staffId"];

    //$useFromBonus = $_POST["useFromBonus"];
    $createdTime = date('Y-m-d H:i:s');

    $DateTime = date("D d M Y h:i A");

    if (isset($bookingId)) {
        $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
        $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

        if (!empty($rowTravelDate)) {
            $travelDate = $rowTravelDate['travelDate'];
            $pax = $rowTravelDate['pax'];
            $Type = $rowTravelDate['tripType'];
            $Airlines = $rowTravelDate["airlines"];
            $From = $rowTravelDate['deptFrom'];
            $To = $rowTravelDate['arriveTo'];
            $Route = "$From - $To";
            $PNR = $rowTravelDate['pnr'];
            $GDS = $rowTravelDate['gds'];
            $Status = $rowTravelDate['status'];
            $netCost = $rowTravelDate["netCost"];
        }
    }

    if ($Status == 'Issue In Processing') {
        $response['status'] = 'Success';
        $response['message'] = 'Your booking has been already Issue In Processing';
        echo json_encode($response);
        exit();
    }

    $creditBalance = 0;
    if (isset($agentId)) {
        $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

        if (!empty($row1)) {
            $agentEmail = $row1['email'];
            $companyname = $row1['company'];
            $bonus = $row1['bonus'];
            $creditBalance = $row1['credit'];
        }
    }

    $staffName;
    $staffsql2 = mysqli_query($conn, "SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
    $staffrow2 = mysqli_fetch_array($staffsql2, MYSQLI_ASSOC);

    if (!empty($staffrow2)) {
        $staffName = $staffrow2['name'];
        $Message = "Dear $companyname, Your staff $staffName  $Route  $Type on $Airlines,
             air ticket booking issue request has been on process at $DateTime. Thank you
              again for booking with Flyway International";

        $OwnerMessage = "Dear Flyway International, Our staff $staffName has been
              requested for $Route $Type  on $Airlines,  air ticket which has been issue in
              Process on $DateTime";

        $IssueBy = $staffrow2['name'];
        $IssuetextBy = "Issue Request By: $IssueBy, $companyname";
    } else {
        $IssueBy = 'Agent';
        $Message = "Dear $companyname, Your $Route $Type  on $Airlines,
          air ticket booking issue request has been on process at $DateTime. Thank you
           again for booking with Flyway International";

        $OwnerMessage = "Dear Flyway International, We have been
          requested for $Route $Type on $Airlines, air ticket which has been issue in
          process on at $DateTime";

        $IssuetextBy = "Issue Request By: $companyname";
    }

    // //Bonus
    // $sqlBonus='';
    //   if($useFromBonus == "yes"){
    //     $bonusRemaining = $bonus-100;
    //      $conn->query("UPDATE `agent` SET `bonus`='$bonusRemaining' where agentId='$agentId'");
    //     $netCost -= 100;
    //     $bonusMessage ="and Use 100 Taka From Bonus";

    //   }else if($useFromBonus == "no"){
    //       $bonusRemaining = $bonus;
    //        $conn->query("UPDATE `agent` SET `bonus`='$bonus' where agentId='$agentId'");
    //       $bonusMessage ="";
    //   }

    $bonusMessage = "";

    $sql1 = mysqli_query($conn, "SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId'
            ORDER BY id DESC LIMIT 1");
    $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);
    if (!empty($row1)) {
        $lastAmount = $row1['lastAmount'];
    } else {
        $lastAmount = 0;
    }

    if ($lastAmount >= $netCost) {
        $newBalance = $lastAmount - $netCost;
    } else if ($lastAmount <= 0 && $creditBalance >= $netCost) {

        $newBalance = $lastAmount - $netCost;
        $creditRemain = $creditBalance - $netCost;
        $conn->query("UPDATE `agent` SET `credit`='$creditRemain' where agentId='$agentId'");

    } else if ($lastAmount > 0 && ($lastAmount + $creditBalance) >= $netCost) {
        $newBalance = $lastAmount - $netCost;
        $creditRemain = $creditBalance + $newBalance;

        $conn->query("UPDATE `agent` SET `credit`='$creditRemain' where agentId='$agentId'");
    } else if ($creditBalance >= $netCost) {
        $newBalance = $lastAmount - $netCost;
        $creditRemain = $creditBalance - $netCost;
        $conn->query("UPDATE `agent` SET `credit`='$creditRemain' where agentId='$agentId'");
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Insufficient Balance';
        echo json_encode($response);
        exit();
    }

    $LeaderUpdate = "INSERT INTO `agent_ledger`(`agentId`,`platform`,`purchase`, `lastAmount`, `transactionId`, `details`, `reference`,`actionBy`,`createdAt`)
        VALUES ('$agentId','B2B','$netCost','$newBalance','$bookingId','$Type Air Ticket $Route - $Airlines By $IssueBy $bonusMessage','$bookingId','','$createdTime')";

    if ($conn->query($LeaderUpdate) === true) {

        $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`actionBy`, `actionAt`)
                    VALUES ('$bookingId','$agentId','Issue In Processing','$IssueBy','$createdTime')");

        $conn->query("UPDATE `booking` SET `status`='Issue In Processing',`netCost`='$netCost',`bonus`='100',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
        $subject = $header = "Booking Issue Request";
        $property = "Booking ID: ";
        $data = $bookingId;
        $adminMessage = "Our Issue Booking Request has been confirmed.";
        $agentMessage = "Your Issue Booking Request has been
    confirmed please wait for while for ticketed. Thank you";

        sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
        sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);

        $response['status'] = "success";
        $response['InvoiceId'] = "$bookingId";
        $response['message'] = "Ticketing Request Successfully";

        echo json_encode($response);
    }
}
