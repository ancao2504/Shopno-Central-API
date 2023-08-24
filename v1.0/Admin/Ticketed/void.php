<?php

require '../../config.php';
require '../../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require "../../vendor/autoload.php";

if (array_key_exists("bookingId", $_GET)) {
    $voidId = $_GET["bookingId"];

    $sql = "SELECT * FROM `void` where bookingId='$voidId'";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $agentId = $row['agentId'];
            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);
            $companyname = $data['company'];

            $response = $row;
            $response['companyname'] = "$companyname";
            array_push($return_arr, $response);
        }
    }

    echo json_encode($return_arr);
} else if (array_key_exists("approved", $_GET)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $_POST = json_decode(file_get_contents('php://input'), true);

        $bookingId = $_POST['bookingId'];
        $agentId = $_POST['agentId'];
        $actionBy = $_POST['actionBy'];
        $voidId = $_POST['voidId'];


        $createdTime = date("Y-m-d H:i:s");


        if (isset($bookingId)) {
            $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
            $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

            if (!empty($rowTravelDate)) {
                $travelDate = $rowTravelDate['travelDate'];
                $staffId = $rowTravelDate['staffId'];
                $subagentId = $rowTravelDate["subagentId"];
                $pax = $rowTravelDate['pax'];
                $gds = $rowTravelDate['gds'];
                $pnr = $rowTravelDate['pnr'];
                $Type = $rowTravelDate['tripType'];
                $Airlines = $rowTravelDate['airlines'];
                $TicketId = $rowTravelDate['ticketId'];
                $TicketCost = $rowTravelDate['netCost'];
                $subagentCost = $rowTravelDate['subagentCost'];
                $arriveTo = $rowTravelDate['arriveTo'];
                $deptFrom = $rowTravelDate['deptFrom'];
                $tripType = $rowTravelDate['tripType'];
                $status = $rowTravelDate['status'];
            }
        }

        if ($status == 'Void In Processing') {
          
            if (isset($agentId)) {
                $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
                $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                if (!empty($row1)) {
                    $agentEmail = $row1['email'];
                    $companyname = $row1['company'];
                }
            }

            $staffName = '';
            $voidtextBy = '';
            $staffsql2 = mysqli_query($conn, "SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
            $staffrow2 = mysqli_fetch_array($staffsql2, MYSQLI_ASSOC);

            if (!empty($staffrow2)) {
                $staffName = $staffrow2['name'];

                $voidBy = $staffrow2['name'];
                $voidtextBy = "Void Request Approve By: $voidBy, $companyname";
            } else {

                $voidtextBy = "Void Request Approve By: $companyname";
            }

            // if(isset($voidId)){
            //     $sqlvoid = mysqli_query($conn, "SELECT * FROM void WHERE bookingId='$bookingId'");
            //     $rowsqlvoid = mysqli_fetch_array($sqlvoid,MYSQLI_ASSOC);

            //     if(!empty($rowsqlvoid)){
            //         $voidrequestedBy = $rowsqlvoid['requestedBy'];
            //         $voidrequestedAt = $rowsqlvoid['requestedAt'];

            //     }
            // }

            $refundAmount = $TicketCost - 500;
            $sarefundAmount = $subagentCost - 500;

            $checkBalanced = mysqli_query($conn, "SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' ORDER BY id DESC LIMIT 1");
            $rowcheckBalanced = mysqli_fetch_array($checkBalanced, MYSQLI_ASSOC);
            if (!empty($rowcheckBalanced)) {
                $lastAmount = $rowcheckBalanced['lastAmount'];
            }

            $newBalance = $lastAmount + $refundAmount;

            $subagentsql1 = mysqli_query($conn, "SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' AND subagentId ='$subagentId'
            ORDER BY id DESC LIMIT 1");
            $subagentrow1 = mysqli_fetch_array($subagentsql1, MYSQLI_ASSOC);
            if (!empty($subagentrow1)) {
                $salastAmount = $subagentrow1['lastAmount'];
            }

            $sanewBalance = $salastAmount + $sarefundAmount;

            $sql = "UPDATE `void` SET `status`='approved',`refundAmount`='$refundAmount',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";

            if ($conn->query($sql) === true) {
                $conn->query("INSERT INTO `agent_ledger`(`agentId`,`void`, `lastAmount`, `transactionId`, `details`, `reference`,`createdAt`)
         VALUES ('$agentId','$refundAmount','$newBalance','$bookingId','Voided Money $TicketId Ticket Invoice $Type Air Ticket $deptFrom - $arriveTo with carrier $Airlines was Requested By $actionBy','$voidId','$createdTime')");

                $conn->query("UPDATE `booking` SET `status`='Voided',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
                $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`,`actionAt`)
                VALUES ('$bookingId','$agentId','Voided',' ','$actionBy','$createdTime')");

                $subject = $header = "Booking Void Request Accepted";
                $property = "Booking ID: ";
                $data = $bookingId;
                $adminMessage = "Our Booking Void Request has been Accepted.";
                $agentMesssage = "Your Booking Void Request has been Accepted.";
                sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
                sendToAgent($subject, $agentMesssage, $agentId, $header, $property, $data);

                $response['status'] = "success";
                $response['InvoiceId'] = "$voidId";
                $response['message'] = "Void Approved Successfully";
            }
        } else {
            $response['status'] = "error";
            $response['message'] = "Already Voided";
        }
        echo json_encode($response);
    }
} else if (array_key_exists("reject", $_GET)) {

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_POST = json_decode(file_get_contents('php://input'), true);

        $bookingId = $_POST['bookingId'];
        $agentId = $_POST['agentId'];
        $actionBy = $_POST['actionBy'];
        $remarks = $_POST['remarks'];

        $createdTime = date("Y-m-d H:i:s");
        $DateTime = date("D d M Y h:i A");

        if (isset($bookingId)) {
            $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
            $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

            if (!empty($rowTravelDate)) {
                $travelDate = $rowTravelDate['travelDate'];
                $subagentId = $rowTravelDate['subagentId'];
                $pax = $rowTravelDate['pax'];
                $gds = $rowTravelDate['gds'];
                $pnr = $rowTravelDate['pnr'];
                $Type = $rowTravelDate['tripType'];
                $Airlines = $rowTravelDate['airlines'];
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

        $sql = "UPDATE `void` SET `status`='rejected',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";

        if ($conn->query($sql) === true) {
            $conn->query("UPDATE `booking` SET `status`='Void Rejected',`lastUpdated`='$createdTime' WHERE bookingId='$bookingId'");
            $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                VALUES ('$bookingId','$agentId','Void Rejected',' ','$actionBy','$createdTime')");

            $subject = $header = "Booking Void Request Cancelled";
            $property = "Booking ID: ";
            $data = $bookingId;
            $adminMessage = "Our Booking Void Request has been Cancelled.";
            $agentMesssage = "Your Booking Void Request has been Cancelled.";
            sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
            sendToAgent($subject, $agentMesssage, $agentId, $header, $property, $data);

            $response['status'] = "success";
            $response['InvoiceId'] = "$bookingId";
            $response['message'] = "Void Rejected Successfully";
        }
        echo json_encode($response);
    }
}
