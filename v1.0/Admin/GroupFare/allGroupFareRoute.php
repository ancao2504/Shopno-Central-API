<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $sql = "SELECT deptName1 AS dept, IF(arriveName2='', arriveName1, arriveName2) AS arrive FROM groupfare
    WHERE deactivated='false' AND deleted='false'
    ORDER BY groupFareId DESC";
    $response = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

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
