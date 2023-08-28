<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if($_SERVER["REQUEST_METHOD"] == "GET")
{   

    $sql="SELECT vendor.id, vendor.vendorId AS vId, vendor AS vName, COUNT(*) AS totalBooking, SUM(gds_segment) AS segment, 
    SUM(netCost) AS bookingAmount, SUM(invoice) AS vendorAmount
    FROM booking
    JOIN vendor ON vendor.name=booking.vendor 
    WHERE status='Ticketed'
    GROUP BY vName  
    ORDER BY segment DESC";

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