<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){  


    $TotalSearch = $conn->query("SELECT * FROM search_history ORDER BY id DESC")->num_rows;
    $TotalOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' ORDER BY id DESC")->num_rows;
    $TotalReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' ORDER BY id DESC")->num_rows;
    $TotalMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' ORDER BY id DESC")->num_rows;
      

    $Total['allsearch'] = $TotalSearch;        
    $Total['oneway'] = $TotalOnewaySearch;
    $Total['return'] = $TotalReturnSearch;
    $Total['multicity'] = $TotalMulticitySearch;

    //Booking status information 
    $TotalBooking  = $conn->query("SELECT * FROM booking ORDER BY id DESC")->num_rows;
    //$TotalBookingData =  $conn->query("SELECT * FROM booking ORDER BY lastUpdated DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
    
    $TotalHoldBooking  = $conn->query("SELECT * FROM booking where status ='Hold' ORDER BY id DESC")->num_rows;
    $TotalCancelledBooking  = $conn->query("SELECT * FROM booking where status ='Cancelled' ORDER BY id DESC")->num_rows;
    $TotalTicketedBooking  = $conn->query("SELECT * FROM booking where status ='Ticketed' ORDER BY id DESC")->num_rows;
    $TotalReissueBooking  = $conn->query("SELECT * FROM booking where status ='Reissued' ORDER BY id DESC")->num_rows;
    $TotalReturnBooking  = $conn->query("SELECT * FROM booking where status ='Return' ORDER BY id DESC")->num_rows;
    $TotalRefundedBooking  = $conn->query("SELECT * FROM booking where status ='Refunded' ORDER BY id DESC")->num_rows;
    $TotalVoidBooking  = $conn->query("SELECT * FROM booking where status ='Voided' ORDER BY id DESC")->num_rows;
    $TotalIssueOnProcessBooking  = $conn->query("SELECT * FROM booking where status ='Issue In Processing' ORDER BY id DESC")->num_rows;
    $TotalReissueOnProcessBooking  = $conn->query("SELECT * FROM booking where status ='Reissue In Processing' ORDER BY id DESC")->num_rows;
    $TotalVoidOnProcessBooking  = $conn->query("SELECT * FROM booking where status ='Void In Processing' ORDER BY id DESC")->num_rows;
    $TotalRefundOnProcessBooking  = $conn->query("SELECT * FROM booking where status ='Refund In Processing' ORDER BY id DESC")->num_rows;
    $TotalRefundRejectedBooking  = $conn->query("SELECT * FROM booking where status ='Refund Rejected' ORDER BY id DESC")->num_rows;
    $TotalVoidRejectedBooking  = $conn->query("SELECT * FROM booking where status ='Void Rejected' ORDER BY id DESC")->num_rows;
    $TotalReissueRejectedBooking  = $conn->query("SELECT * FROM booking where status ='Reissue Rejected' ORDER BY id DESC")->num_rows;

     //booking status information
    $Total['TotalBooking'] = $TotalBooking;
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

        
    //Agent status information
    $TotalAgent  = $conn->query("SELECT * FROM agent ORDER BY id DESC")->num_rows;
    $TotalActiveAgent = $conn->query("SELECT * FROM agent where status ='active' ORDER BY id DESC")->num_rows;
    $TotalDeactiveAgent = $conn->query("SELECT * FROM agent where status ='deactive' ORDER BY id DESC")->num_rows;
    $TotalRejectedAgent = $conn->query("SELECT * FROM agent where status ='rejected' ORDER BY id DESC")->num_rows;
    $TotalPendingAgent = $conn->query("SELECT * FROM agent where status ='pending' ORDER BY id DESC")->num_rows;
    $TotalAgentCredit = $conn->query("SELECT * FROM agent where credit > 0 ORDER BY id DESC")->num_rows;


    //Agent failed information
    $TotalAgentFailed  = $conn->query("SELECT * FROM agent_failed ORDER BY id DESC")->num_rows;
    $TotalAgentFailedData = $conn->query("SELECT * FROM  agent_failed ORDER BY id DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
    $TotalAgentCreditData = $conn->query("SELECT agentId, phone, company,
        (SELECT lastAmount FROM `agent_ledger` where agentId = agent.agentId ORDER BY id DESC LIMIT 1) as Balance FROM agent ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);  
    
    //agent status information
    $Total['TotalAgent'] = $TotalAgent;   
    $Total['AgentActive'] = $TotalActiveAgent;   
    $Total['DeactiveAgent'] = $TotalDeactiveAgent;   
    $Total['AgentRejected'] = $TotalRejectedAgent;
    $Total['AgentPending'] = $TotalPendingAgent;
    $Total['AgentCredit'] = $TotalAgentCredit;


    //deposit status information
    $TotalDeposit  = $conn->query("SELECT * FROM deposit_request ORDER BY id DESC")->num_rows;
    $TotalApproved = $conn->query("SELECT * FROM deposit_request where status ='approved' ORDER BY id DESC")->num_rows;
    $TotalApprovedDeposit = $conn->query("SELECT SUM(amount) as totalApprovedAmount FROM `deposit_request` WHERE status='approved' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
    $TotalRejected = $conn->query("SELECT * FROM deposit_request where status ='rejected' ORDER BY id DESC")->num_rows;
    $TotalRejectedDeposit = $conn->query("SELECT SUM(amount) as totalRejectedAmount FROM `deposit_request` WHERE status='rejected'ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
    $TotalPending = $conn->query("SELECT * FROM deposit_request where status ='pending' ORDER BY id DESC")->num_rows;
    $TotalPendingDeposit = $conn->query("SELECT SUM(amount) as totalPendingAmount FROM `deposit_request` WHERE status='pending' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
    $TotalCheque = $conn->query("SELECT * FROM deposit_request where paymentway='Cheque' ORDER BY id DESC")->num_rows;
    $TotalChequeDeposit = $conn->query("SELECT SUM(amount) as totalChequeAmount FROM `deposit_request` WHERE paymentway='Cheque' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
    $TotalBankTransfer = $conn->query("SELECT * FROM deposit_request where paymentway='bankTransfer' ORDER BY id DESC")->num_rows;
    $TotalBankTransferDeposit = $conn->query("SELECT SUM(amount) as totalBankTransferAmount FROM `deposit_request` WHERE paymentway='bankTransfer' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
    $TotalMobileTransfer = $conn->query("SELECT * FROM deposit_request where paymentway='mobileTransfer' ORDER BY id DESC")->num_rows;
    $TotalMobileTransferDeposit = $conn->query("SELECT SUM(amount) as totalMobileTransferAmount FROM `deposit_request` WHERE paymentway='mobileTransfer' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
    $TotalCash = $conn->query("SELECT * FROM deposit_request where paymentway='Cash' ORDER BY id DESC")->num_rows;
    $TotalCashDeposit = $conn->query("SELECT SUM(amount) as totalCashAmount FROM `deposit_request` WHERE paymentway='Cash' ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);    

    //deposit status information
    $Total['TotalDeposit'] = $TotalDeposit;
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


echo json_encode($Total);


}else{
  authorization($conn);
}


    
    