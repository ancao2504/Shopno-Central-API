<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


 if(array_key_exists("search",$_GET)){
        $TodayAgentwise = array();

    //TOTAL STATS
    $TotalSearch = $conn->query("SELECT * FROM search_history ORDER BY id DESC")->num_rows;
    $TotalOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' ORDER BY id DESC")->num_rows;
    $TotalReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' ORDER BY id DESC")->num_rows;
    $TotalMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' ORDER BY id DESC")->num_rows;

    $TotalSearchList = $conn->query("SELECT search_history.searchId as SLNO,search_history.agentId, agent.phone, agent.company,search_history.searchtype,
                        CONCAT(search_history.DepFrom,'-',search_history.ArrTo) as Routes
                        FROM `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId ORDER BY search_history.id DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);

    $TotalAgentWiseOnewaySearch = $conn->query("SELECT search_history.agentId, agent.company,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId where search_history.searchtype='oneway'  GROUP BY 
            search_history.agentId ORDER BY Search DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
            
    $TotalAgentWiseReturnSearch = $conn->query("SELECT search_history.agentId, agent.company,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId where search_history.searchtype='return' GROUP BY 
            search_history.agentId ORDER BY Search DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
            
        $TotalAgentWiseSearch = array_merge($TotalAgentWiseOnewaySearch,$TotalAgentWiseReturnSearch);
        array_multisort(array_column($TotalAgentWiseSearch, 'Search'), SORT_ASC, $TotalAgentWiseSearch);

    $TotalInactiveAgentWiseSearch = $conn->query("SELECT company, phone FROM agent WHERE agentId NOT IN 
            (SELECT agentId FROM search_history) ORDER BY id DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
    
            
    $TotalStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy, search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy ORDER BY Search DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);

    $TotalDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0  ORDER BY Search DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);

        $Total['allsearch'] = $TotalSearch;        
        $Total['allsearchlist'] = $TotalSearchList;
        $Total['oneway'] = $TotalOnewaySearch;
        $Total['return'] = $TotalReturnSearch;
        $Total['multicity'] = $TotalMulticitySearch;
        $Total['agentwise'] = $TotalAgentWiseSearch;
        $Total['staffwise'] = $TotalStaffWiseSearch;
        $Total['destinationwise'] = $TotalDestination;
        $Total['inactiveagentwise'] = $TotalInactiveAgentWiseSearch;
        
 }else if(array_key_exists("booking",$_GET)){
        //Booking status information 
     $TotalBooking  = $conn->query("SELECT * FROM booking ORDER BY id DESC")->num_rows;
     //$TotalBookingData =  $conn->query("SELECT * FROM booking ORDER BY lastUpdated DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
     
        $result = $conn->query("SELECT * FROM booking ORDER BY lastUpdated DESC LIMIT 30");

        $TotalBookingData = array();

        $count = 0;
        if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()){
                $count++;
                $agentId = $row['agentId'];

                $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
                $data = mysqli_fetch_assoc($query);
                $companyname = $data['company'];
                $companyphone = $data['phone'];

                $TotalBookingDatas = $row;
                $TotalBookingDatas['companyname'] ="$companyname";
                $TotalBookingDatas['companyphone'] ="$companyphone";
                $TotalBookingDatas['serial']="$count";

                array_push($TotalBookingData, $TotalBookingDatas);
                }
        }
     

     
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

        
 }else if(array_key_exists("agent",$_GET)){
    //Agent status information
     $TotalAgent  = $conn->query("SELECT * FROM agent ORDER BY id DESC")->num_rows;
     $TotalAgentData =  $conn->query("SELECT * FROM agent ORDER BY id DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
     $TotalActiveAgent = $conn->query("SELECT * FROM agent where status ='active' ORDER BY id DESC")->num_rows;
     $TotalDeactiveAgent = $conn->query("SELECT * FROM agent where status ='deactive' ORDER BY id DESC")->num_rows;
     $TotalRejectedAgent = $conn->query("SELECT * FROM agent where status ='rejected' ORDER BY id DESC")->num_rows;
     $TotalPendingAgent = $conn->query("SELECT * FROM agent where status ='pending' ORDER BY id DESC")->num_rows;
     //$TotalCreditAgent = $conn->query("SELECT * FROM agent where status ='pending' ORDER BY id DESC")->num_rows;
    


     //Agent failed information
     $TotalAgentFailed  = $conn->query("SELECT * FROM agent_failed ORDER BY id DESC")->num_rows;
     $TotalAgentFailedData = $conn->query("SELECT * FROM  agent_failed ORDER BY id DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC); 
     $TotalAgentCreditData = $conn->query("SELECT agentId, phone, company,
         (SELECT lastAmount FROM `agent_ledger` where agentId = agent.agentId ORDER BY id DESC LIMIT 1) as balance FROM agent Having balance <  0")->fetch_all(MYSQLI_ASSOC);  
     
      //agent status information
    $Total['TotalAgent'] = $TotalAgent;
    $Total['TotalAgentData'] = $TotalAgentData;   
    $Total['AgentActive'] = $TotalActiveAgent;   
    $Total['DeactiveAgent'] = $TotalDeactiveAgent;   
    $Total['AgentRejected'] = $TotalRejectedAgent;
    $Total['AgentPending'] = $TotalPendingAgent;
    $Total['AgentCredit'] = count($TotalAgentCreditData);
    $Total['AgentCreditData'] = $TotalAgentCreditData;

    //Agent failed information
    $Total['TotalAgentFailed'] = $TotalAgentFailed;
    $Total['TotalAgentFailedData'] = $TotalAgentFailedData;
 }else if(array_key_exists("deposit",$_GET)){
    //deposit status information
     $TotalDeposit  = $conn->query("SELECT * FROM deposit_request ORDER BY id DESC")->num_rows;
     $TotalDepositData = $conn->query("SELECT agent.agentId, agent.company, agent.phone, deposit_request.depositId,
      deposit_request.staffId, deposit_request.sender, deposit_request.reciever, deposit_request.paymentway,
      deposit_request.paymentmethod, deposit_request.transactionId, deposit_request.chequeIssueDate, deposit_request.ref,
      deposit_request.amount, deposit_request.attachment, deposit_request.createdAt, deposit_request.depositBy,
      deposit_request.status, deposit_request.remarks, deposit_request.approvedBy, deposit_request.rejectBy, deposit_request.actionAt
       FROM deposit_request INNER JOIN agent ON agent.agentId = deposit_request.agentId ORDER BY deposit_request.id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
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

$conn->close();

?>