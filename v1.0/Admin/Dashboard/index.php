<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$sqlBooking=
"SELECT 'Hold' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Hold'

UNION ALL

SELECT 'Issue In Processing' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Issue In Processing'

UNION ALL

SELECT 'Ticketed' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Ticketed'

UNION ALL

SELECT 'Issue Rejected' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Issue Rejected'

UNION ALL

SELECT 'Refund In Processing' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Refund In Processing'

UNION ALL

SELECT 'Void In Processing' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Void In Processing'

UNION ALL

SELECT 'Reissue In Processing' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Reissue In Processing'

UNION ALL

SELECT 'Refunded' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Refunded'

UNION ALL

SELECT 'Voided' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Voided'

UNION ALL

SELECT 'Reissued' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Reissued'

UNION ALL

SELECT 'Refund Rejected' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Refund Rejected'

UNION ALL

SELECT 'Void Rejected' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Void Rejected'

UNION ALL

SELECT 'Reissue Rejected' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Reissue Rejected'

UNION ALL

SELECT 'Cancelled' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt) = CURDATE() AND status = 'Cancelled'

UNION ALL

SELECT 'today' AS category, COUNT(*) AS count
FROM `booking`
WHERE DATE(travelDate) = CURDATE() AND status = 'Ticketed'

UNION ALL

SELECT 'tomorrow' AS category, COUNT(*) AS count
FROM `booking`
WHERE DATE(travelDate) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND status = 'Ticketed'

UNION ALL

SELECT 'dayAfterTomorrow' AS category, COUNT(*) AS count
FROM `booking`
WHERE DATE(travelDate) = DATE_ADD(CURDATE(), INTERVAL 2 DAY) AND status = 'Ticketed'

UNION ALL

SELECT 'totalPendingDepositAmount' AS category, COALESCE(SUM(amount), 0) AS count
FROM `deposit_request`
WHERE DATE(createdAt) = CURDATE() AND status = 'pending'

UNION ALL

SELECT 'totalApprovedDepositAmount' AS category, COALESCE(SUM(amount), 0) AS count
FROM `deposit_request`
WHERE DATE(actionAt) = CURDATE() AND status = 'approved'

UNION ALL

SELECT 'totalRejectedDepositAmount' AS category, COALESCE(SUM(amount), 0) AS count
FROM `deposit_request`
WHERE DATE(actionAt) = CURDATE() AND status = 'rejected'

UNION ALL

SELECT 'todayTotalTicketedAmount' AS category, COALESCE(SUM(netCost),0) AS count
FROM `booking`
WHERE DATE(lastUpdated) = CURDATE() AND status='ticketed'

UNION ALL

SELECT 'pendingAgentCount' AS category, COUNT(*) AS count
FROM `agent`
WHERE DATE(joinAt)=CURDATE() AND status='pending'

UNION ALL

SELECT 'totalSearchCount' AS category, COUNT(*) AS count
FROM search_history
WHERE DATE(searchTime)=CURDATE()

UNION ALL 

SELECT 'totalBookCount' AS category, COUNT(*) AS count
FROM booking
WHERE DATE(bookedAt)=CURDATE()

UNION ALL

SELECT 'bookingClearanceCount' AS category, COUNT(*) AS count
FROM booking 
WHERE DATE(lastUpdated)=CURDATE() AND status='Issued'

UNION ALL

SELECT 'bookingClearanceAmount' AS category, COALESCE(SUM(netCost),0) AS count
FROM booking 
WHERE DATE(lastUpdated)=CURDATE() AND status='Issued'

UNION ALL

SELECT 'refundCount' AS category, COUNT(*) AS count
FROM booking 
WHERE DATE(lastUpdated)=CURDATE() AND status IN ('Refunded', 'Return')

UNION ALL

SELECT 'refundAmount' AS category, COALESCE(SUM(netCost),0) AS count
FROM booking 
WHERE DATE(lastUpdated)=CURDATE() AND status IN ('Refunded', 'Return')

UNION ALL

SELECT 'customerAmount' AS category, COALESCE(SUM(grossCost),0) AS count
FROM booking 
WHERE DATE(lastUpdated)=CURDATE() AND status='Ticketed'

UNION ALL

SELECT 'agentAmount' AS category, COALESCE(SUM(subagentCost),0) AS count
FROM booking 
WHERE DATE(lastUpdated)=CURDATE() AND status='Ticketed'
";

$result = $conn->query($sqlBooking)->fetch_all(MYSQLI_ASSOC);

// echo json_encode($result);

$custAm=$result[28]["count"];
$subagAm=$result[20]["count"];
$lossProf=$custAm-$subagAm;


$response["holdCount"]=$result[0]["count"];
$response["issueInProcessingCount"]=$result[1]["count"];
$response["ticketedCount"]=$result[2]["count"];
$response["issueRejectedCount"]=$result[3]["count"];
$response["refundInProcessingCount"]=$result[4]["count"];
$response["voidInProcessingCount"]=$result[5]["count"];
$response["reissueInProcessingCount"]=$result[6]["count"];
$response["refundedCount"]=$result[7]["count"];
$response["voided"]=$result[8]["count"];
$response["reissuedCount"]=$result[9]["count"];
$response["refundRejectedCount"]=$result[10]["count"];
$response["voidRejectedCount"]=$result[11]["count"];
$response["reissueRejectedCount"]=$result[12]["count"];
$response["cancelledCount"]=$result[13]["count"];

$response["todayFly"]=$result[14]["count"];
$response["tomorrowFly"]=$result[15]["count"];
$response["dayAfterTomorrowFLy"]=$result[16]["count"];

$response["totalPendingDepositAmount"]=$result[17]["count"];
$response["todayTotalDeposit"]=$result[18]["count"];
$response["totalRejectedDepositAmount"]=$result[19]["count"];

$response["todayTotalTicketedAmount"]=$result[20]["count"];

$response["pendingAgentCount"]=$result[21]["count"];

$response["totalSearchCount"]=$result[22]["count"];

$response["totalBookCount"]=$result[23]["count"];

$response["bookingClearanceCount"]=$result[24]["count"];
$response["bookingClearanceAmount"]=$result[25]["count"];

$response["refundCount"]=$result[26]["count"];
$response["refundAmount"]=$result[27]["count"];

$response["customerAmount"]=$result[28]["count"];
$response["agentAmount"]=$result[29]["count"];
$response["afterMarkUp"]=$response["todayTotalTicketedAmount"];
$response["lossProfit"]=$lossProf;




echo json_encode($response);

?>