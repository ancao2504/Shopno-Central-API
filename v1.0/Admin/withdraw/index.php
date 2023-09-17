<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists('all',$_GET))
{
    $withdrawList=$conn->query("SELECT * FROM `withdraw_req` ORDER BY `id` DESC")->fetch_all(MYSQLI_ASSOC);
    echo json_encode($withdrawList);

}else if(array_key_exists('id',$_GET))
{   
    $id=$_GET["id"];
    $withdrawList=$conn->query("SELECT * FROM `withdraw_req` WHERE `id`='$id'")->fetch_assoc();
    echo json_encode($withdrawList);

}