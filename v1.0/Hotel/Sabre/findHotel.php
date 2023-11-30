<?php

include "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists('all', $_GET)) {
    $queryRes = mysqli_query($conn, "SELECT * FROM airport_lists");

    if ($queryRes) {
        $queryResult = mysqli_fetch_all($countryList, MYSQLI_ASSOC);


        echo json_encode($queryResult);
    } else {
        $response = [
            "status" => "error",
            "message" => "Failed to retrieve country data."
        ];
        echo json_encode($response);
    }
} else if (array_key_exists('query', $_GET)) {
    $query= $_GET['query'];
    $queryRes = mysqli_query($conn, "SELECT * FROM airport_lists WHERE CITY_NAME='$query' OR POI_NAME='$query' ");

    if ($queryRes) {

        $queryResult = mysqli_fetch_all($queryRes, MYSQLI_ASSOC);

        echo json_encode($queryResult);
    } else {
        $response = [
            "status" => "error",
            "message" => "No Data Found."
        ];
        echo json_encode($response);
    }
} else {
    $response = [
        "status" => "error",
        "message" => "No Data Found."
    ];
    echo json_encode($response);
}