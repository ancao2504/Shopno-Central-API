<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {
    //Booking status information
    $TotalBooking = $conn->query("SELECT * FROM booking")->num_rows;

    $result = $conn->query("SELECT * FROM booking ORDER BY id DESC LIMIT 30");

    $TotalBookingData = array();

    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;
            $agentId = $row['agentId'];

            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);
            $companyname = $data['company'];
            $companyphone = $data['phone'];
            $balanceQuery = mysqli_query($conn, "SELECT * FROM agent_ledger WHERE agentId='$agentId'");
            $balanceData = mysqli_fetch_assoc($balanceQuery);
            $balance = isset($balanceData['lastAmount']) ? $balanceData['lastAmount']:"";

            $TotalBookingDatas = $row;
            $TotalBookingDatas['companyname'] = "$companyname";
            $TotalBookingDatas['companyphone'] = "$companyphone";
            $TotalBookingDatas['balance'] = "$balance";
            $TotalBookingDatas['serial'] = "$count";

            array_push($TotalBookingData, $TotalBookingDatas);
        }
    }

    $TotalHoldBooking = $conn->query("SELECT * FROM booking where status ='Hold' ORDER BY id DESC")->num_rows;
    $TotalCancelledBooking = $conn->query("SELECT * FROM booking where status ='Cancelled' ORDER BY id DESC")->num_rows;
    $TotalTicketedBooking = $conn->query("SELECT * FROM booking where status ='Ticketed' ORDER BY id DESC")->num_rows;
    $TotalReissueBooking = $conn->query("SELECT * FROM booking where status ='Reissued' ORDER BY id DESC")->num_rows;
    $TotalReturnBooking = $conn->query("SELECT * FROM booking where status ='Return' ORDER BY id DESC")->num_rows;
    $TotalRefundedBooking = $conn->query("SELECT * FROM booking where status ='Refunded' ORDER BY id DESC")->num_rows;
    $TotalVoidBooking = $conn->query("SELECT * FROM booking where status ='Voided' ORDER BY id DESC")->num_rows;
    $TotalIssueOnProcessBooking = $conn->query("SELECT * FROM booking where status ='Issue In Processing' ORDER BY id DESC")->num_rows;
    $TotalReissueOnProcessBooking = $conn->query("SELECT * FROM booking where status ='Reissue In Processing' ORDER BY id DESC")->num_rows;
    $TotalVoidOnProcessBooking = $conn->query("SELECT * FROM booking where status ='Void In Processing' ORDER BY id DESC")->num_rows;
    $TotalRefundOnProcessBooking = $conn->query("SELECT * FROM booking where status ='Refund In Processing' ORDER BY id DESC")->num_rows;
    $TotalRefundRejectedBooking = $conn->query("SELECT * FROM booking where status ='Refund Rejected' ORDER BY id DESC")->num_rows;
    $TotalVoidRejectedBooking = $conn->query("SELECT * FROM booking where status ='Void Rejected' ORDER BY id DESC")->num_rows;
    $TotalReissueRejectedBooking = $conn->query("SELECT * FROM booking where status ='Reissue Rejected' ORDER BY id DESC")->num_rows;

    //booking status information
    $Total['TotalBooking'] = $TotalBooking;
    $Total['TotalBookingData'] = $TotalBookingData;
    $Total['Hold'] = $TotalHoldBooking;
    $Total['Cancelled'] = $TotalCancelledBooking;
    $Total['Reissued'] = $TotalReissueBooking;
    $Total['Ticketed'] = $TotalTicketedBooking;
    $Total['Return'] = $TotalReturnBooking;
    $Total['Refunded'] = $TotalRefundedBooking;
    $Total['Void'] = $TotalVoidBooking;

    $Total['IssueOnProcessing'] = $TotalIssueOnProcessBooking;
    $Total['ReissueOnProcessing'] = $TotalReissueOnProcessBooking;
    $Total['VoidOnProcessing'] = $TotalVoidOnProcessBooking;
    $Total['RefundOnProcessing'] = $TotalRefundOnProcessBooking;
    $Total['RefundRejected'] = $TotalRefundRejectedBooking;
    $Total['VoidRejected'] = $TotalVoidRejectedBooking;
    $Total['ReissueRejected'] = $TotalReissueRejectedBooking;

    echo json_encode($Total);
}else if (array_key_exists("agentBookingData", $_GET)) {
    

    $result = $conn->query("SELECT * FROM booking ORDER BY id DESC");

    $TotalBookingData = array();

    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;
            $agentId = $row['agentId'];

            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId' ");
            $data = mysqli_fetch_assoc($query);
            $companyname = $data['company'];
            $companyphone = $data['phone'];

            $TotalBookingDatas = $row;
            $TotalBookingDatas['companyname'] = "$companyname";
            $TotalBookingDatas['companyphone'] = "$companyphone";
            $TotalBookingDatas['serial'] = "$count";

            array_push($TotalBookingData, $TotalBookingDatas);
        }
    }

    //booking status information

    echo json_encode($TotalBookingData);
}