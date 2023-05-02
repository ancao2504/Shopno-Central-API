<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if (array_key_exists("agentId", $_GET) && array_key_exists("page", $_GET)) {

  $page = $_GET['page'];
  $result_per_page = 20;
  $page_first_result = ($page-1) * $result_per_page;
    
  $agentId = $_GET["agentId"];
  $sql = "SELECT * FROM `search_history` where agentId='$agentId' ORDER BY Id DESC LIMIT $page_first_result,$result_per_page";
  $totalData = $conn->query("SELECT * FROM `search_history` where agentId='$agentId'")->num_rows;
  $result = $conn->query($sql);

  $return_arr = array();
  $Data = array();
  $count = 0;
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $count++;
      $response = $row;
      $response['serial']="$count";
      array_push($Data, $response);
    }
  }

  $return_arr['total'] =  $totalData;
  $return_arr['data_per_page'] = $result_per_page;
  $return_arr['number_of_page'] = ceil(($totalData) / $result_per_page);
  $return_arr['data'] = $Data;

  echo json_encode($return_arr);
  
}