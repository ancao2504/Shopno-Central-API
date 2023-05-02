<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {

  $visaId ='';
  $result = $conn->query("SELECT * FROM `visa_info` ORDER BY id DESC");
  $return_arr = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      
      $visaId = $row['visaId'];

      $checklist = $conn->query("SELECT * FROM  visa_check_list where visaId='$visaId'")->fetch_all(MYSQLI_ASSOC);
      $response['visainfo'] = $row;
      $response['checklist'] = $checklist;
      
      array_push($return_arr, $response);

    }
  }

  echo json_encode($return_arr);
} else if (array_key_exists("singleVisa", $_GET)) {

  $_POST = json_decode(file_get_contents('php://input'), true);
  
  $country = $_POST['country'];
  $category = $_POST['category'];

  $visaId ='';
  $result = $conn->query("SELECT * FROM `visa_info` where country='$country' AND visaCategory='$category'");
  $return_arr = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $visaId = $row['visaId'];
     array_push($return_arr, $row);
    }
  }

  $checklist = $conn->query("SELECT * FROM  visa_check_list where visaId='$visaId'")->fetch_all(MYSQLI_ASSOC);

  $response['visainfo'] = $return_arr;
  $response['checklist'] = $checklist;

  echo json_encode($response);

  
}else if (array_key_exists("allcountry", $_GET)) {

  $country = $_GET['allcountry'];

  $sql = "SELECT country FROM `visa_info` GROUP BY country";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $response = $row;
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}else if (array_key_exists("country", $_GET)) {

  $country = $_GET['country'];

  $sql = "SELECT visaCategory FROM `visa_info`where country='$country' GROUP BY visaCategory";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
       $response = $row;
       array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}else if (array_key_exists("id", $_GET)) {

  $Id = $_GET['id'];

  $sql = "SELECT * FROM `visa` where id='$Id'";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
       $response = $row;
       array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}