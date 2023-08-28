<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("all", $_GET)){
    $data = $conn->query("SELECT * FROM booking WHERE status = 'Ticketed'")->fetch_assoc();

    if(!empty($data)){
        echo json_encode($data);
    }else {
    $response['status'] = 'error';
    $response['message'] ="Data Not Found";
    echo json_encode($response);
    }

}else if(array_key_exists("search", $_GET)){
    $Search = $_GET['search'];
    $getData = $conn->query("SELECT * FROM booking WHERE status = 'Ticketed' AND  ")->fetch_assoc();
    
}else if(array_key_exists("startdate", $_GET) && array_key_exists("enddate", $_GET)){
        $StartDate = $_GET['startdate'];
        $EndDate = $_GET['enddate'];
        $getData = $conn->query("SELECT * FROM booking WHERE bookedAt=< $StartDate AND bookedAt=>$EndDate")->fetch_assoc();
        if(!empty($getData)){
            echo json_encode($getData);
        }else{
            $response['status'] ="error";
            $response['message'] ="Data not found";
            echo json_encode($response);
        }
}

$conn->close();
?>