<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../../authorization.php';
if (authorization($conn) == true){  

    if (array_key_exists("all", $_GET)) {

        //TOTAL STATS
        $TotalAgent = $conn->query("SELECT * FROM agent ORDER BY id DESC")->num_rows;
        $TotalActiveAgent = $conn->query("SELECT * FROM agent where status ='active' ORDER BY id DESC")->num_rows;
        $TotalPendingAgent = $conn->query("SELECT * FROM agent where status ='pending' ORDER BY id DESC")->num_rows;
        $TotalDeactiveAgent = $conn->query("SELECT * FROM agent where status ='deactive' ORDER BY id DESC")->num_rows;


        $Total['allAgent'] = $TotalAgent;
        $Total['active'] = $TotalActiveAgent;
        $Total['pending'] = $TotalPendingAgent;
        $Total['deactive'] = $TotalDeactiveAgent;

    
        //TODAY STATS
        $TodayAgent = $conn->query("SELECT * FROM agent where joinAt>= CURRENT_DATE ORDER BY id DESC")->num_rows;
        // $TodayAgent = $conn->query("SELECT * FROM agent where joinAt Between CURDATE()-7 AND CURDATE() ORDER BY id DESC");

        //last 7 STATS
        $Last7date = date('Y-m-d', strtotime('-7 days'));
        $Last7DaysAgent = $conn->query("SELECT * FROM agent where joinAt>= '$Last7date' ORDER BY id DESC")->num_rows;
        
        // $Last7days['allsearch'] = $Last7DaysAgent;

        //last 15 STATS
        $Last15date = date('Y-m-d', strtotime('-15 days'));
        $Last15DaysAgent = $conn->query("SELECT * FROM agent where joinAt>= '$Last15date' ORDER BY id DESC")->num_rows;
        
        // $Last15days['allsearch'] = $Last15DaysAgent;

        //last 30 STATS
        $Last30date = date('Y-m-d', strtotime('-30 days'));
        $Last30DaysAgent = $conn->query("SELECT * FROM agent where joinAt>= '$Last30date' ORDER BY id DESC")->num_rows;
        
        // $Last30days['allsearch'] = $Last30DaysAgent;

        //last 90 STATS
        $Last90date = date('Y-m-d', strtotime('-90 days'));
        $Last90DaysAgent = $conn->query("SELECT * FROM agent where joinAt>= '$Last90date' ORDER BY id DESC")->num_rows;
        
        // $Last90days['allsearch'] = $Last90DaysAgent;

        //last 365 STATS
        $Last365date = date('Y-m-d', strtotime('-365 days'));
        $Last365DaysAgent = $conn->query("SELECT * FROM agent where joinAt>= '$Last365date' ORDER BY id DESC")->num_rows;
        
        // $Last365days['allsearch'] = $Last365DaysAgent;
    
    
        

        $response['total'] = $Total;
        $response['todays'] = $TodayAgent;
        $response['last7days'] = $Last7DaysAgent;
        $response['last15days'] = $Last15DaysAgent;
        $response['last30days'] = $Last30DaysAgent;
        $response['last90days'] = $Last90DaysAgent;
        $response['last365days'] = $Last365DaysAgent;
        

        echo json_encode($response);
    }

}else{
  authorization($conn);
}