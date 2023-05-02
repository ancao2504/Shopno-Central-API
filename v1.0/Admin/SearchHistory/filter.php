<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("stats", $_GET)) {

    //TOTAL STATS
    $TotalSearch = $conn->query("SELECT * FROM search_history ORDER BY id DESC")->num_rows;
    $TotalOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' ORDER BY id DESC")->num_rows;
    $TotalReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' ORDER BY id DESC")->num_rows;
    $TotalMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' ORDER BY id DESC")->num_rows;

    $TotalAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $TotalInactiveAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING Search < 1  ORDER BY agent.company ASC")->fetch_all(MYSQLI_ASSOC);
            
    $TotalStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $TotalDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0  ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);



    $Total['allsearch'] = $TotalSearch;
    $Total['oneway'] = $TotalOnewaySearch;
    $Total['return'] = $TotalReturnSearch;
    $Total['multicity'] = $TotalMulticitySearch;
    $Total['agentwise'] = $TotalAgentWiseSearch;
    $Total['staffwise'] = $TotalStaffWiseSearch;
    $Total['destinationwise'] = $TotalDestination;
    $Total['inactiveagentwise'] = $TotalInactiveAgentWiseSearch;


    //TODAY STATS
    $TodaySearch = $conn->query("SELECT * FROM search_history where searchTime>= CURRENT_DATE ORDER BY id DESC")->num_rows;
    $TodayOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' AND searchTime>= CURRENT_DATE ORDER BY id DESC")->num_rows;
    $TodayReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' AND searchTime>= CURRENT_DATE ORDER BY id DESC")->num_rows;
    $TodayMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' AND searchTime>= CURRENT_DATE ORDER BY id DESC")->num_rows;

    $TodayAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING searchTime>= CURRENT_DATE ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $TodayInactiveAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING Search < 1 AND searchTime>= CURRENT_DATE  ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);
            
    $TodayStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy HAVING searchTime>= CURRENT_DATE ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $TodayDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype,searchTime, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0 AND searchTime>= CURRENT_DATE ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);


    $Today['allsearch'] = $TodaySearch;
    $Today['oneway'] = $TodayOnewaySearch;
    $Today['return'] = $TodayReturnSearch;
    $Today['multicity'] = $TodayMulticitySearch;
    $Today['agentwise'] = $TodayAgentWiseSearch;
    $Today['staffwise'] = $TodayStaffWiseSearch;
    $Today['destinationwise'] = $TodayDestination;
    $Today['inactiveagentwise'] = $TodayInactiveAgentWiseSearch;

    

     //Yestarday STATS

    $Yestardate = date('Y-m-d', strtotime('-1 days'));
    $YestardaySearch = $conn->query("SELECT * FROM search_history where searchTime LIKE '$Yestardate%' ORDER BY id DESC")->num_rows;
    $YestardayOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' AND searchTime>= '$Yestardate' ORDER BY id DESC")->num_rows;
    $YestardayReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' AND searchTime>= '$Yestardate' ORDER BY id DESC")->num_rows;
    $YestardayMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' AND searchTime>= '$Yestardate' ORDER BY id DESC")->num_rows;

    $YestardayAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING searchTime>= '$Yestardate' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $YestardayInactiveAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING Search < 1 AND searchTime>= '$Yestardate'  ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);
            
    $YestardayStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy HAVING searchTime>= '$Yestardate' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $YestardayDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype,searchTime, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0 AND searchTime>= '$Yestardate' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);


    $Yestarday['allsearch'] = $YestardaySearch;
    $Yestarday['oneway'] = $YestardayOnewaySearch;
    $Yestarday['return'] = $YestardayReturnSearch;
    $Yestarday['multicity'] = $YestardayMulticitySearch;
    $Yestarday['agentwise'] = $YestardayAgentWiseSearch;
    $Yestarday['staffwise'] = $YestardayStaffWiseSearch;
    $Yestarday['destinationwise'] = $YestardayDestination;
    $Yestarday['inactiveagentwise'] = $YestardayInactiveAgentWiseSearch;

     //Last 7 DAys STATS

    $Last7date = date('Y-m-d', strtotime('-7 days'));
    $Last7DaysSearch = $conn->query("SELECT * FROM search_history where searchTime>= '$Last7date' ORDER BY id DESC")->num_rows;
    $Last7DaysOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' AND searchTime>= '$Last7date' ORDER BY id DESC")->num_rows;
    $Last7DaysReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' AND searchTime>= '$Last7date' ORDER BY id DESC")->num_rows;
    $Last7DaysMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' AND searchTime>= '$Last7date' ORDER BY id DESC")->num_rows;

    $Last7DaysAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING searchTime>= '$Last7date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last7DaysInactiveAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING Search < 1 AND searchTime>= '$Last7date'  ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);
            
    $Last7DaysStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy HAVING searchTime>= '$Last7date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last7DaysDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype,searchTime, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0 AND searchTime>= '$Last7date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);


    $Last7Days['allsearch'] = $Last7DaysSearch;
    $Last7Days['oneway'] = $Last7DaysOnewaySearch;
    $Last7Days['return'] = $Last7DaysReturnSearch;
    $Last7Days['multicity'] = $Last7DaysMulticitySearch;
    $Last7Days['agentwise'] = $Last7DaysAgentWiseSearch;
    $Last7Days['staffwise'] = $Last7DaysStaffWiseSearch;
    $Last7Days['destinationwise'] = $Last7DaysDestination;
    $Last7Days['inactiveagentwise'] = $Last7DaysInactiveAgentWiseSearch;

    //Last 15 DAys STATS

    $Last15date = date('Y-m-d', strtotime('-15 days'));
    $Last15DaysSearch = $conn->query("SELECT * FROM search_history where searchTime>= '$Last15date' ORDER BY id DESC")->num_rows;
    $Last15DaysOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' AND searchTime>= '$Last15date' ORDER BY id DESC")->num_rows;
    $Last15DaysReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' AND searchTime>= '$Last15date' ORDER BY id DESC")->num_rows;
    $Last15DaysMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' AND searchTime>= '$Last15date' ORDER BY id DESC")->num_rows;

    $Last15DaysAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING searchTime>= '$Last15date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last15DaysInactiveAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING Search < 1 AND searchTime>= '$Last15date'  ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);
            
    $Last15DaysStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy HAVING searchTime>= '$Last15date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last15DaysDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype,searchTime, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0 AND searchTime>= '$Last15date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);


    $Last15Days['allsearch'] = $Last15DaysSearch;
    $Last15Days['oneway'] = $Last15DaysOnewaySearch;
    $Last15Days['return'] = $Last15DaysReturnSearch;
    $Last15Days['multicity'] = $Last15DaysMulticitySearch;
    $Last15Days['agentwise'] = $Last15DaysAgentWiseSearch;
    $Last15Days['staffwise'] = $Last15DaysStaffWiseSearch;
    $Last15Days['destinationwise'] = $Last15DaysDestination;
    $Last15Days['inactiveagentwise'] = $Last15DaysInactiveAgentWiseSearch;

    //Last 30 DAys STATS

    $Last30date = date('Y-m-d', strtotime('-30 days'));
    $Last30DaysSearch = $conn->query("SELECT * FROM search_history where searchTime>= '$Last30date' ORDER BY id DESC")->num_rows;
    $Last30DaysOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' AND searchTime>= '$Last30date' ORDER BY id DESC")->num_rows;
    $Last30DaysReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' AND searchTime>= '$Last30date' ORDER BY id DESC")->num_rows;
    $Last30DaysMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' AND searchTime>= '$Last30date' ORDER BY id DESC")->num_rows;

    $Last30DaysAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING searchTime>= '$Last30date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last30DaysInactiveAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING Search < 1 AND searchTime>= '$Last30date'  ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);
            
    $Last30DaysStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy HAVING searchTime>= '$Last30date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last30DaysDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype,searchTime, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0 AND searchTime>= '$Last30date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);


    $Last30Days['allsearch'] = $Last30DaysSearch;
    $Last30Days['oneway'] = $Last30DaysOnewaySearch;
    $Last30Days['return'] = $Last30DaysReturnSearch;
    $Last30Days['multicity'] = $Last30DaysMulticitySearch;
    $Last30Days['agentwise'] = $Last30DaysAgentWiseSearch;
    $Last30Days['staffwise'] = $Last30DaysStaffWiseSearch;
    $Last30Days['destinationwise'] = $Last30DaysDestination;
    $Last30Days['inactiveagentwise'] = $Last30DaysInactiveAgentWiseSearch;

    //Last 90 DAys STATS

    $Last90date = date('Y-m-d', strtotime('-90 days'));
    $Last90DaysSearch = $conn->query("SELECT * FROM search_history where searchTime>= '$Last90date' ORDER BY id DESC")->num_rows;
    $Last90DaysOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' AND searchTime>= '$Last90date' ORDER BY id DESC")->num_rows;
    $Last90DaysReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' AND searchTime>= '$Last90date' ORDER BY id DESC")->num_rows;
    $Last90DaysMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' AND searchTime>= '$Last90date' ORDER BY id DESC")->num_rows;

    $Last90DaysAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING searchTime>= '$Last90date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last90DaysInactiveAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING Search < 1 AND searchTime>= '$Last90date'  ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);
            
    $Last90DaysStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy HAVING searchTime>= '$Last90date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last90DaysDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype,searchTime, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0 AND searchTime>= '$Last90date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);


    $Last90Days['allsearch'] = $Last90DaysSearch;
    $Last90Days['oneway'] = $Last90DaysOnewaySearch;
    $Last90Days['return'] = $Last90DaysReturnSearch;
    $Last90Days['multicity'] = $Last90DaysMulticitySearch;
    $Last90Days['agentwise'] = $Last90DaysAgentWiseSearch;
    $Last90Days['staffwise'] = $Last90DaysStaffWiseSearch;
    $Last90Days['destinationwise'] = $Last90DaysDestination;
    $Last90Days['inactiveagentwise'] = $Last90DaysInactiveAgentWiseSearch;

    //Last 365 DAys STATS

    $Last365date = date('Y-m-d', strtotime('-365 days'));
    $Last365DaysSearch = $conn->query("SELECT * FROM search_history where searchTime>= '$Last365date' ORDER BY id DESC")->num_rows;
    $Last365DaysOnewaySearch = $conn->query("SELECT * FROM search_history where searchtype ='oneway' AND searchTime>= '$Last365date' ORDER BY id DESC")->num_rows;
    $Last365DaysReturnSearch = $conn->query("SELECT * FROM search_history where searchtype ='return' AND searchTime>= '$Last365date' ORDER BY id DESC")->num_rows;
    $Last365DaysMulticitySearch = $conn->query("SELECT * FROM search_history where searchtype ='multicity' AND searchTime>= '$Last365date' ORDER BY id DESC")->num_rows;

    $Last365DaysAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING searchTime>= '$Last365date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last365DaysInactiveAgentWiseSearch = $conn->query("SELECT search_history.agentId, agent.company,searchTime, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY 
            search_history.agentId HAVING Search < 1 AND searchTime>= '$Last365date'  ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);
            
    $Last365DaysStaffWiseSearch = $conn->query("SELECT agent.company, search_history.searchBy,searchTime,search_history.searchtype, COUNT(*) as Search FROM
            `search_history` INNER JOIN agent ON agent.agentId = search_history.agentId GROUP BY
             search_history.searchBy HAVING searchTime>= '$Last365date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);

    $Last365DaysDestination = $conn->query("SELECT DepFrom, ArrTo,searchtype,searchTime, COUNT(*) as Search FROM search_history 
                    GROUP BY DepFrom, ArrTo HAVING COUNT(*) > 0 AND searchTime>= '$Last365date' ORDER BY Search DESC")->fetch_all(MYSQLI_ASSOC);


    $Last365Days['allsearch'] = $Last365DaysSearch;
    $Last365Days['oneway'] = $Last365DaysOnewaySearch;
    $Last365Days['return'] = $Last365DaysReturnSearch;
    $Last365Days['multicity'] = $Last365DaysMulticitySearch;
    $Last365Days['agentwise'] = $Last365DaysAgentWiseSearch;
    $Last365Days['staffwise'] = $Last365DaysStaffWiseSearch;
    $Last365Days['destinationwise'] = $Last365DaysDestination;
    $Last365Days['inactiveagentwise'] = $Last365DaysInactiveAgentWiseSearch;

    

    $response['total'] = $Total;
    $response['today'] = $Today;
    $response['yesterday'] = $Yestarday;
    $response['last7days'] = $Last7Days;
    $response['last15days'] = $Last15Days;
    $response['last30days'] = $Last30Days;
    $response['last90days'] = $Last90Days;
    $response['last365days'] = $Last365Days;



    



    echo json_encode($response);
}