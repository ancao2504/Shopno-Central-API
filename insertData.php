<?php

// Function to update data from JSON file into MySQL table
function insertDataIntoSql($jsonFilePath)
{
    $servername = "flyfarint.com";
    $username = "flyfarin_shopno";
    $password = "*04ruXfEfq";
    $dbname = "flyfarin_shopno";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Read JSON file
    $jsonData = file_get_contents($jsonFilePath);

    // Decode JSON data
    $data = json_decode($jsonData, true);

    // Prepare and execute SQL statements for each record
    foreach ($data as $record) {
        $vendorCode = $record['VENDOR_CODE'];
        $poiName = $record['POI_NAME'];
        $cityName = $record['CITY_NAME'];
        $countryCode = $record['COUNTRY_CODE'];
        $latitude = $record['LATITUDE'];
        $longitude = $record['LONGITUDE'];

        // Prepare SQL statement
        $sql = "INSERT INTO airport_lists (`VENDOR_CODE`, `POI_NAME`, `CITY_NAME`, `COUNTRY_CODE`, `LATITUDE`, `LONGITUDE`) 
                VALUES ('$vendorCode', '$poiName', '$cityName', '$countryCode', '$latitude', '$longitude')";

        // Execute SQL statement
        if ($conn->query($sql) === FALSE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Close connection
    $conn->close();
}

// Usage: Provide the path to your data.json file
// $jsonFilePath = './data.json';
$jsonFilePath = '';
insertDataIntoSql($jsonFilePath);
?>
