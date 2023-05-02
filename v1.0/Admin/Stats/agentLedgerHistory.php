<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("ledger", $_GET)) {

    //TOTAL STATS
    // $TotalAgentLedger = $conn->query("SELECT * FROM agent_ledger ORDER BY id DESC")->num_rows;
    $TotalPurchase = $conn->query("SELECT * FROM agent_ledger WHERE purchase <>'0' ORDER BY id  DESC ")->num_rows;

    //  $Total['AllAgentLedger'] = $TotalAgentLedger; 
     $Total['TotalSell'] = $TotalPurchase; 

     //TODAY STATS
     $TodaysAgentLedger = $conn->query("SELECT * FROM agent_ledger where purchase !='0' AND createdAt>= CURRENT_DATE ORDER BY id DESC")->num_rows;

     //yesterday  STATS
     $Yesterdate = date('Y-m-d', strtotime('-1 days'));
     $YesterAgentLedger = $conn->query("SELECT * FROM agent_ledger where purchase !='0' AND createdAt>= '$Yesterdate' ORDER BY id DESC")->num_rows;
   

     //last 7 STATS
     $Last7date = date('Y-m-d', strtotime('-7 days'));
     $Last7DaysAgentLedger = $conn->query("SELECT * FROM agent_ledger where purchase !='0' AND createdAt>= '$Last7date' ORDER BY id DESC")->num_rows;

     //last 15 STATS
     $Last15date = date('Y-m-d', strtotime('-15 days'));
     $Last15DaysAgentLedger = $conn->query("SELECT * FROM agent_ledger where purchase !='0' AND createdAt>= '$Last15date' ORDER BY id DESC")->num_rows;

     //last 30 STATS
     $Last30date = date('Y-m-d', strtotime('-30 days'));
     $Last30DaysAgentLedger = $conn->query("SELECT * FROM agent_ledger where purchase !='0' AND createdAt>= '$Last30date' ORDER BY id DESC")->num_rows;

     //last 90 STATS
     $Last90date = date('Y-m-d', strtotime('-90 days'));
     $Last90DaysAgentLedger = $conn->query("SELECT * FROM agent_ledger where purchase !='0' AND createdAt>= '$Last90date' ORDER BY id DESC")->num_rows; 

     //last 365 STATS
     $Last365date = date('Y-m-d', strtotime('-365 days'));
     $Last365DaysAgentLedger = $conn->query("SELECT * FROM agent_ledger where purchase !='0' AND createdAt>= '$Last365date' ORDER BY id DESC")->num_rows;
    
     // $Last365days['allsearch'] = $Last365DaysDeposit;
   
     $response['total'] = $Total;    
     $response['todaySell'] = $TodaysAgentLedger;
     $response['yesterdaySell'] = $YesterAgentLedger;
     $response['last7daySell'] = $Last7DaysAgentLedger;
     $response['last15daySell'] = $Last15DaysAgentLedger;
     $response['last30daySell'] = $Last30DaysAgentLedger;
     $response['last90daySell'] = $Last90DaysAgentLedger;
     $response['last365daySell'] = $Last365DaysAgentLedger;
    

    echo json_encode($response);
}