<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){  

if (array_key_exists("allDeposit", $_GET)) {

    //TOTAL STATS
    $TotalDeposit = $conn->query("SELECT * FROM deposit_request ORDER BY id DESC")->num_rows;
    


    $Total['allDeposit'] = $TotalDeposit;
    
   
    //TODAY STATS
    $TodayDeposit = $conn->query("SELECT * FROM deposit_request where createdAt>= CURRENT_DATE ORDER BY id DESC")->num_rows;

    // $TodayDeposit['allsearch'] = $TodayDeposit;

    //yesterday  STATS
    $Yesterdate = date('Y-m-d', strtotime('-1 days'));
    $YesterDaysDeposit = $conn->query("SELECT * FROM deposit_request where createdAt>= '$Yesterdate' ORDER BY id DESC")->num_rows;
    
    // $Yesterdays['allsearch'] = $YesterDaysDeposit;
   

    //last 7 STATS
    $Last7date = date('Y-m-d', strtotime('-7 days'));
    $Last7DaysDeposit = $conn->query("SELECT * FROM deposit_request where createdAt>= '$Last7date' ORDER BY id DESC")->num_rows;
    
    // $Last7days['allsearch'] = $Last7DaysDeposit;

    //last 15 STATS
    $Last15date = date('Y-m-d', strtotime('-15 days'));
    $Last15DaysDeposit = $conn->query("SELECT * FROM deposit_request where createdAt>= '$Last15date' ORDER BY id DESC")->num_rows;
    
    // $Last15days['allsearch'] = $Last15DaysDeposit;

    //last 30 STATS
    $Last30date = date('Y-m-d', strtotime('-30 days'));
    $Last30DaysDeposit = $conn->query("SELECT * FROM deposit_request where createdAt>= '$Last30date' ORDER BY id DESC")->num_rows;
    
    // $Last30days['allsearch'] = $Last30DaysDeposit;

    //last 90 STATS
    $Last90date = date('Y-m-d', strtotime('-90 days'));
    $Last90DaysDeposit = $conn->query("SELECT * FROM deposit_request where createdAt>= '$Last90date' ORDER BY id DESC")->num_rows;

    // $Last90days['allsearch'] = $Last90DaysDeposit;

    //last 365 STATS
    $Last365date = date('Y-m-d', strtotime('-365 days'));
    $Last365DaysDeposit = $conn->query("SELECT * FROM deposit_request where createdAt>= '$Last365date' ORDER BY id DESC")->num_rows;
    
    // $Last365days['allsearch'] = $Last365DaysDeposit;
   
   
    

    $response['total'] = $Total;    
    $response['todays'] = $TodayDeposit;
    $response['yesterdays'] = $YesterDaysDeposit;
    $response['last7days'] = $Last7DaysDeposit;
    $response['last15days'] = $Last15DaysDeposit;
    $response['last30days'] = $Last30DaysDeposit;
    $response['last90days'] = $Last90DaysDeposit;
    $response['last365days'] = $Last365DaysDeposit;
    

    echo json_encode($response);
}

}else{
  authorization($conn);
}