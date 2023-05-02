<?php
include "../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("option", $_GET))
{
    $Option = $_GET["option"];
    if($Option == "globalmarkup"){
        $data = $conn->query("SELECT alliMarkup, alldMarkup, alliMarkupType, alldMarkupType FROM agent LIMIT 1")->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    }
    if($Option == "agentmarkup"){
        $data = $conn->query("SELECT iMarkup, dMarkup, iMarkupType, dMarkupType  FROM agent LIMIT 1")->fetch_all(MYSQLI_ASSOC);
    echo json_encode($data);
    }
}