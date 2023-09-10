<?php
require '../../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("deactive", $_GET) && array_key_exists("offerId", $_GET))
{   
    $offerId=$_GET["offerId"];
    $time = date("dmYHis");

    if($conn->query("UPDATE offers SET `active`='false', `last_updated_at`='$time' WHERE offerId='$offerId'"))
    {
        echo json_encode(
            array(
                "status" => "success",
                "message" => "$offerId is deactivated"
            )
            );
    }
    else
    {
        echo json_encode(
            array(
                "status" => "error",
                "message" => "$offerId deactivation failed"
            )
            );
    }


}
else if(array_key_exists("active", $_GET) && array_key_exists("offerId", $_GET))
{
    $offerId=$_GET["offerId"];
    if($conn->query("UPDATE offers SET `active`='true', `last_updated_at`='$time' WHERE offerId='$offerId'"))
    {
        echo json_encode(
            array(
                "status" => "success",
                "message" => "$offerId is activated again"
            )
            );
    }
    else
    {
        echo json_encode(
            array(
                "status" => "error",
                "message" => "$offerId activation failed"
            )
            );
    }
}
