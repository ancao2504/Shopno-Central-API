<?php

require '../../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){  
    if(array_key_exists("code", $_GET)){
        $Code = $_GET["code"];
        $sql = "SELECT * FROM agent WHERE phone LIKE '$Code%'";
            $getData = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
        if(!empty($getData)){
            echo json_encode($getData);
        }else{
            echo json_encode([]);
        }

    }
}else{
  authorization($conn);
}