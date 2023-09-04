<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("all", $_GET)){
    $sql = "SELECT * FROM booking WHERE platform LIKE 'B2C%'";
    $result = $conn->query($sql);
    $Data = array();
    if($result->num_rows > 0){
        while ($row = $result->fetch_assoc()) {
            $UserId = $row['userId'];
            $balanceQuery = mysqli_query($conn, "SELECT * FROM agent_ledger WHERE userId='$UserId'");
            $balanceData = mysqli_fetch_assoc($balanceQuery);
            $balance = isset($balanceData['lastAmount']) ? $balanceData['lastAmount']:"";
            
            $response = $row;
            $response['lastBalance'] = $balance;

            $userQuery=mysqli_query($conn, "SELECT * FROM agent WHERE userId='$UserId'");
            $userData=mysqli_fetch_assoc($userQuery);
            $response["userEmail"]=$userData["email"];
            array_push($Data, $response);
        }
        echo json_encode($Data);
    }else{
        $response['status'] ="error";
        $response['message'] ="Data Not Found";
        echo json_encode($response);
    }
}