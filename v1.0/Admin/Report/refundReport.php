<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if($_SERVER["REQUEST_METHOD"] == "GET")
{   
    $sql="SELECT b.refundId, b.bookingId, b.status, b.agentId, b.remarks, b.netCost, b.invoice, r.amountRefunded, r.actionAt, r.actionBy  
    FROM booking b 
    JOIN refund r ON b.refundId=r.refundId
    WHERE b.status IN ('Refunded', 'Return')  
    ORDER BY b.refundId DESC;";
    
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

?>