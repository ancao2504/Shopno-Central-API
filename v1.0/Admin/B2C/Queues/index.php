<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("all", $_GET)){
    $sql = "SELECT * FROM booking WHERE platform = 'B2C'";
    $result = $conn->query($sql);
    $Data = array();
    if($result->num_rows > 0){
        while ($row = $result->fetch_assoc()) {
            $UserId = $row['userId'];
            $lastAmountSql = $conn->query("SELECT lastAmount FROM agent_ledger WHERE userId = '$UserId' ORDER BY id LIMIT 1")->fetch_all(MYSQLI_ASSOC);
            if(!empty($lastAmountSql)){
                $LastAmount = $lastAmountSql['lastAmount'];
            }else{
                $LastAmount = 0;
            }
            
            $response = $row;
            $response['lastBalance'] = $LastAmount;
            array_push($Data, $response);
        }
        echo json_encode($Data);
    }else{
        $response['status'] ="error";
        $response['message'] ="Data Not Found";
        echo json_encode($response);
    }
}