<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("get", $_GET)){
    $data = $conn->query("SELECT * FROM currency ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
    if(!empty($data)){
        echo json_encode($data);
    }else{
        echo json_encode([]);
    }
}else if (array_key_exists("add", $_GET)) {
    $_POST = json_decode(file_get_contents("php://input"), true);

   $country = $_POST["country"];
    $conversionRate = $_POST["conversionrate"];
    $currency = $_POST["currency"];
    $flag = $_POST["flag"];
    $date = date("Y-m-d H:i:s");


    if (isset($country)) {
        $sql = "INSERT INTO currency (code, rate, country, flag, status , created_at) VALUES ('$currency', '$conversionRate', '$country','$flag','deactivate', '$date')";

        if ($conn->query($sql)) {
            echo json_encode([
                "status" => "success",
                "message" => "currency added successfully",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "query failed",
            ]);
        }
    }
}else if(array_key_exists("edit", $_GET)){
    
    $_POST = json_decode(file_get_contents("php://input"), true);

    $Id = $_POST["id"];
    $country =  $_POST["country"];
    $conversionRate = $_POST["conversionrate"];
    $currency = $_POST["currency"];
    $flag = $_POST["flag"];
    $date = date("Y-m-d H:i:s");

    $sql = "UPDATE currency SET code='$currency', rate='$conversionRate', country='$country', flag='$flag' WHERE id='$Id'";

        if ($conn->query($sql)) {
            echo json_encode([
                "status" => "success",
                "message" => "currency update successfully",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "query failed",
            ]);
        }
    
}else if(array_key_exists("delete", $_GET)){
    $_POST = json_decode(file_get_contents("php://input"), true);

    $Id = $_POST["id"];
    $sql = "DELETE FROM currency WHERE id='$Id'";

    if ($conn->query($sql)) {
        echo json_encode([
            "status" => "success",
            "message" => "currency delete successfully",
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "query failed",
        ]);
    }
}else if(array_key_exists("status", $_GET)){
    $_POST = json_decode(file_get_contents("php://input"), true);

    $status = $_POST["status"];
    $id = $_POST["id"];
    $sql = "UPDATE currency SET `status`='$status' where id='$id'";

    if ($conn->query($sql)) {
        echo json_encode([
            "status" => "success",
            "message" => "currency $status",
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "query failed",
        ]);
    }
}
