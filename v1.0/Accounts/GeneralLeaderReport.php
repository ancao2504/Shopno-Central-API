<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("agentId", $_GET) && array_key_exists("all", $_GET)){
    $agentId = $_GET['agentId'];
    
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' ORDER BY id DESC LIMIT 100";
    $result = $conn->query($sql);

    $return_arr = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}else if (array_key_exists("agentId", $_GET) && array_key_exists("startDate", $_GET) && array_key_exists("endDate", $_GET)){
    $agentId = $_GET['agentId'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' AND createdAt BETWEEN '$startDate' AND '$endDate' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}else if (array_key_exists("agentId", $_GET) && array_key_exists("today", $_GET)){
    $agentId = $_GET['agentId'];
    $yestarday = date("Y-m-d", strtotime("yesterday")); 
      
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' AND createdAt >= CURRENT_DATE ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}else if (array_key_exists("agentId", $_GET) && array_key_exists("yestarday", $_GET)){
    $agentId = $_GET['agentId'];
    $yestarday = date("Y-m-d", strtotime("yesterday"));
      
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' AND createdAt LIKE '$yestarday%' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}else if (array_key_exists("agentId", $_GET) && array_key_exists("last7days", $_GET)){
    $agentId = $_GET['agentId'];
    $last7days = date('Y-m-d', strtotime('today - 7 days'));
      
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' AND createdAt LIKE '$last7days' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}else if (array_key_exists("agentId", $_GET) && array_key_exists("last15days", $_GET)){
    $agentId = $_GET['agentId'];
    $last15days = date('Y-m-d', strtotime('today - 15 days'));
      
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' AND createdAt >= '$last15days' ORDER BY id DESC";
    $result = $conn->query($sql);
    $count = 0;
    $return_arr = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}else if (array_key_exists("agentId", $_GET) && array_key_exists("last30days", $_GET)){
    $agentId = $_GET['agentId'];
    $last30days = date('Y-m-d', strtotime('today - 30 days'));
      
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' AND createdAt >= '$last30days' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    $count = 0;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}else if (array_key_exists("agentId", $_GET) && array_key_exists("last90days", $_GET)){
    $agentId = $_GET['agentId'];
    $last90days = date('Y-m-d', strtotime('today - 90 days'));
      
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' AND createdAt >= '$last90days' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();

    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}else if (array_key_exists("agentId", $_GET) && array_key_exists("last365days", $_GET)){
    $agentId = $_GET['agentId'];
    $lastyear = date("Y",strtotime("-1 year"));
      
    $sql = "SELECT * FROM `agent_ledger` WHERE agentId='$agentId' AND createdAt LIKE '$lastyear%' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $response = $row;
            $response['serial']= $count;
            array_push($return_arr, $response);
        }

        echo json_encode($return_arr);
        
    }else{
        echo json_encode("No Record Found");
    }

}


?>