<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("passenger", $_GET)) {

    //TOTAL STATS
    $TotalPassenger = $conn->query("SELECT * FROM passengers ORDER BY id DESC")->num_rows;
    $TotalMale = $conn->query("SELECT * FROM passengers WHERE gender='Male' ORDER BY id  DESC ")->num_rows;
    $TotalFemale = $conn->query("SELECT * FROM passengers WHERE gender='Female' ORDER BY id  DESC ")->num_rows;

     $Total['TotalPassenger'] = $TotalPassenger; 
     $Total['TotalMalePassenger'] = $TotalMale; 
     $Total['TotalFemalePassenger'] = $TotalFemale; 

    //TODAY STATS
    $TodaysPassengers = $conn->query("SELECT * FROM passengers where created>= CURRENT_DATE ORDER BY id DESC")->num_rows;

    //yesterday  STATS
    $Yesterdate = date('Y-m-d', strtotime('-1 days'));
    $YesterDaysPassengers = $conn->query("SELECT * FROM passengers where created>= '$Yesterdate' ORDER BY id DESC")->num_rows;
   

    //last 7 STATS
    $Last7date = date('Y-m-d', strtotime('-7 days'));
    $Last7DaysPassengers = $conn->query("SELECT * FROM passengers where  created>= '$Last7date' ORDER BY id DESC")->num_rows;
    
    // $Last7days['allsearch'] = $Last7DaysDeposit;

    //last 15 STATS
    $Last15date = date('Y-m-d', strtotime('-15 days'));
    $Last15DaysPassengers = $conn->query("SELECT * FROM passengers where created>= '$Last15date' ORDER BY id DESC")->num_rows;
    
    // $Last15days['allsearch'] = $Last15DaysDeposit;

    //last 30 STATS
    $Last30date = date('Y-m-d', strtotime('-30 days'));
    $Last30DaysPassengers = $conn->query("SELECT * FROM passengers where created>= '$Last30date' ORDER BY id DESC")->num_rows;
    
    //  // $Last30days['allsearch'] = $Last30DaysDeposit;

    //last 90 STATS
    $Last90date = date('Y-m-d', strtotime('-90 days'));
    $Last90DaysPassengers = $conn->query("SELECT * FROM passengers where created>= '$Last90date' ORDER BY id DESC")->num_rows;

    // $Last90days['allsearch'] = $Last90DaysDeposit;

    //last 365 STATS
    $Last365date = date('Y-m-d', strtotime('-365 days'));
    $Last365DaysPassengers = $conn->query("SELECT * FROM passengers where created>= '$Last365date' ORDER BY id DESC")->num_rows;
    
    // $Last365days['allsearch'] = $Last365DaysDeposit;
   
     $response['total'] = $Total;    
     $response['todaysPassenger'] = $TodaysPassengers;
     $response['yesterdayPassenger'] = $YesterDaysPassengers;
     $response['last7dayPassenger'] = $Last7DaysPassengers;
     $response['last15dayPassenger'] = $Last15DaysPassengers;
     $response['last30dayPassenger'] = $Last30DaysPassengers;
     $response['last90dayPassenger'] = $Last90DaysPassengers;
     $response['last365dayPassenger'] = $Last365DaysPassengers;
    

    echo json_encode($response);
}