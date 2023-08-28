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

    // $farePolicyDate = $jsonData["farePolicyDate"];
    // $farePolicyPercentage = $jsonData["farePolicyPercentage"];

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
        //flight data
         

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

        //departure airport data
        $deptCode1=$deptFrom["code"];
        $deptName1=str_replace("'", "''",$deptFrom["name"]); 
        $deptAddress1=str_replace("'", "''",$deptFrom["Address"]);
        
        //arrival airport data
        $arriveCode1=$arriveTo["code"];
        $arriveName1=str_replace("'", "''",$arriveTo["name"]);
        $arriveAddress1=str_replace("'", "''",$arriveTo["Address"]);

        //carrier data
        $carrierCode1=$carrierName["code"];
        $carrierName1=str_replace("'", "''",$carrierName["name"]);
        $carrierNameBangla1=str_replace("'", "''",$carrierName["nameBangla"]);
        

        $sql = "INSERT INTO groupfare 
            (groupFareId, segment, deptCode1, deptName1, deptAddress1, deptTime1, arriveName1, arriveAddress1, arriveCode1,  arriveTime1,  
            carrierName1, carrierCode1, carrierNameBangla1, flightNum1,  flightCode1,  cabin1,  class1,  baggage1,  travelTime1, 
            transitTime, totalSeat, grossFare, availableSeat, deactivated, createdAt, deleted)
            VALUES 
            ('$groupFareId','$segment','$deptCode1', '$deptName1', '$deptAddress1','$deptTime','$arriveName1', '$arriveAddress1', 
            '$arriveCode1', '$arriveTime','$carrierName1', '$carrierCode1', '$carrierNameBangla1','$flightNum','$flightCode', '$cabin', 
            '$class','$baggage','$travelTime','$transitTime', '$totalSeat', '$grossFare', '$totalSeat', 'false', '$createdAt', 'false')";
        
    
    } elseif ($segment == 2) {

        //segment 1's flight data
        $deptFrom1 = $jsonData[0]["DepartureFrom"];
        $deptTime1 = $jsonData[0]["DepartureTime"];
        $arriveTo1 = $jsonData[0]["ArriveTo"];
        $arriveTime1 = $jsonData[0]["ArrivalTime"];
        $carrier1= $jsonData[0]["CarrierName"];
        $flightNum1 = $jsonData[0]["FlightNumber"];
        $flightCode1 = $jsonData[0]["FlightCode"];
        $cabin1 = $jsonData[0]["Cabin"];
        $class1 = $jsonData[0]["Class"];
        $baggage1 = $jsonData[0]["Baggage"];
        $travelTime1 = $jsonData[0]["TravelTime"];
        
        //segment 2's flight data
        $deptFrom2 = $jsonData[1]["DepartureFrom"];
        $deptTime2 = $jsonData[1]["DepartureTime"];
        $arriveTo2 = $jsonData[1]["ArriveTo"];
        $arriveTime2 = $jsonData[1]["ArrivalTime"];
        $carrier2 = $jsonData[1]["CarrierName"];
        $flightNum2 = $jsonData[1]["FlightNumber"];
        $flightCode2 = $jsonData[1]["FlightCode"];
        $cabin2 = $jsonData[1]["Cabin"];
        $class2 = $jsonData[1]["Class"];
        $baggage2 = $jsonData[1]["Baggage"];
        $travelTime2 = $jsonData[1]["TravelTime"];
        $createdAt= date("y-m-d h:i:s");

        //segment 1's departure airport data
        $deptCode1=$deptFrom1["code"]; 
        $deptName1=str_replace("'", "''",$deptFrom1["name"]); 
        $deptAddress1=str_replace("'", "''",$deptFrom1["Address"]);
        
        //segment 2's departure airport data
        $deptCode2=$deptFrom2["code"]; 
        $deptName2=str_replace("'", "''",$deptFrom2["name"]); 
        $deptAddress2=str_replace("'", "''",$deptFrom2["Address"]);

        //segment 1's arrival airport data
        $arriveCode1=$arriveTo1["code"];
        $arriveName1=str_replace("'", "''",$arriveTo1["name"]);
        $arriveAddress1=str_replace("'", "''",$arriveTo1["Address"]);
        
        //segment 2's arrival airport data
        $arriveCode2=$arriveTo2["code"];
        $arriveName2=str_replace("'", "''",$arriveTo2["name"]);
        $arriveAddress2=str_replace("'", "''",$arriveTo2["Address"]);

        //segment 1's carrier data
        $carrierCode1=$carrier1["code"];
        $carrierName1=str_replace("'", "''",$carrier1["name"]);
        $carrierNameBangla1=str_replace("'", "''",$carrier1["nameBangla"]);

        //segment 2's carrier data
        $carrierCode2=$carrier2["code"];
        $carrierName2=str_replace("'", "''",$carrier2["name"]);
        $carrierNameBangla2=str_replace("'", "''",$carrier2["nameBangla"]);

        $sql = "INSERT INTO groupfare 
            (groupFareId, segment, deptCode1, deptName1, deptAddress1, deptCode2, deptName2, deptAddress2, deptTime1, deptTime2, arriveName1,
            arriveAddress1, arriveCode1, arriveName2, arriveAddress2, arriveCode2, arriveTime1, arriveTime2,  carrierName1, carrierCode1, 
            carrierNameBangla1, carrierName2, carrierCode2, carrierNameBangla2, flightNum1, flightNum2, flightCode1, flightCode2, cabin1, 
            cabin2, class1, class2, baggage1, baggage2, travelTime1, travelTime2, transitTime, totalSeat, grossFare, availableSeat, 
            deactivated, createdAt, deleted)
            VALUES 
            ('$groupFareId','$segment','$deptCode1', '$deptName1', '$deptAddress1', '$deptCode2', '$deptName2', '$deptAddress2','$deptTime1',
            '$deptTime2','$arriveName1', '$arriveAddress1', '$arriveCode1','$arriveName2', '$arriveAddress2', '$arriveCode2','$arriveTime1',
            '$arriveTime2', '$carrierName1', '$carrierCode1', '$carrierNameBangla1','$carrierName2', '$carrierCode2', '$carrierNameBangla2',
            '$flightNum1','$flightNum2','$flightCode1','$flightCode2', '$cabin1','$cabin2','$class1','$class2', '$baggage1','$baggage2',
            '$travelTime1','$travelTime2','$transitTime', '$totalSeat', '$grossFare', '$totalSeat', 'false', '$createdAt', 'false')";

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
