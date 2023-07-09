<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$sql="SELECT * FROM booking WHERE DATE(travelDate) IN (CURDATE(),  DATE_ADD(travelDate, INTERVAL 7 DAY))";

$result=$conn->query($sql)->fetch_all(MYSQLI_ASSOC);

echo json_encode($result);

?>