<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){  

    if (array_key_exists("allBooking", $_GET)) {

        //TOTAL STATS
        $TotalBooking = $conn->query("SELECT * FROM booking ORDER BY id DESC")->num_rows;

        $Total['allBooking'] = $TotalBooking;
        
    
        //TODAY STATS
        $TodayBooking = $conn->query("SELECT * FROM booking where bookedAt>= CURRENT_DATE ORDER BY id DESC")->num_rows;

        // $TodayDeposit['allsearch'] = $TodayDeposit;

        //yesterday  STATS
        $Yesterdate = date('Y-m-d', strtotime('-1 days'));
        $YesterDaysBooking = $conn->query("SELECT * FROM booking where bookedAt>= '$Yesterdate' ORDER BY id DESC")->num_rows;
        
        // $Yesterdays['allsearch'] = $YesterDaysBooking;
    

        //last 7 STATS
        $Last7date = date('Y-m-d', strtotime('-7 days'));
        $Last7DaysBooking = $conn->query("SELECT * FROM booking where bookedAt>= '$Last7date' ORDER BY id DESC")->num_rows;
        
        // $Last7days['allsearch'] = $Last7DaysDeposit;

        //last 15 STATS
        $Last15date = date('Y-m-d', strtotime('-15 days'));
        $Last15DaysBooking = $conn->query("SELECT * FROM booking where bookedAt>= '$Last15date' ORDER BY id DESC")->num_rows;
        
        // $Last15days['allsearch'] = $Last15DaysDeposit;

        //last 30 STATS
        $Last30date = date('Y-m-d', strtotime('-30 days'));
        $Last30DaysBooking = $conn->query("SELECT * FROM booking where bookedAt>= '$Last30date' ORDER BY id DESC")->num_rows;
        
        // $Last30days['allsearch'] = $Last30DaysDeposit;

        //last 90 STATS
        $Last90date = date('Y-m-d', strtotime('-90 days'));
        $Last90DaysBooking = $conn->query("SELECT * FROM booking where bookedAt>= '$Last90date' ORDER BY id DESC")->num_rows;

        // $Last90days['allsearch'] = $Last90DaysDeposit;

        //last 365 STATS
        $Last365date = date('Y-m-d', strtotime('-365 days'));
        $Last365DaysBooking = $conn->query("SELECT * FROM booking where bookedAt>= '$Last365date' ORDER BY id DESC")->num_rows;
        
        // $Last365days['allsearch'] = $Last365DaysDeposit;
    
    
        

        $response['total'] = $Total;    
        $response['todays'] = $TodayBooking;
        $response['yesterdays'] = $YesterDaysBooking;
        $response['last7days'] = $Last7DaysBooking;
        $response['last15days'] = $Last15DaysBooking;
        $response['last30days'] = $Last30DaysBooking;
        $response['last90days'] = $Last90DaysBooking;
        $response['last365days'] = $Last365DaysBooking;
        

        echo json_encode($response);
    }

}else{
  authorization($conn);
}