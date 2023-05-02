<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists('all', $_GET)) {

  $sql = "SELECT * FROM `deposit_request` ORDER BY id DESC";
  $result = $conn->query($sql);
  $totaldata = $conn->query("SELECT * FROM `deposit_request`")->num_rows;
  $return_arr = array();
  $Data = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];
      $staffId = $row['staffId'];
      
      $agentSql = mysqli_query($conn,"SELECT company FROM agent WHERE agentId='$agentId' ");
			$agentRow = mysqli_fetch_array($agentSql,MYSQLI_ASSOC);
      $companyName = $agentRow['company'];

      $staffsql = mysqli_query($conn,"SELECT * FROM staffList WHERE staffId='$staffId' ");
			$staffRow = mysqli_fetch_array($staffsql,MYSQLI_ASSOC);

			if(!empty($staffRow)){				
				$staffName = $staffRow['name'];		
			}else{
          $staffName = "Agent";
      }
      
      $response = $row;
      $response['bookedby'] ="$staffName";
      $response['company'] ="$companyName";
      array_push($Data, $response);
    }
  }

  $return_arr['total'] = $totaldata;
  $return_arr['data'] = $Data;

  echo json_encode($return_arr);
}

if (array_key_exists('all', $_GET) && array_key_exists("page", $_GET)) {

  $page = $_GET['page'];
  $result_per_page = 20;
  $page_first_result = ($page-1) * $result_per_page;
  
  $sql = "SELECT * FROM `deposit_request` ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
  $result = $conn->query($sql);
  
  $totaldata = $conn->query("SELECT count(*) FROM `deposit_request`")->num_rows;
  $return_arr = array();
  $Data = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];
      $staffId = $row['staffId'];

      // $agentSql = mysqli_query($conn,"SELECT company FROM agent WHERE agentId='$agentId' ");
			// $agentRow = mysqli_fetch_array($agentSql,MYSQLI_ASSOC);
      // $companyName = $agentRow['company'];
      
      $staffsql = mysqli_query($conn,"SELECT * FROM staffList WHERE staffId='$staffId' ");
			$staffRow = mysqli_fetch_array($staffsql,MYSQLI_ASSOC);

			if(!empty($staffRow)){				
				$staffName = $staffRow['name'];		
			}else{
          $staffName = "Agent";
      }
      
      $response = $row;
      $response['bookedby'] ="$staffName";
      //$response['company'] ="$companyName";
      array_push($Data, $response);
    }
  }

  $return_arr['total'] = $totaldata;
  $return_arr['data_per_page'] = $result_per_page;
  $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
  $return_arr['data'] = $Data;

  echo json_encode($return_arr);
}else if (array_key_exists("agentId", $_GET) && array_key_exists("page", $_GET)) {

  $page = $_GET['page'];
  $agentId = $_GET['agentId'];
  $result_per_page = 20;
  $page_first_result = ($page-1) * $result_per_page;

  
  $sql = "SELECT * FROM `deposit_request` WHERE agentId='$agentId' ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
  $result = $conn->query($sql);
  $totaldata = $conn->query("SELECT count(*) FROM `deposit_request` WHERE agentId='$agentId'")->num_rows;
  $return_arr = array();
  $Data = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];
      $staffId = $row['staffId'];

      $agentSql = mysqli_query($conn,"SELECT company FROM agent WHERE agentId='$agentId' ");
			$agentRow = mysqli_fetch_array($agentSql,MYSQLI_ASSOC);
      $companyName = $agentRow['company'];
      
      $staffsql = mysqli_query($conn,"SELECT * FROM staffList WHERE staffId='$staffId' ");
			$staffRow = mysqli_fetch_array($staffsql,MYSQLI_ASSOC);

			if(!empty($staffRow)){				
				$staffName = $staffRow['name'];		
			}else{
          $staffName = "Agent";
      }
      
      $response = $row;
      $response['bookedby'] ="$staffName";
      $response['company'] ="$companyName";
      array_push($Data, $response);
    }
  }

  $return_arr['total'] = $totaldata;
  $return_arr['data_per_page'] = $result_per_page;
  $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
  $return_arr['data'] = $Data;

  echo json_encode($return_arr);
}
if(array_key_exists("agentId", $_GET) && array_key_exists("pages", $_GET) && array_key_exists("type", $_GET)){

  $type = $_GET['type'];
  $page = $_GET['pages'];
  $agentId = $_GET['agentId'];

  $result_per_page = 20;
  $page_first_result = ($page-1) * $result_per_page;

  
  $sql = "SELECT * FROM `deposit_request` WHERE `agentId`='$agentId' AND `paymentway`='$type' ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
  $result = $conn->query($sql);
  $totaldata = $conn->query("SELECT count(*) FROM `deposit_request` WHERE `agentId`='$agentId' AND `paymentway`='$type'")->num_rows;
  $return_arr = array();
  $Data = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];
      $staffId = $row['staffId'];

      $agentSql = mysqli_query($conn,"SELECT company FROM agent WHERE agentId='$agentId' ");
			$agentRow = mysqli_fetch_array($agentSql,MYSQLI_ASSOC);
      $companyName = $agentRow['company'];

      $staffsql = mysqli_query($conn,"SELECT * FROM staffList WHERE staffId='$staffId' ");
			$staffRow = mysqli_fetch_array($staffsql,MYSQLI_ASSOC);

			if(!empty($staffRow)){				
				$staffName = $staffRow['name'];		
			}else{
          $staffName = "Agent";
      }
      
      $response = $row;
      $response['bookedby'] ="$staffName";
      $response['company'] ="$companyName";
      array_push($Data, $response);
    }
  }

  $return_arr['total'] = $totaldata;
  $return_arr['data_per_page'] = $result_per_page;
  $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
  $return_arr['data'] = $Data;

  echo json_encode($return_arr);

}