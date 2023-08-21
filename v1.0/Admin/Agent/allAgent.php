<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("page", $_GET)) {
  $page = $_GET['page'];
  $result_per_page = 20;
  $page_first_result = ($page-1) * $result_per_page;

  $sql = "SELECT * FROM `agent` WHERE platform='B2B' ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
  //echo $sql;
  $totaldata = $conn->query("SELECT * FROM `agent` WHERE platform='B2B' ORDER BY id DESC")->num_rows;
  $result = $conn->query($sql);
 
  $return_arr = array();
  $Data = array();
  $count=0;
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $count++;
      $agentId = $row['agentId'];
    
      $checkBalanced = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' AND platform='B2B'
            ORDER BY id DESC LIMIT 1");
        $row1 = mysqli_fetch_array($checkBalanced,MYSQLI_ASSOC);  
        
        if(!empty($row1)){
            $lastAmount = $row1['lastAmount'];							
        }else{
          $lastAmount = 0;
        }
    
      $response = $row;
      $response['serial'] = $count;
      $response['lastBalance'] = $lastAmount;
      array_push($Data, $response);
    }
  }

  array_multisort(array_column($Data, 'lastBalance'), SORT_DESC, $Data);
  
  $return_arr['total'] = $totaldata;
  $return_arr['data_per_page'] = $result_per_page;
  $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
  $return_arr['data'] = $Data;

  echo json_encode($return_arr);

}else if(array_key_exists("search",$_GET)){
  $search = $_GET['search'];
  
  $sql = "SELECT * FROM `agent` where (agentId='$search' OR email='$search' OR company LIKE '$search%' OR phone LIKE '$search%')AND platform='B2B'";
  $result = $conn->query($sql);

  $return_arr = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];  
      // $checkBalanced = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
      //       ORDER BY id DESC LIMIT 1");
      //   $row1 = mysqli_fetch_array($checkBalanced,MYSQLI_ASSOC); 
        
      //   if(!empty($row1)){
      //       $lastAmount = $row1['lastAmount'];							
      //   }else{
      //     $lastAmount = 0;
      //   }
    
      $response = $row;
      //$response['lastBalance'] = $lastAmount;
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
  
}else if(array_key_exists("status", $_GET) && array_key_exists("pages",$_GET)) {

    $page = $_GET['pages'];
    $result_per_page = 20;
    $page_first_result = ($page-1) * $result_per_page;

    $status= $_GET['status'];
   
    $sql = "SELECT * FROM `agent` where status='$status' ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
    $totaldata = $conn->query("SELECT * FROM `agent` where status='$status'  ORDER BY id DESC")->num_rows;
    $result = $conn->query($sql);

    $return_arr = array();
    $Data = array();
    if($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];          
      $agentsql = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId'  ORDER BY id DESC LIMIT 1");
			$agentRow = mysqli_fetch_array($agentsql,MYSQLI_ASSOC);
			if(!empty($agentRow)){				
				$Balanced = $agentRow['lastAmount'];
			}else{
        $Balanced=0;
      }
    
      $response = $row;
      $response['lastBalance'] ="$Balanced";
      array_push($Data, $response);
      }
    }
  
  $return_arr['total'] = $totaldata;
  $return_arr['data_per_page'] = $result_per_page;
  $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
  $return_arr['data'] = $Data;

  echo json_encode($return_arr);
  
}else if(array_key_exists("agentId", $_GET)) {

    $agentId= $_GET['agentId'];
   
    $sql = "SELECT * FROM `agent` where agentId='$agentId' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        
      $agentId = $row['agentId'];   
      $agentsql = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId'  ORDER BY id DESC LIMIT 1");
			$agentRow = mysqli_fetch_array($agentsql,MYSQLI_ASSOC);

			if(!empty($agentRow)){				
				$Balanced = $agentRow['lastAmount'];		
			}else{
        $Balanced=0;
      }
    
      $response = $row;
      $response['balanced'] = $Balanced;

      array_push($return_arr, $response);
      }
    }
  
  echo json_encode($return_arr);
  
}else if (array_key_exists("credit", $_GET)) {

  $sql = "SELECT * FROM `agent` ORDER BY id DESC";
  $result = $conn->query($sql);
 
  $return_arr = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];   
      $checkBalanced = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId'  ORDER BY id DESC LIMIT 1");
        $row1 = mysqli_fetch_array($checkBalanced,MYSQLI_ASSOC); 
        
        if(!empty($row1)){
            $lastAmount = $row1['lastAmount'];							
        }else{
          $lastAmount = 0;
        }
    
      $response = $row;
      $response['lastBalance'] = $lastAmount;
      if($lastAmount < 0){
        array_push($return_arr, $response);
      }
    }
  }

  echo json_encode($return_arr);

}