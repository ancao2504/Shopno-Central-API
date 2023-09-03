<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {

  $sql = "SELECT * FROM `agent_ledger` WHERE platform='B2C' ORDER BY id DESC";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $response = $row;
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}else if (array_key_exists("userId", $_GET) && array_key_exists("search", $_GET)) {
    
  $userId = $_GET["userId"];

  $result = $conn->query("SELECT * FROM `agent_ledger` where userId = '$userId' ORDER BY id DESC");

  $return_arr = array();
  $count=0;
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $count++;
      $response = $row;
      $response['serial'] =$count;
      array_push($return_arr, $response);
    }
    echo json_encode($return_arr);
  }else{
    $response['error'] = "error";
    $response['message'] = "Data Not Found"; 
    echo json_encode($response);
  }

  
}else if (array_key_exists("userId", $_GET) && array_key_exists("balance", $_GET)) {
    
  $userId = $_GET["userId"];  
  $sql = "SELECT lastAmount FROM `agent_ledger` where userId = '$userId' ORDER BY id DESC LIMIT 1";  
  $result = $conn->query($sql);
  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
     
      $creaditsql = mysqli_query($conn,"SELECT * FROM agent WHERE userId='$userId' ");
			$creditRow = mysqli_fetch_array($creaditsql,MYSQLI_ASSOC);

			if(!empty($creditRow)){				
				$Credit = $creditRow['credit'];
        $Bonus = $creditRow['bonus'];		
			}else{
        $Credit = 0;
        $Bonus = 0;	
      }
      
      $response = $row;
      $response['credit'] ="$Credit";
      $response['bonus'] = "$Bonus";
      array_push($return_arr, $response);
    }
  }else{
    $creaditsql = mysqli_query($conn,"SELECT * FROM agent WHERE userId='$userId' ");
			$creditRow = mysqli_fetch_array($creaditsql,MYSQLI_ASSOC);

			if(!empty($creditRow)){				
				$Credit = $creditRow['credit'];
        $Bonus = $creditRow['bonus'];		
			}else{
        $Credit = 0;
        $Bonus = 0;	
      }
      $response['lastAmount'] = 0;
      $response['credit'] = $Credit;
      $response['bonus'] = $Bonus;
      array_push($return_arr, $response);
  }

  echo json_encode($return_arr);
}