<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {

  $sql = "SELECT * FROM `allpackages`";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $response = $row;
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
} else if (array_key_exists("id", $_GET)) {

  $pkId = $_GET['id'];

  $sql = "SELECT * FROM `allpackages` where pkId='$pkId'";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo json_encode($row);
    }
  }
}else if (array_key_exists("title", $_GET)) {

  $title = $_GET['title'];

  $sql = "SELECT * FROM `allpackages` where longTitleEN='$title'";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo json_encode($row);
    }
  }
}else if (array_key_exists("link", $_GET)) {

  $title = $_GET['link'];

  $sql = "SELECT * FROM `allpackages` where link='$title'";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo json_encode($row);
    }
  }
}else if (array_key_exists("page", $_GET)) {
  $page = $_GET['page'];
  $Start = $page * 10 - 10;
  $End = $page * 10;

  $sql = "SELECT * FROM `allpackages` LIMIT $Start,$End ";
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