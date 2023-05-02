<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {
    $sql = "SELECT * FROM `agent_ledger` ORDER BY id DESC";
    $result = $conn->query($sql);
    $return_arr = array();
    $Data = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;
            $agentId = $row['agentId'];
            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);

            if (isset($data['company'])) {
                $companyname = $data['company'];
            } else {
                $companyname = "No Data";
            }

            $response = $row;
            $response['companyname'] = "$companyname";
            $response['serial'] = $count;

            array_push($Data, $response);
        }
    }
    $return_arr['data'] = $Data;

    echo json_encode($return_arr);
}else if (array_key_exists("page", $_GET)) {
    $page = $_GET['page'];
    $result_pare_page = 20;
    $first_page_result = ($page-1) * $result_pare_page;
    $sql = "SELECT * FROM `agent_ledger` ORDER BY id DESC LIMIT $first_page_result, $result_pare_page";
    $totalData = $conn->query("SELECT * FROM `agent_ledger` ORDER BY id DESC")->num_rows;
    $result = $conn->query($sql);
    $return_arr = array();
    $Data = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;
            $agentId = $row['agentId'];
            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);

            if (isset($data['company'])) {
                $companyname = $data['company'];
            } else {
                $companyname = "No Data";
            }

            $response = $row;
            $response['companyname'] = "$companyname";
            $response['serial'] = $count;

            array_push($Data, $response);
        }
    }
    $return_arr['total'] = $totalData;
    $return_arr['data_pare_page'] = $result_pare_page;
    $return_arr['number_of_page'] = ceil(($totalData) / $result_pare_page);
    $return_arr['data'] = $Data;

    echo json_encode($return_arr);
} else if (array_key_exists("agentId", $_GET) && array_key_exists("pages",$_GET)) {
    $agentId = $_GET["agentId"];
    $page = $_GET['pages'];
    $result_pare_page = 20;
    $first_page_result = ($page-1) * $result_pare_page;
    $sql = "SELECT * FROM `agent_ledger` ORDER BY id DESC LIMIT $first_page_result, $result_pare_page";
   $result = $conn->query($sql);
   $totalData = $conn->query("SELECT * FROM `agent_ledger` where agentId = '$agentId' ORDER BY id DESC")->num_rows;

    $return_arr = array();
    $Data = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;
            $response = $row;
            $response['serial'] = $count;

            array_push($Data, $response);
        }
    }
      $return_arr['total'] = $totalData;
      $return_arr['data_per_page'] = $result_pare_page;
      $return_arr['number_of_page'] = ceil(($totalData)/$result_pare_page);
      $return_arr['data'] = $Data;
    echo json_encode($return_arr);
} else if (array_key_exists("search", $_GET)) {
  
    $Search = $_GET["search"];
    $sql = "SELECT * FROM `agent_ledger` where agentId='$Search' OR transactionId='$Search' OR reference='$Search' ORDER BY id DESC";
    
    $result = $conn->query($sql);
    $return_arr = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $count++;
            $agentId = $row['agentId'];
            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);
            $companyname = $data['company'];

            if (isset($data['company'])) {
                $companyname = $data['company'];
            } else {
                $companyname = "No Data";
            }

            $response['company'] = $companyname;
            $response = $row;
            $response['serial'] = $count;
            array_push($return_arr, $response);
        }
    }

    echo json_encode($return_arr);
}