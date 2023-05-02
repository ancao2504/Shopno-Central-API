<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$SqlContent = "booking.id,
    booking.BookingId,
    booking.agentId,
    booking.invoiceId,
    booking.staffId,
    booking.tripType,
    booking.airlines,
    booking.deptFrom,
    booking.arriveTo,
    booking.pax,
    booking.pnr,
    booking.gds,
    booking.netCost,
    booking.grossCost,
    booking.baseFare,
    booking.Tax,
    booking.adultCount,
    booking.childCount,
    booking.infantCount,
    booking.adultCostBase,
    booking.childCostBase,
    booking.infantCostBase,
    booking.adultCostTax,
    booking.childCostTax,
    booking.infantCostTax,
    booking.name AS leadpassenger,
    booking.email AS leadpassengeremail,
    booking.phone AS leadpassengerphone,
    staffList.name AS bookedby,
    booking.dateTime as bookingtime,
    booking.issueTime as ticketingtime";
    


if (array_key_exists("all", $_GET)) {

  $sql = "SELECT $SqlContent FROM `booking`
          INNER JOIN staffList ON booking.staffId = staffList.staffId
          ORDER BY id DESC";
          
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      $response = $row;
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}else if(array_key_exists("search", $_GET) && array_key_exists("agentId", $_GET)) {
    
  $Search = $_GET["search"];
  $agentId = $_GET["agentId"];

  if($Search == 'all'){
    $sql = "SELECT $SqlContent FROM `booking`
            INNER JOIN staffList ON booking.staffId = staffList.staffId
            where booking.agentId='$agentId' ORDER BY id DESC";


    $result = $conn->query($sql);
    $return_arr = array();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        $response = $row;
        array_push($return_arr, $response);
      }
    }
  
  echo json_encode($return_arr);
  
  }else if($Search == 'BId'){
    $bookingId = $_GET["bookingId"];
    $sql = "SELECT $SqlContent FROM `booking`
            INNER JOIN staffList ON booking.staffId = staffList.staffId
            where booking.staffId='$agentId' AND booking.BookingId='$bookingId' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()){
        $response = $row;
        array_push($return_arr, $response);
      }
    }

    echo json_encode($return_arr);
  }else{
    $sql = "SELECT * FROM `booking` where agentId='$agentId' AND status = '$Search' ORDER BY id DESC";
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
    
  
}