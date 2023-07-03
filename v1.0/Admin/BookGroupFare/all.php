<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if($_SERVER["REQUEST_METHOD"] == "GET")
{
    $response=$conn->query("SELECT p.bookingId, p.agentId, gf.status, p.fName, p.lName, p.gender, p.dob, p.passNo, p.passEx 
        FROM passenger p
        JOIN group_fare_booking gf ON p.bookingId=gf.bookingId")->fetch_all(MYSQLI_ASSOC);


    if(count($response)>0)
    {
        echo json_encode($response);
    }
    else
    {
        $response["status"] = "Failed";
        $response["message"] = "Data Not Found";
        
        echo json_encode($response);
    }
    
}
else
{
    $response["status"] = "Failed";
    $response["message"] = "Wrong Request Method";
    
    echo json_encode($response);
}    

?>