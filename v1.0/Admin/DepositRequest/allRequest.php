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
  $first_page_result = ($page-1) * $result_per_page;
  $sql = "SELECT * FROM `deposit_request` ORDER BY id DESC LIMIT $first_page_result, $result_per_page";
  $totatdata = $conn->query("SELECT * FROM `deposit_request` ORDER BY id DESC")->num_rows;
  $result = $conn->query($sql);
  
  $return_arr = array();
  $Data = array();
  $count = 0;

  if ($result->num_rows > 0) {
   
    while ($row = $result->fetch_assoc()){
      $agentId = $row['agentId'];
      $staffId = $row['staffId'];
      
      $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $data = mysqli_fetch_assoc($query);

      if(!empty($data)){
        $companyname = $data['company'];
      }else{
        $companyname = '';
      }
      

      $query1 = mysqli_query($conn, "SELECT * FROM staffList WHERE staffId='$staffId'");
      $data1 = mysqli_fetch_assoc($query1);

      if(!empty($data1)){
        $staffName = $data1['name'];
      }else{
        $staffName = "Agent";
      }
    
      $response = $row;
      $response['company'] = $companyname;
      $response['RequestedBy'] = $staffName;
      array_push($Data, $response);
    }
  }

  $return_arr['total'] = $totatdata;
  $return_arr['data_per_page']  =  $result_per_page;
  $return_arr['number_of_page'] = ceil(($totatdata) / $result_per_page);
  $return_arr['data'] = $Data;

  echo json_encode($return_arr);
}else if(array_key_exists("status", $_GET) && array_key_exists("pages", $_GET)){

    $status= $_GET['status'];
    $page = $_GET['pages'];
    $result_per_page = 20;
    $first_page_result = ($page-1) * $result_per_page;

   
    $sql = "SELECT * FROM `deposit_request` where status='$status' ORDER BY id DESC LIMIT $first_page_result,$result_per_page";
    $result = $conn->query($sql);
    $totatdata = $conn->query( "SELECT * FROM `deposit_request` WHERE status= '$status' ORDER BY id DESC")->num_rows;

    $return_arr = array();
    $Data = array();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        $agentId = $row['agentId'];
        $staffId  = $row['staffId'];
        $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $data = mysqli_fetch_assoc($query);
        if(!empty($data)){
          $companyname = $data['company'];
        }else{
          $companyname = '';
        }

        $query1 = mysqli_query($conn, "SELECT * FROM staffList WHERE staffId='$staffId'");
        $data1 = mysqli_fetch_assoc($query1);

        if(!empty($data1)){
          $staffName = $data1['name'];
        }else{
          $staffName = "Agent";
        }
    
      
        $response = $row;
        $response['company'] = $companyname;
        $response['RequestedBy'] = $staffName;
        array_push($Data, $response);
        }
    }
    $return_arr['total'] = $totatdata;
    $return_arr['data_per_page'] = $result_per_page;
    $return_arr['number_of_page'] = ceil(($totatdata) / $result_per_page);
    $return_arr['data'] = $Data;

  echo json_encode($return_arr);
  
}else if(array_key_exists("method", $_GET) && array_key_exists("depositpage", $_GET)){

    $method= $_GET['method'];
    $page = $_GET['depositpage'];
    $result_per_page = 20;
    $first_page_result = ($page-1) * $result_per_page;

   
    $sql = "SELECT * FROM `deposit_request` where paymentway='$method' ORDER BY id DESC LIMIT $first_page_result,$result_per_page";
    $result = $conn->query($sql);
    $totatdata = $conn->query( "SELECT * FROM `deposit_request` WHERE paymentway= '$method' ORDER BY id DESC")->num_rows;

    $return_arr = array();
    $Data = array();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        $agentId = $row['agentId'];
        $staffId  = $row['staffId'];
        $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $data = mysqli_fetch_assoc($query);
        if(!empty($data)){
          $companyname = $data['company'];
        }else{
          $companyname = '';
        }

        $query1 = mysqli_query($conn, "SELECT * FROM staffList WHERE staffId='$staffId'");
        $data1 = mysqli_fetch_assoc($query1);

        if(!empty($data1)){
          $staffName = $data1['name'];
        }else{
          $staffName = "Agent";
        }
          
          $response = $row;
          $response['company'] = $companyname;
          $response['RequestedBy'] = $staffName;
          array_push($Data, $response);
        }
    }
    $return_arr['total'] = $totatdata;
    $return_arr['data_per_page'] = $result_per_page;
    $return_arr['number_of_page'] = ceil(($totatdata) / $result_per_page);
    $return_arr['data'] = $Data;

  echo json_encode($return_arr);
  
}