<?php

$servername = "flyfarint.com";
$username = "flyfarin_erp"; // Put the MySQL Username
$password = "@Kayes70455"; // Put the MySQL Password
$database = "flyfarin_b2bv3"; // Put the Database Name

// $servername = "flyfarint.com";
// $username = "flyfarin_erp";
// $password = "@Kayes70455";
// $dbname = "flyfarin_b2bv3";

// Create connection for integration
$conn_integration = mysqli_connect($servername, $username, $password, $database);

// Check connection for integration
if (!$conn_integration) {
    die("Connection failed: " . mysqli_connect_error());
}