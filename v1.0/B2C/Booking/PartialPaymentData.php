<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {

    $sql = "SELECT bookingId,PPstatus,bookedAt,deptFrom,arriveTo,pax,tripType, travelDate,netCost,paidAmount,dueAmount,dueDate   FROM `booking` where isPartial='yes' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {         
            array_push($return_arr, $row);
        }
        echo json_encode($return_arr);
    }
}else if (array_key_exists("agentId", $_GET)) {
    $agentId = $_GET["agentId"];

    $data  = $conn->query("SELECT bookingId,PPstatus,bookedAt,deptFrom,arriveTo,pax,tripType, travelDate,netCost,paidAmount,dueAmount,dueDate FROM `booking` where isPartial='yes' AND agentId='$agentId' ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

    $Today = date('Y-m-d');

    $DueToday = $conn->query("SELECT sum(dueAmount) as amount from `booking` where agentId='$agentId' AND dueDate LIKE '$Today%'")->fetch_all();
    $TotalDue = $conn->query("SELECT sum(dueAmount) as amount from `booking` where agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);

        $response['data'] = $data;
        $response['outstanding'] = $TotalDue[0]['amount'];
        $response['todaydue'] = isset($DueToday[0]['amount']) ? $DueToday[0]['amount'] : '0' ;
        
        echo json_encode($response);
}

?>