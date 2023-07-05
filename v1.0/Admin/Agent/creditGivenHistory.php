<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if($_SERVER["REQUEST_METHOD"] == "GET")
{   
    $sql="SELECT al.id AS acitivityId,al.agentId, a.company, a.credit, al.remarks, al.platform, al.actionAt, c.lastAmount, 
    c.loan, b.remBook, b.latestTravelDate
    FROM activitylog al
    JOIN agent a ON al.agentId=a.agentId
    JOIN (
        SELECT agentId, lastAmount, loan,
        ROW_NUMBER() OVER (PARTITION BY agentId ORDER BY createdAt DESC) AS rn
        FROM agent_ledger
    ) c ON al.agentId = c.agentId
     JOIN (
        SELECT agentId, COUNT(*) AS remBook, MAX(travelDate) AS latestTravelDate
        FROM booking
        WHERE status = 'Hold'
        GROUP BY agentId
    ) b ON a.agentId = b.agentId
    WHERE al.status='Credited' AND c.rn=1
    ORDER BY al.id DESC;";
    
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