<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if (array_key_exists("agentId", $_GET)) {
    $agentId = $_GET["agentId"];
    if (array_key_exists("booking", $_GET)) {
        $TotalBooking = $conn->query("SELECT * FROM booking where agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        //$TotalBookingData =  $conn->query("SELECT * FROM booking ORDER BY lastUpdated DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);

        $TotalBookingData = $conn->query("SELECT bookingId, agentId, subagentId, pnr, gds, status, lastUpdated, deptFrom,
                 arriveTo, tripType, (select company from subagent where subagentId=booking.subagentId) as company from booking where subagentId !='' AND agentId='$agentId' ORDER BY id DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);


        $TotalHoldBooking = $conn->query("SELECT * FROM booking where status ='Hold' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalCancelledBooking = $conn->query("SELECT * FROM booking where status ='Cancelled' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalTicketedBooking = $conn->query("SELECT * FROM booking where status ='Ticketed' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalReissueBooking = $conn->query("SELECT * FROM booking where status ='Reissued' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalReturnBooking = $conn->query("SELECT * FROM booking where status ='Return' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalRefundedBooking = $conn->query("SELECT * FROM booking where status ='Refunded' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalVoidBooking = $conn->query("SELECT * FROM booking where status ='Voided' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalIssueOnProcessBooking = $conn->query("SELECT * FROM booking where status ='Issue In Processing' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalReissueOnProcessBooking = $conn->query("SELECT * FROM booking where status ='Reissue In Processing' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalVoidOnProcessBooking = $conn->query("SELECT * FROM booking where status ='Void In Processing' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalRefundOnProcessBooking = $conn->query("SELECT * FROM booking where status ='Refund In Processing' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalRefundRejectedBooking = $conn->query("SELECT * FROM booking where status ='Refund Rejected' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalVoidRejectedBooking = $conn->query("SELECT * FROM booking where status ='Void Rejected' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;
        $TotalReissueRejectedBooking = $conn->query("SELECT * FROM booking where status ='Reissue Rejected' AND agentId='$agentId' AND subagentId !='' ORDER BY id DESC")->num_rows;

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


    } else if (array_key_exists("subagent", $_GET)) {
        //Agent status information
        $TotalAgent = $conn->query("SELECT * FROM subagent where agentId='$agentId' ORDER BY id DESC")->num_rows;
        $TotalAgentData = $conn->query("SELECT * FROM subagent where agentId='$agentId' ORDER BY id DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
        $TotalActiveAgent = $conn->query("SELECT * FROM subagent where agentId='$agentId' AND status ='active' ORDER BY id DESC")->num_rows;
        $TotalRejectedAgent = $conn->query("SELECT * FROM subagent where agentId='$agentId' AND  status ='rejected' ORDER BY id DESC")->num_rows;
        $TotalPendingAgent = $conn->query("SELECT * FROM subagent where agentId='$agentId' AND status ='pending' ORDER BY id DESC")->num_rows;
        $TotalDeactiveAgent = $conn->query("SELECT * FROM subagent where agentId='$agentId' AND  status ='deactive' ORDER BY id DESC")->num_rows;
        //agent status information
        $Total['TotalAgent'] = $TotalAgent;
        $Total['TotalAgentData'] = $TotalAgentData;
        $Total['AgentActive'] = $TotalActiveAgent;
        $Total['DeactiveAgent'] = $TotalDeactiveAgent;
        $Total['AgentRejected'] = $TotalRejectedAgent;
        $Total['AgentPending'] = $TotalPendingAgent;

    } else if (array_key_exists("deposit", $_GET)) {
        //deposit status information
        $TotalDeposit = $conn->query("SELECT * FROM deposit_request where agentId='$agentId' ORDER BY id DESC")->num_rows;
        $TotalDepositData = $conn->query("SELECT depositId, status, paymentway, amount, (select company from subagent where subagentId=deposit_request.subagentId) as company
         FROM deposit_request where agentId='$agentId' AND subagentId !='' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
        $TotalApproved = $conn->query("SELECT * FROM deposit_request where agentId='$agentId' AND status ='approved' ORDER BY id DESC")->num_rows;
        $TotalApprovedDeposit = $conn->query("SELECT SUM(amount) as totalApprovedAmount FROM `deposit_request` WHERE agentId='$agentId' AND subagentId !='' AND status='approved' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
        $TotalRejected = $conn->query("SELECT * FROM deposit_request where agentId='$agentId' AND status ='rejected' ORDER BY id DESC")->num_rows;
        $TotalRejectedDeposit = $conn->query("SELECT SUM(amount) as totalRejectedAmount FROM `deposit_request` WHERE agentId='$agentId' AND status='rejected'ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
        $TotalPending = $conn->query("SELECT * FROM deposit_request where agentId='$agentId' AND status ='pending' ORDER BY id DESC")->num_rows;
        $TotalPendingDeposit = $conn->query("SELECT SUM(amount) as totalPendingAmount FROM `deposit_request` WHERE agentId='$agentId' AND status='pending' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
        $TotalCheque = $conn->query("SELECT * FROM deposit_request where agentId='$agentId' AND paymentway='Cheque' ORDER BY id DESC")->num_rows;
        $TotalChequeDeposit = $conn->query("SELECT SUM(amount) as totalChequeAmount FROM `deposit_request` WHERE agentId='$agentId' AND paymentway='Cheque' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
        $TotalBankTransfer = $conn->query("SELECT * FROM deposit_request where agentId='$agentId' AND paymentway='bankTransfer' ORDER BY id DESC")->num_rows;
        $TotalBankTransferDeposit = $conn->query("SELECT SUM(amount) as totalBankTransferAmount FROM `deposit_request` WHERE agentId='$agentId' AND paymentway='bankTransfer' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
        $TotalMobileTransfer = $conn->query("SELECT * FROM deposit_request where agentId='$agentId' AND paymentway='mobileTransfer' ORDER BY id DESC")->num_rows;
        $TotalMobileTransferDeposit = $conn->query("SELECT SUM(amount) as totalMobileTransferAmount FROM `deposit_request` WHERE agentId='$agentId' AND paymentway='mobileTransfer' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
        $TotalCash = $conn->query("SELECT * FROM deposit_request where agentId='$agentId' AND paymentway='Cash' ORDER BY id DESC")->num_rows;
        $TotalCashDeposit = $conn->query("SELECT SUM(amount) as totalCashAmount FROM `deposit_request` WHERE agentId='$agentId' AND paymentway='Cash' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);

        //deposit status information
        $Total['TotalDeposit'] = $TotalDeposit;
        $Total['TotalDepositData'] = $TotalDepositData;
        $Total['TotalApproved'] = $TotalApproved;
        $Total['TotalApprovedDeposit'] = $TotalApprovedDeposit[0]['totalApprovedAmount'];
        $Total['TotalRejected'] = $TotalRejected;
        $Total['TotalRejectedDeposit'] = $TotalRejectedDeposit[0]['totalRejectedAmount'];
        $Total['TotalPending'] = $TotalPending;
        $Total['TotalPendingDeposit'] = $TotalPendingDeposit[0]['totalPendingAmount'];
        $Total['TotalCheque'] = $TotalCheque;
        $Total['TotalChequeDeposit'] = $TotalChequeDeposit[0]['totalChequeAmount'];
        $Total['TotalBankTransfer'] = $TotalBankTransfer;
        $Total['TotalBankTransferDeposit'] = $TotalBankTransferDeposit[0]['totalBankTransferAmount'];
        $Total['TotalMobileTransfer'] = $TotalMobileTransfer;
        $Total['TotalMobileTransferDeposit'] = $TotalMobileTransferDeposit[0]['totalMobileTransferAmount'];
        $Total['TotalCash'] = $TotalCash;
        $Total['TotalCashDeposit'] = $TotalCashDeposit[0]['totalCashAmount'];

    }
    echo json_encode($Total);
} else if (array_key_exists("date", $_GET)) {
    $date = $_GET["date"];
    // echo json_encode($result);
    $result = getDashBoardData($date);

    $agentAm = $result[29]["count"];
    $todayTotalTickted = $result[20]["count"];
    $lossProf = $agentAm - $todayTotalTickted;


    $response["holdCount"] = $result[0]["count"];
    $response["issueInProcessingCount"] = $result[1]["count"];
    $response["ticketedCount"] = $result[2]["count"];
    $response["issueRejectedCount"] = $result[3]["count"];
    $response["refundInProcessingCount"] = $result[4]["count"];
    $response["voidInProcessingCount"] = $result[5]["count"];
    $response["reissueInProcessingCount"] = $result[6]["count"];
    $response["refundedCount"] = $result[7]["count"];
    $response["voided"] = $result[8]["count"];
    $response["reissuedCount"] = $result[9]["count"];
    $response["refundRejectedCount"] = $result[10]["count"];
    $response["voidRejectedCount"] = $result[11]["count"];
    $response["reissueRejectedCount"] = $result[12]["count"];
    $response["cancelledCount"] = $result[13]["count"];

    $response["todayFly"] = $result[14]["count"];
    $response["tomorrowFly"] = $result[15]["count"];
    $response["dayAfterTomorrowFLy"] = $result[16]["count"];

    $response["totalPendingDepositAmount"] = $result[17]["count"];
    $response["todayTotalDeposit"] = $result[18]["count"];
    $response["totalRejectedDepositAmount"] = $result[19]["count"];

    $response["todayTotalTicketedAmount"] = $result[20]["count"];

    $response["pendingAgentCount"] = $result[21]["count"];

    $response["totalSearchCount"] = $result[22]["count"];

    $response["totalBookCount"] = $result[23]["count"];

    $response["cancelledBooking"] = $result[30]["count"];

    $response["bookingClearanceCount"] = $result[24]["count"];
    $response["bookingClearanceAmount"] = $result[25]["count"];

    $response["refundCount"] = $result[26]["count"];
    $response["refundAmount"] = $result[27]["count"];

    $response["customerAmount"] = $result[28]["count"];
    $response["agentAmount"] = $result[29]["count"];
    $response["afterMarkUp"] = $response["todayTotalTicketedAmount"];
    $response["lossProfit"] = $lossProf;


    echo json_encode($response);

} else {
    echo json_encode("Error");
}

function getDashBoardData($date)
{   
    $platform='B2B';
    global $conn;

    $sql =
        "SELECT 'Hold' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Hold' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Issue In Processing' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Issue In Processing' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Ticketed' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Ticketed' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Issue Rejected' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Issue Rejected' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Refund In Processing' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Refund In Processing' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Void In Processing' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Void In Processing' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Reissue In Processing' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Reissue In Processing' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Refunded' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Refunded' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Voided' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Voided'  AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Reissued' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Reissued'  AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Refund Rejected' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Refund Rejected' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Void Rejected' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Void Rejected' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Reissue Rejected' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Reissue Rejected' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'Cancelled' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)='$date' AND status = 'Cancelled' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'today' AS category, COUNT(*) AS count
        FROM `booking`
        WHERE DATE(travelDate)='$date' AND status = 'Ticketed' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'tomorrow' AS category, COUNT(*) AS count
        FROM `booking`
        WHERE DATE(travelDate) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND status = 'Ticketed' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'dayAfterTomorrow' AS category, COUNT(*) AS count
        FROM `booking`
        WHERE DATE(travelDate) = DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND status = 'Ticketed' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'totalPendingDepositAmount' AS category, COALESCE(SUM(amount), 0) AS count
        FROM `deposit_request`
        WHERE DATE(createdAt)='$date' AND status = 'pending' 
    
        UNION ALL
    
        SELECT 'totalApprovedDepositAmount' AS category, COALESCE(SUM(amount), 0) AS count
        FROM `deposit_request`
        WHERE DATE(actionAt)='$date' AND status = 'approved' 
    
        UNION ALL
    
        SELECT 'totalRejectedDepositAmount' AS category, COALESCE(SUM(amount), 0) AS count
        FROM `deposit_request`
        WHERE DATE(actionAt)='$date' AND status = 'rejected' 
    
        UNION ALL
    
        SELECT 'todayTotalTicketedAmount' AS category, COALESCE(SUM(netCost),0) AS count
        FROM `booking`
        WHERE DATE(lastUpdated)='$date' AND status='ticketed' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'pendingAgentCount' AS category, COUNT(*) AS count
        FROM `agent`
        WHERE DATE(joinAt)=CURDATE() AND status='pending' 
    
        UNION ALL
    
        SELECT 'totalSearchCount' AS category, COUNT(*) AS count
        FROM search_history
        WHERE DATE(searchTime)=CURDATE() AND platform = '$platform'
    
        UNION ALL 
    
        SELECT 'totalBookCount' AS category, COUNT(*) AS count
        FROM booking
        WHERE DATE(bookedAt)=CURDATE() AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'bookingClearanceCount' AS category, COUNT(*) AS count
        FROM booking 
        WHERE DATE(lastUpdated)=CURDATE() AND status='Issue In Processing' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'bookingClearanceAmount' AS category, COALESCE(SUM(netCost),0) AS count
        FROM booking 
        WHERE DATE(lastUpdated)=CURDATE() AND status='Issue In Processing' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'refundCount' AS category, COUNT(*) AS count
        FROM booking 
        WHERE DATE(lastUpdated)=CURDATE() AND status IN ('Refunded', 'Issue Rejected') AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'refundAmount' AS category, COALESCE(SUM(netCost),0) AS count
        FROM booking 
        WHERE DATE(lastUpdated)=CURDATE() AND status IN ('Refunded', 'Issue Rejected') AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'customerAmount' AS category, COALESCE(SUM(grossCost),0) AS count
        FROM booking 
        WHERE DATE(lastUpdated)=CURDATE() AND status='Ticketed' AND platform = '$platform'
    
        UNION ALL
    
        SELECT 'agentAmount' AS category, COALESCE(SUM(subagentCost),0) AS count
        FROM booking 
        WHERE DATE(lastUpdated)=CURDATE() AND status='Ticketed' AND platform = '$platform'

        UNION ALL
    
        SELECT 'cancelledBooking' AS category, COUNT(*) AS count
        FROM booking 
        WHERE DATE(lastUpdated)=CURDATE() AND status='Cancelled' AND platform = '$platform'
        ";
    
    $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    return $result;

}
$conn->close();


?>