<?php

require '../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if(array_key_exists("userId", $_GET))
{
    $userId=$_GET["userId"];
    $withdrawList=$conn->query("SELECT * FROM `withdraw_req` WHERE `userId`='$userId'")->fetch_all(MYSQLI_ASSOC);

    echo json_encode($withdrawList);
}