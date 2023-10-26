<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../../authorization.php';
if (authorization($conn) == true){  

  if(array_key_exists("add", $_GET)) {
      if($_SERVER["REQUEST_METHOD"] == "POST") {
          $_POST = json_decode(file_get_contents("php://input"), true);
          $title = $_POST["title"];
          $text = $_POST["text"];
          $createTime = date("Y-m-d H:i:s");

          $sql = "UPDATE notification SET title='$title', text = '$text', status='active', timedate='$createTime'";

          if($conn->query($sql) == true) {
              $response['status'] = "success";
              $response['message'] = "Notifications Updated Successfully";
          } else {
              $response['status'] = "error";
              $response['message'] = "Query Failed";
          }
          echo json_encode($response);
      }
  }else if (array_key_exists("status", $_GET)) {
          $status = $_GET['status'];
          $sql = "UPDATE notification SET status='$status'";
          if($conn->query($sql) == true){
              $response['status'] = "success";
              $response['message'] = "Notifications Status Updated Successfully";
          }else{
              $response['status'] = "error";
              $response['message'] = "Query Failed";
          }
          echo json_encode($response);
  }else if (array_key_exists("all", $_GET)) {

    $sql = "SELECT * FROM notification ORDER BY id DESC LIMIT 5";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        $response = $row;
        array_push($return_arr, $response);
      }
    }

    echo json_encode($return_arr);
  }else if (array_key_exists("agentId", $_GET)) {
    $Id = $_GET['agentId'];

    $sql = "SELECT * FROM notification where agentId='$Id' ORDER BY id DESC LIMIT 5";
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