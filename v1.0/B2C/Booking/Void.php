<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $voidId = "";
    $sql1 = "SELECT * FROM void ORDER BY voidId DESC LIMIT 1";
    $result = $conn->query($sql1);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $outputString = preg_replace('/[^0-9]/', '', $row["voidId"]);
            $number = (int) $outputString + 1;
            $voidId = "STVD$number";
        }
    } else {
        $voidId = "STVD1000";
    }

    $userId = $_POST["userId"];
    $bookingId = $_POST["bookingId"];
    $requestedBy = $_POST["requestedBy"];
    $paxDetails = $_POST['passengerData'];

    $passData = array();
    foreach ($paxDetails as $paxDet) {
        $name = $paxDet['name'];
        $ticket = $paxDet['ticket'];

        $data = "($name-$ticket)";
        array_push($passData, $data);
    }

    $dataPax = implode('', $passData);

    $createdTime = date('Y-m-d H:i:s');
    $DateTime = date("D d M Y h:i A");

    if (isset($bookingId)) {
        $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
        $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

        if (!empty($rowTravelDate)) {
            $travelDate = $rowTravelDate['travelDate'];
            $userId = $rowTravelDate['userId'];
            $pax = $rowTravelDate['pax'];
            $gds = $rowTravelDate['gds'];
            $pnr = $rowTravelDate['pnr'];
            $Type = $rowTravelDate['tripType'];
            $Airlines = $rowTravelDate['airlines'];
            $TicketId = $rowTravelDate['ticketId'];
            $TicketCost = $rowTravelDate['netCost'];
            $arriveTo = $rowTravelDate['arriveTo'];
            $deptFrom = $rowTravelDate['deptFrom'];
            $tripType = $rowTravelDate['tripType'];

        }
    }

    $voidtextBy = '';

    $sql = "INSERT INTO `void`(`voidId`,`userId`,`platform`, `bookingId`,`ticketId`,`passengerDetails`,`status`,`requestedBy`,`requestedAt`)
             VALUES ('$voidId','$userId','B2C','$bookingId','$TicketId','$dataPax','pending','$requestedBy','$createdTime')";

    if ($conn->query($sql) === true) {
        $conn->query("UPDATE `booking` SET `status`='Void In Processing',`voidId`='$voidId',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
        $conn->query("INSERT INTO `activitylog`(`ref`,`userId`,`status`,`remarks`,`actionRef`,`actionBy`,`actionAt`)
              VALUES ('$bookingId','$userId','Void In Processing','$userId Void request from WLB2C','$voidId','$requestedBy','$createdTime')");

        $response['status'] = "success";
        $response['VoidId'] = "$voidId";
        $response['message'] = "Ticket Void Request Successfully";

    } else {
        $response['status'] = "error";
        $response['message'] = "Query Failed";
    }

    echo json_encode($response);

}
