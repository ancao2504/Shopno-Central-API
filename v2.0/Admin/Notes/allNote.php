<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../../authorization.php';
if (authorization($conn) == true){  

  if(array_key_exists("ref", $_GET) & array_key_exists("category", $_GET)) {

      $refId= $_GET['ref'];
      $category = $_GET['category'];
    
      $sql = "SELECT * FROM `notes` where reference='$refId' AND category='$category' ORDER BY ID DESC";
      $result = $conn->query($sql);
      $return_arr = array();

      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()){
          
          $response = $row;
          array_push($return_arr, $response);
          }
      }
    
    echo json_encode($return_arr);
  }
}else{
  authorization($conn);
} 