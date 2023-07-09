<?php


require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if($_SERVER["REQUEST_METHOD"] == "GET")
{   

    $sql="SELECT gf.*, b.platform  
    FROM group_fare_booking gf
    JOIN booking b ON gf.bookingId = b.bookingId 
    ORDER BY bookingId DESC";
    $response=$conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    if(!empty($response))
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
    $conn->close();


?>