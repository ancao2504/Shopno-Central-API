<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if($_SERVER["REQUEST_METHOD"] == "GET"){
     if((array_key_exists("agentId", $_GET) && array_key_exists("userId" , $_GET)) || array_key_exists("all", $_GET) || array_key_exists("page", $_GET) || array_key_exists("bookingId", $_GET) || array_key_exists("status", $_GET) || array_key_exists("pages", $_GET)){
            $agentId = $_GET['agentId'];
            $userId = $_GET['userId'];
            $bookingId = isset($_GET['bookingId']) ? $_GET['bookingId'] :"";
            $status = isset($_GET['status']) ? $_GET['status'] :"";
            $getAll = isset($_GET['all']) ? $_GET['all'] : "";
            $page = isset($_GET['page']) ? $_GET['page']: "";
            $pages = isset($_GET['pages']) ? $_GET['pages'] : "";

            $checker = $conn->query("SELECT * FROM booking WHERE agentId = '$agentId' AND userId ='$userId'")->fetch_all(MYSQLI_ASSOC);
            if(!empty($checker)){
                getData($agentId, $userId, $bookingId, $page, $pages, $getAll, $conn);
            }else if(empty($checker)){
                $response['status'] = "error";
                $response["message"] = "Agent Or User Is Invalid";
                echo json_encode($response);
            }       
     }
}

/**
 * Get Data From Booking Data Table
 */

 function getData($agentId, $userId, $bookingId, $page, $pages, $getAll, $conn){
    $sql = "SELECT * FROM `booking` where agentId='$agentId' AND platform='WLB2C' ORDER BY id DESC";
    $result = $conn->query($sql);
    $totaldata = $conn->query("SELECT * FROM `booking` where agentId='$agentId' AND platform='WLB2C'")->num_rows;

    $return_arr = array();
    $Data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $agentId = $row['agentId'];
        $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $data = mysqli_fetch_assoc($query);
        if (!empty($data)) {
            $companyname = $data['company'];
            $companyphone = $data['phone'];
        }

        $response = $row;
        $response['companyname'] = "$companyname";
        $response['companyphone'] = "$companyphone";

        array_push($Data, $response);
    }
}
        $return_arr['data'] = $Data;
        
  
    echo json_encode($return_arr);
 }
$conn->close();
?>