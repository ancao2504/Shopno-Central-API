<?php

include "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists('all', $_GET)) {
    $countryList = mysqli_query($conn, "SELECT * FROM airport_lists");

    if ($countryList) {
        $queryResult = mysqli_fetch_all($countryList, MYSQLI_ASSOC);

        // Initialize an array to store the final result with cities
        // $result = [];

        // Loop through each country
        // foreach ($countryData as $country) {
        //     $id = $country['id'];
        //     $cityList = mysqli_query($conn, "SELECT * FROM city_list WHERE ref = '$id'");
            
        //     if ($cityList) {
        //         $cityData = mysqli_fetch_all($cityList, MYSQLI_ASSOC);
        //         $country['cities'] = $cityData;
        //         $result[] = $country; // Add the country with cities to the result
        //     }
        // }

        echo json_encode($queryResult);
    } else {
        $response = [
            "status" => "error",
            "message" => "Failed to retrieve country data."
        ];
        echo json_encode($response);
    }
} else if (array_key_exists('query', $_GET)) {
    $query= $_GET['country'];
    $queryRes = mysqli_query($conn, "SELECT * FROM airport_lists WHERE CITY_NAME='$query' OR POI_NAME='$query' ");

    if ($queryRes) {

        $queryResult = mysqli_fetch_all($countryList, MYSQLI_ASSOC);

        // Initialize an array to store the final result with cities
        // $result = [];

        // Loop through each country
        // foreach ($countryData as $country) {
        //     $id = $country['id'];
        //     $cityList = mysqli_query($conn, "SELECT * FROM city_list WHERE ref = '$id'");
            
        //     if ($cityList) {
        //         $cityData = mysqli_fetch_all($cityList, MYSQLI_ASSOC);
        //         $country['cities'] = $cityData;
        //         $result[] = $country; // Add the country with cities to the result
        //     }
        // }

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
        "message" => "Invalid Request"
    ];
    echo json_encode($response);
}