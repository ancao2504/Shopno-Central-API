<?php

include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    
    $conn->query("UPDATE groupfare SET deleted='true' WHERE deptTime1 < CURDATE() AND deleted='false'");
    $conn->query("UPDATE groupfare SET deactivated='true' WHERE availableSeat=0 AND deactivated='false' AND deleted='false'");
    
    
    $response = $conn->query("SELECT * FROM groupfare WHERE deactivated='false' AND deleted='false' ORDER BY groupFareId DESC")->fetch_all(MYSQLI_ASSOC);

    
    if (!empty($response)) {
        
        echo json_encode($response);

    } else {
        $response["status"] = "error";
        $response["message"] = "Data Not Found";
        echo json_encode($response);
    }

} else {
    
    $response["status"] = "error";
    $response["message"] = "Wrong Request Method";
    echo json_encode($response);

}
$conn->close();
