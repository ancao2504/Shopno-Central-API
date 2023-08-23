<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $jsonData = json_decode(file_get_contents('php://input'), true);

    $segment = $jsonData["segment"];
    $grossFare = $jsonData["grossFare"];
    $totalSeat = $jsonData["TotalSeat"];
    $transitTime = $jsonData["TransitTime"];
    $farePolicyDate = $jsonData["farePolicyDate"];
    $farePolicyPercentage = $jsonData["farePolicyPercentage"];

    $groupFareId = "";
    $sql = "SELECT * FROM groupfare ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $outputString = preg_replace('/[^0-9]/', '', $row["groupFareId"]);

            $number = (int)$outputString + 1;

            $groupFareId = "STGF$number";
        }
    } else {
        $groupFareId = "STGF1000";
    }

    if ($segment == 1) {
        $deptFrom = $jsonData[0]["DepartureFrom"];
        $deptTime = $jsonData[0]["DepartureTime"];
        $arriveTo = $jsonData[0]["ArriveTo"];
        $arriveTime = $jsonData[0]["ArrivalTime"];
        $carrierName = $jsonData[0]["CarrierName"];
        $flightNum = $jsonData[0]["FlightNumber"];
        $flightCode = $jsonData[0]["FlightCode"];
        $cabin = $jsonData[0]["Cabin"];
        $class = $jsonData[0]["Class"];
        $baggage = $jsonData[0]["Baggage"];
        $travelTime = $jsonData[0]["TravelTime"];
        $createdAt= date("y-m-d h:i:s");

        $sql = "INSERT INTO groupfare 
            (groupFareId, segment, dept1, deptTime1,  arrive1,  arriveTime1,  carrierName1,  
            flightNum1,  flightCode1,  cabin1,  class1,  baggage1,  travelTime1, 
             transitTime, totalSeat, grossFare, availableSeat, deactivated, farePolicyDate, farePolicyPercentage, createdAt, deleted)
            VALUES 
            ('$groupFareId','$segment','$deptFrom','$deptTime','$arriveTo','$arriveTime','$carrierName','$flightNum','$flightCode', '$cabin',
            '$class','$baggage','$travelTime','$transitTime', '$totalSeat', '$grossFare', '$totalSeat', 'false', '$farePolicyDate',
            '$farePolicyPercentage', '$createdAt', 'false')";
            
    } elseif ($segment == 2) {

        $deptFrom1 = $jsonData[0]["DepartureFrom"];
        $deptTime1 = $jsonData[0]["DepartureTime"];
        $arriveTo1 = $jsonData[0]["ArriveTo"];
        $arriveTime1 = $jsonData[0]["ArrivalTime"];
        $carrierName1 = $jsonData[0]["CarrierName"];
        $flightNum1 = $jsonData[0]["FlightNumber"];
        $flightCode1 = $jsonData[0]["FlightCode"];
        $cabin1 = $jsonData[0]["Cabin"];
        $class1 = $jsonData[0]["Class"];
        $baggage1 = $jsonData[0]["Baggage"];
        $travelTime1 = $jsonData[0]["TravelTime"];

        $deptFrom2 = $jsonData[1]["DepartureFrom"];
        $deptTime2 = $jsonData[1]["DepartureTime"];
        $arriveTo2 = $jsonData[1]["ArriveTo"];
        $arriveTime2 = $jsonData[1]["ArrivalTime"];
        $carrierName2 = $jsonData[1]["CarrierName"];
        $flightNum2 = $jsonData[1]["FlightNumber"];
        $flightCode2 = $jsonData[1]["FlightCode"];
        $cabin2 = $jsonData[1]["Cabin"];
        $class2 = $jsonData[1]["Class"];
        $baggage2 = $jsonData[1]["Baggage"];
        $travelTime2 = $jsonData[1]["TravelTime"];
        $createdAt= date("y-m-d h:i:s");

        $sql = "INSERT INTO groupfare 
            (groupFareId,segment, dept1, dept2, deptTime1, deptTime2, arrive1, arrive2, arriveTime1, arriveTime2, carrierName1, carrierName2, 
            flightNum1, flightNum2, flightCode1, flightCode2, cabin1, cabin2, class1, class2, baggage1, baggage2, travelTime1, 
            travelTime2, transitTime, totalSeat, grossFare, availableSeat, deactivated, farePolicyDate, farePolicyPercentage, createdAt, deleted)
            VALUES 
            ('$groupFareId','$segment','$deptFrom1','$deptFrom2','$deptTime1','$deptTime2','$arriveTo1','$arriveTo2','$arriveTime1','$arriveTime2',
            '$carrierName1','$carrierName2','$flightNum1','$flightNum2','$flightCode1','$flightCode2', '$cabin1','$cabin2','$class1','$class2',
            '$baggage1','$baggage2','$travelTime1','$travelTime2','$transitTime', '$totalSeat', '$grossFare', '$totalSeat', 'false', '$farePolicyDate',
            '$farePolicyPercentage', '$createdAt', 'false')";
    }

    if ($conn->query($sql)) {

        $response["status"] = "success";
        $response["message"] = "Group Fare Added Successfully!";
    
    } else {

        $response["status"] = "error";
        $response["message"] = "Query Failed!";
    
    }

    echo json_encode($response);

} else {
    
    $response["status"] = "error";
    $response["message"] = "Wrong Request Method";

    echo json_encode($response);

}

$conn->close();
