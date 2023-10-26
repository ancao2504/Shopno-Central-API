<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){  

  if (array_key_exists("page", $_GET) && array_key_exists("all", $_GET)) {
    $page = $_GET['page'];
    $result_per_page = 20;
    $page_first_result = ($page-1) * $result_per_page;

    $staffId="";
    $sql = "SELECT * FROM `booking` ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
    $result = $conn->query($sql);
    $totaldata =mysqli_query($conn, "SELECT * FROM booking ")->num_rows;
    $totalHold =mysqli_query($conn, "SELECT status FROM booking WHERE status='Hold' ")->num_rows;
    $totalIssueInProcessing =mysqli_query($conn, "SELECT status FROM booking WHERE status='Issue In Processing' ")->num_rows;
    $totalTicketed =mysqli_query($conn, "SELECT status FROM booking WHERE status='Ticketed' ")->num_rows;
    $totalIssueRejected =mysqli_query($conn, "SELECT status FROM booking WHERE status='Issue Rejected' ")->num_rows;
    $totalReissueRequest =mysqli_query($conn, "SELECT status FROM booking WHERE status='Reissue In Processing' ")->num_rows;
    $totalReissued =mysqli_query($conn, "SELECT status FROM booking WHERE status='Reissued' ")->num_rows;
    $totalReissueRejected =mysqli_query($conn, "SELECT status FROM booking WHERE status='Reissue Rejected' ")->num_rows;
    $totalVoidRejected =mysqli_query($conn, "SELECT status FROM booking WHERE status='Void Rejected' ")->num_rows;
    $totalVoided =mysqli_query($conn, "SELECT status FROM booking WHERE status='Voided' ")->num_rows;
    $totalVoidedRequest =mysqli_query($conn, "SELECT status FROM booking WHERE status='Void In Processing' ")->num_rows;
    $totalReturn =mysqli_query($conn, "SELECT status FROM booking WHERE status='Return' ")->num_rows;
    $totalRefundRequest =mysqli_query($conn, "SELECT status FROM booking WHERE status='Refund In Processing'")->num_rows;
    $totalRefund =mysqli_query($conn, "SELECT status FROM booking WHERE status='Refunded'")->num_rows;
    $totalRefundReject =mysqli_query($conn, "SELECT status FROM booking WHERE status='Refund Rejected'")->num_rows;
    $totalBookingFailed =mysqli_query($conn, "SELECT * FROM failed_booking")->num_rows;
    $totalCancel =mysqli_query($conn, "SELECT status FROM booking WHERE status='Cancelled'")->num_rows;

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
        

        $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $data = mysqli_fetch_assoc($query);
        if(!empty($data)){
          $companyname = $data['company'];
          $companyphone = $data['phone'];
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

        $activityLog =  $conn->query("SELECT actionAt, actionBy FROM `activityLog` where ref='$bookingId' AND status='$status'")->fetch_all(MYSQLI_ASSOC);
        
        $response = $row;
        $response['companyname'] ="$companyname";
        $response['companyphone'] ="$companyphone";
        $response['lastBalance'] = $lastAmount ;
        $response['bookedby'] ="$staffName";
        $response['activity']= $activityLog;
        $response['serial']="$count";
      
        array_push($Data, $response);
      }
    }

    $return_arr['total'] = $totaldata;
    $return_arr['data_per_page'] = $result_per_page;
    $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
    $return_arr['totalHold'] = $totalHold;
    $return_arr['totalIssueInProcessing'] = $totalIssueInProcessing;
    $return_arr['totalTicketed'] = $totalTicketed;
    $return_arr['totalIssueRejected'] = $totalIssueRejected;
    $return_arr['totalReissueRequest'] = $totalReissueRequest;
    $return_arr['totalReissued'] = $totalReissued;
    $return_arr['totalReissueRejected'] = $totalReissueRejected;
    $return_arr['totalVoidRejected'] = $totalVoidRejected;
    $return_arr['totalVoided'] = $totalVoided;
    $return_arr['totalVoidedRequest'] = $totalVoidedRequest;
    $return_arr['totalReturn'] = $totalReturn;
    $return_arr['totalRefundRequest'] = $totalRefundRequest;
    $return_arr['totalRefund'] = $totalRefund;
    $return_arr['totalRefundReject'] = $totalRefundReject;
    $return_arr['totalBookingFailed'] = $totalBookingFailed;
    $return_arr['totalCancel'] = $totalCancel;
  
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
  }else if(array_key_exists("status",$_GET) && array_key_exists("page", $_GET)){
    
      $page = $_GET['page'];
      $result_per_page = 20;
      $page_first_result = ($page-1) * $result_per_page;
    
      $status = $_GET['status'];
      $sql = "SELECT * FROM `booking` where status = '$status' ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
      $totaldata = $conn->query("SELECT * FROM `booking` where status = '$status'");
      $result = $conn->query($sql);

      $return_arr = array();
      $Data = array();
      $count = 0;
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
          $count++;
          $staffId = $row['staffId'];
          $agentId = $row['agentId'];

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
        
          array_push($Data, $response);
        }
      }

      $return_arr['total'] = $totaldata;
      $return_arr['data_per_page'] = $result_per_page;
      $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
      $return_arr['data'] = $Data;

      echo json_encode($return_arr);
  }else if(array_key_exists("agentId",$_GET) && array_key_exists("agentId",$_GET)){
    
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
        }else if($tripType == 'return'){
            $flightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
        }

        $activityLog =  $conn->query("SELECT * FROM  `activityLog` where ref='$bookingId'")->fetch_all(MYSQLI_ASSOC);
        $TicketInfo =  $conn->query("SELECT DISTINCT  * FROM ticketed WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

          
          $response = $row;
          $response['passenger']= $PassengerData;
          $response['activity']= $activityLog;
          $response['flightData'] = $flightData;
          $response['ticketData'] = $TicketInfo;
                
          array_push($return_arr, $response);
        }
      }

      echo json_encode($return_arr);
  }

}else{
  authorization($conn);
}
  
  