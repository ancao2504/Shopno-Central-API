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

  $staffId="";
  $sql = "SELECT * FROM `booking` ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
  $result = $conn->query($sql);
  $totaldata = $conn->query("SELECT * FROM `booking`")->num_rows;

  $return_arr = array();
  $Data = array();
  $count = 0;
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $count++;
      $staffId = $row['staffId'];
      $agentId = $row['agentId'];
      $bookingId = $row['bookingId'];
      $status = $row['status'];
      $pnr  = $row['pnr'];
      $tripType = $row['tripType'];
      
      

      $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $data = mysqli_fetch_assoc($query);
      if(!empty($data)){
        $companyname = $data['company'];
        $companyphone = $data['phone'];
      }
if ($tripType == 'oneway') {
  $flightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
  if(!empty($flightData)){
    $FlightDate = $flightData[0]['departureTime1'];
    
  }
 }else if($tripType == 'return'){
  $flightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
  if(!empty($flightData)){
    $FlightDate = $flightData[0]['goDepartureTime1'];
    
  }
}
      
      $checkBalanced = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
            ORDER BY id DESC LIMIT 1");
      $row1 = mysqli_fetch_array($checkBalanced,MYSQLI_ASSOC);        
      if(!empty($row1)){
          $lastAmount = $row1['lastAmount'];							
      }else{
        $lastAmount = 0;
      }
      
      $staffsql = mysqli_query($conn,"SELECT * FROM staffList WHERE staffId='$staffId' AND agentId ='$agentId'");
			$staffRow = mysqli_fetch_array($staffsql,MYSQLI_ASSOC);

			if(!empty($staffRow)){				
				$staffName = $staffRow['name'];		
			}else{
          $staffName = "Agent";
      }

      $activitylog =  $conn->query("SELECT actionAt, actionBy FROM  `activitylog` where ref='$bookingId' AND status='$status'")->fetch_all(MYSQLI_ASSOC);
      
      $response = $row;
      $response['companyname'] ="$companyname";
      $response['companyphone'] ="$companyphone";
      $response['lastBalance'] = $lastAmount ;
      $response['bookedby'] ="$staffName";
      $response['activity']= $activitylog;
      $response['serial']="$count";
      $response['flightDate'] = $FlightDate;
     
      array_push($Data, $response);
    }
  }

  $return_arr['total'] = $totaldata;
  $return_arr['data_per_page'] = $result_per_page;
  $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
  $return_arr['data'] = $Data;
 
 
  echo json_encode($return_arr);
}else if (array_key_exists("allother", $_GET)) {

  $staffId="";
  $sql = "SELECT * FROM `bookingothers` ORDER BY id DESC";
  $result = $conn->query($sql);

  $return_arr = array();

  $count = 0;
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $count++;

      
      $response = $row;
      $response['serial']="$count";
     
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}else if(array_key_exists("search",$_GET)){
   
    $search = $_GET["search"];
    
    $sql = "SELECT * FROM `booking` where bookingId = '$search' OR pnr='search' OR agentId = '$search'";
    $result = $conn->query($sql);
    $return_arr = array();
    $count = 0;
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        $count++;
        $staffId = $row['staffId'];
        $agentId = $row['agentId'];
        $pnr  = $row['pnr'];
        $tripType = $row['tripType'];

        if ($tripType == 'oneway') {
          $flightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
          if(!empty($flightData)){
            $FlightDate = $flightData[0]['departureTime1'];
            
          }
         }else if($tripType == 'return'){
          $flightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
          if(!empty($flightData)){
            $FlightDate = $flightData[0]['goDepartureTime1'];
            
          }
        }


      $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $data = mysqli_fetch_assoc($query);
      $companyname = $data['company'];
      $companyphone = $data['phone'];
        
        $staffsql = mysqli_query($conn,"SELECT * FROM staffList WHERE staffId='$staffId' AND agentId ='$agentId'");
        $staffRow = mysqli_fetch_array($staffsql,MYSQLI_ASSOC);

        if(!empty($staffRow)){				
          $staffName = $staffRow['name'];		
        }else{
          $staffName = "Agent";
        }

        $checkBalanced = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
            ORDER BY id DESC LIMIT 1");
        $row1 = mysqli_fetch_array($checkBalanced,MYSQLI_ASSOC);        
        if(!empty($row1)){
            $lastAmount = $row1['lastAmount'];							
        }else{
          $lastAmount = 0;
        }
        
        $response = $row;
        $response['companyname'] ="$companyname";
        $response['companyphone'] ="$companyphone";
        $response['lastBalance'] = $lastAmount ;
        $response['bookedby'] ="$staffName";
         $response['serial']="$count";
         $response['flightDate'] = $FlightDate;
      
        array_push($return_arr, $response);
      }
    }


    echo json_encode($return_arr);
}else if(array_key_exists("status",$_GET) && array_key_exists("pages", $_GET)){
  
    $page = $_GET['pages'];
    $result_per_page = 20;
    $page_first_result = ($page-1) * $result_per_page;
  
    $status = $_GET['status'];
    $sql = "SELECT * FROM `booking` where status = '$status' ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
    $totaldata = $conn->query("SELECT * FROM `booking` where status = '$status'")->num_rows;
    $result = $conn->query($sql);

    $return_arr = array();
    $Data = array();
    $count = 0;
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        $count++;
        $staffId = $row['staffId'];
        $agentId = $row['agentId'];
        $pnr  = $row['pnr'];
        $tripType = $row['tripType'];

        if ($tripType == 'oneway') {
          $flightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
          if(!empty($flightData)){
            $FlightDate = $flightData[0]['departureTime1'];
            
          }
         }else if($tripType == 'return'){
          $flightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
          if(!empty($flightData)){
            $FlightDate = $flightData[0]['goDepartureTime1'];
            
          }
        }


      $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $data = mysqli_fetch_assoc($query);
      $companyname = $data['company'];
      $companyphone = $data['phone'];
        
        $staffsql = mysqli_query($conn,"SELECT * FROM staffList WHERE staffId='$staffId' AND agentId ='$agentId'");
        $staffRow = mysqli_fetch_array($staffsql,MYSQLI_ASSOC);

        if(!empty($staffRow)){				
          $staffName = $staffRow['name'];		
        }else{
          $staffName = "Agent";
        }

        $checkBalanced = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
            ORDER BY id DESC LIMIT 1");
        $row1 = mysqli_fetch_array($checkBalanced,MYSQLI_ASSOC);        
        if(!empty($row1)){
            $lastAmount = $row1['lastAmount'];							
        }else{
          $lastAmount = 0;
        }
        
        $response = $row;
        $response['companyname'] ="$companyname";
        $response['companyphone'] ="$companyphone";
        $response['lastBalance'] = $lastAmount ;
        $response['bookedby'] ="$staffName";
         $response['serial']="$count";
         $response['flightDate'] = $FlightDate;
      
        array_push($Data, $response);
      }
    }

    $return_arr['total'] = $totaldata;
    $return_arr['data_per_page'] = $result_per_page;
    $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
    $return_arr['data'] = $Data;

    echo json_encode($return_arr);
}else if(array_key_exists("agentId",$_GET) && array_key_exists("bookingId",$_GET)){
  
    $agentId =  $_GET["agentId"];
    $bookingId = $_GET["bookingId"];
    $sql = "SELECT * FROM `booking` where agentId='$agentId' AND bookingId = '$bookingId' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        $staffId = $row['staffId'];
        $agentId = $row['agentId'];
        $pnr = $row['pnr'];
        $tripType = $row['tripType'];
      
        $staffsql = mysqli_query($conn,"SELECT * FROM staffList WHERE agentId='$agentId' AND  staffId='$staffId' ");
        $staffRow = mysqli_fetch_array($staffsql,MYSQLI_ASSOC);

        if(!empty($staffRow)){				
          $staffName = $staffRow['name'];		
        }else{
          $staffName = "Agent";
        }

      $bookingId = $row['bookingId'];
      
      $PassengerData = $conn->query("SELECT * FROM  `passengers` where bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);
      

      //flight Data

      if($tripType == 'oneway'){
          $flightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
          $FlightDate = $flightData[0]['departureTime1'];
      }else if($tripType == 'return'){
          $flightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
          $FlightDate = $flightData[0]['goDepartureTime1'];
      }

      $activitylog =  $conn->query("SELECT * FROM  `activitylog` where ref='$bookingId'")->fetch_all(MYSQLI_ASSOC);
      $TicketInfo =  $conn->query("SELECT DISTINCT  * FROM ticketed WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

        
        $response = $row;
        $response['passenger']= $PassengerData;
        $response['activity']= $activitylog;
        $response['flightDate'] = $FlightDate;
        $response['ticketData'] = $TicketInfo;
              
        array_push($return_arr, $response);
      }
    }

    echo json_encode($return_arr);
}

    
  