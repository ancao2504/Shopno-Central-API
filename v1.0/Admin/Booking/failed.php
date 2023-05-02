<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {

  $sql = "SELECT * FROM `failed_booking` ORDER BY id DESC";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];
      $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $data = mysqli_fetch_array($query,MYSQLI_ASSOC);

      $companyphone = '';
      if (isset($data)) {
        $companyphone = $data['phone'];
      }
      $response = $row;
      $response['phone'] = "$companyphone";
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}else if(array_key_exists("agentId", $_GET)) {

    $agentId= $_GET['agentId'];
   
    $sql = "SELECT * FROM `failed_booking` where agentId='$agentId' ORDER BY id DESC";
    $result = $conn->query($sql);
    $return_arr = array();

    
    $response = $row;
    array_push($return_arr, $response);
  
  echo json_encode($return_arr);
} 