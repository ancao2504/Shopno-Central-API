<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("userId", $_GET)){

  $userId = $_GET["userId"];
  $sql = "SELECT * FROM `passengers` where userId='$userId' ORDER BY Id DESC";
  $result = $conn->query($sql);

  $return_arr = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      array_push($return_arr, $row);
    }
  }

  echo json_encode($return_arr);
  
}