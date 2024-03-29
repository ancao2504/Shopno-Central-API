<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // echo $_POST["requestedBody"];
    $jsonData = json_decode(file_get_contents('php://input'), true);
    // echo json_encode($jsonData);


    // $passengersNames=json_decode($_POST["travelerNames"], true);
    $gfId = $jsonData["groupFareId"];
    $agentId = $jsonData["agentId"];
    $name = $jsonData["name"];
    $phone = $jsonData["phone"];
    $email = $jsonData["email"];
    $pax = $jsonData["pax"];
    $grossCost = $jsonData["grossCost"];
    $platform = "B2B";

    // echo json_encode($jsonData);


    /* The line of code is executing a SQL query to select all the columns and rows from the "groupfare"
table where the "groupFareId" column matches the value of the variable "". The fetch_all()
function is then used to retrieve all the rows returned by the query as an array. */
    $flightData = $conn->query("SELECT * FROM groupfare WHERE groupFareId='$gfId'")->fetch_assoc();

    $gfId = $flightData["groupFareId"];
    $segment = $flightData["segment"];
    $deptCode1 = $flightData["deptCode1"];
    $deptCode2 = $flightData["deptCode2"];
    $deptTime1 = $flightData["deptTime1"];
    $deptTime2 = $flightData["deptTime2"];
    $arriveCode1 = $flightData["arriveCode1"];
    $arriveCode2 = $flightData["arriveCode2"];
    $arriveTime1 = $flightData["arriveTime1"];
    $arriveTime2 = $flightData["arriveTime2"];
    $carrierCode1 = $flightData["carrierCode1"];
    $carrierCode2 = $flightData["carrierCode2"];
    $flightNum1 = $flightData["flightNum1"];
    $flightNum2 = $flightData["flightNum2"];
    $flightCode1 = $flightData["flightCode1"];
    $flightCode2 = $flightData["flightCode2"];
    $cabin1 = $flightData["cabin1"];
    $cabin2 = $flightData["cabin2"];
    $class1 = $flightData["class1"];
    $class2 = $flightData["class2"];
    $baggage1 = $flightData["baggage1"];
    $baggage2 = $flightData["baggage2"];
    $travelTime1 = $flightData["travelTime1"];
    $travelTime2 = $flightData["travelTime2"];
    $transitTime = $flightData["transitTime"];
    $totalSeat = $flightData["totalSeat"];
    $availableSeat = $flightData["availableSeat"];
    $grossFare = $flightData["grossFare"];
    $createdAt = $flightData["createdAt"];
    $deactivated = $flightData["deactivated"];
    $deleted = $flightData["deleted"];
    $currentDateTime = date('Y-m-d H:i:s');

    //regex
    $deptName1 = str_replace("'", "''", $flightData["deptName1"]);
    $deptAddress1 = str_replace("'", "''", $flightData["deptAddress1"]);
    $deptName2 = str_replace("'", "''", $flightData["deptName2"]);
    $deptAddress2 = str_replace("'", "''", $flightData["deptAddress2"]);
    $arriveName1 = str_replace("'", "''", $flightData["arriveName1"]);
    $arriveAddress1 = str_replace("'", "''", $flightData["arriveAddress1"]);
    $arriveName2 = str_replace("'", "''", $flightData["arriveName2"]);
    $arriveAddress2 = str_replace("'", "''", $flightData["arriveAddress2"]);
    $carrierName1 = str_replace("'", "''", $flightData["carrierName1"]);
    $carrierNameBangla1 = str_replace("'", "''", $flightData["carrierNameBangla1"]);
    $carrierName2 = str_replace("'", "''", $flightData["carrierName2"]);
    $carrierNameBangla2 = str_replace("'", "''", $flightData["carrierNameBangla2"]);

    /*putting arrival destination*/
    $arrivalCode = (empty($arriveCode2)) ? $arriveCode1 : $arriveCode2;
    $arrivalName = (empty($arriveName2)) ? $arriveName1 : $arriveName2;
    $arrivalAddress = (empty($arriveAddress2)) ? $arriveAddress1 : $arriveAddress2;

    $airlines = (empty($carrierName2)) ? $carrierName1 : "$carrierName1  - $carrierName2";


    /* This code block is checking if the number of groupfare passengers is greater than the available
    seats left in the group fare, which can be found in database. 
    This is a validation check to ensure that the number of passengers does not exceed the available
    seats before proceeding with the booking process. */
    if ($pax > $availableSeat) {
        $response["pax"] = $pax;
        $response["availableSeat"] = $availableSeat;
        $response["status"] = "error";
        $response["message"] = "pax is more than available seats";
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT lastAmount FROM agent_ledger WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1";

    /* This code block is checking if the agent has sufficient balance in their ledger to make the
    group fare booking. */
    if ($row = $conn->query($sql)->fetch_assoc()) {

        $lastAmount = $row['lastAmount'];

        if ($lastAmount >= $grossCost) {
            // echo("$lastAmount\n$grossCost\n");
            $newAmount = $lastAmount - $grossCost;
            // echo("$newAmount\n");
            $details = "GFBOOKING-$gfId" . "_PAX-$pax";

            $sql = "INSERT INTO agent_ledger (agentId, purchase, lastAmount, transactionId, details, reference, actionBy, createdAt)
            VALUES ('$agentId', '$grossCost', '$newAmount', '$gfId', '$details', '$gfId', '$agentId' ,'$currentDateTime')";

            if ($conn->query($sql)) {

                $bookingId = "";
                $sql = "SELECT * FROM gf_booking ORDER BY id DESC LIMIT 1";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {

                        $outputString = preg_replace('/[^0-9]/', '', $row["bookingId"]);
                        $number = (int)$outputString + 1;
                        $bookingId = "STGFB$number";
                    }
                } else {
                    $bookingId = "STGFB1000";
                }

                $sql = "INSERT gf_booking
                (bookingId, agentId, customer_email, customer_phone, customer_name, pax, deptFromCode, deptFromName, deptFromAddress, 
                airlines, arriveToCode, arriveToName, arriveToAddress, segment, `status`, travelDate, bookedAt,  grossCost, groupFareId)
                VALUES ('$bookingId','$agentId',  '$email', '$phone', '$name', '$pax', '$deptCode1', '$deptName1', '$deptAddress1', 
                '$airlines', '$arrivalCode', '$arrivalName', '$arrivalAddress', '$segment', 'Issued', '$deptTime1', '$currentDateTime',
                 '$grossCost', '$gfId')";

                // echo ($sql); exit;
                if ($conn->query($sql)) {

                    /* The code is updating the `availableSeat` column in the `groupfare` table by
                    subtracting the value of `` (number of passengers) from the current value of
                    `availableSeat`. It is updating the row where the `groupFareId` matches the
                    value of ``. */
                    $sql = "UPDATE groupfare SET 
                    availableSeat=availableSeat-'$pax' 
                    WHERE groupFareId='$gfId'";

                    if ($conn->query($sql)) {
                        $response["status"] = "success";
                        $response["message"] = "Group Fare Booked Successfully";
                    } else {
                        $response["status"] = "error";
                        $response["message"] = "Available Seat Update Failed";
                    }
                } else {
                    $response["status"] = "error";
                    $response["message"] = "Booking Failed";
                }
            } else {
                $response["status"] = "error";
                $response["message"] = "Insert Ledger Failed";
            }
        } else {

            $response["status"] = "error";
            $response["message"] = "Insufficient Balance";
        }
    } else {

        $response["status"] = "error";
        $response["message"] = "Ledger not found";
    }
} else {

    $response["status"] = "error";
    $response["message"] = "Wrong Request Method";
}

echo json_encode($response);
