<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("agentId", $_GET) && array_key_exists("page", $_GET)) {
    $page = $_GET['page'];
    $agentId = $_GET['agentId'];
    $result_per_page = 20;
    $page_first_result = ($page - 1) * $result_per_page;  

    $agentId = $_GET["agentId"];
    $result = $conn->query("SELECT * FROM `agent_ledger` where agentId = '$agentId' ORDER BY id DESC LIMIT $page_first_result,$result_per_page");

    $return_arr = array();
    $count=0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $response = $row;
            $response['serial'] =$count;
            array_push($return_arr, $response);
        }
    }

  echo json_encode($return_arr);
}