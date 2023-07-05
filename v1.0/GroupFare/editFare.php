<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if($_SERVER["REQUEST_METHOD"] == "POST")
{   $jsonData = json_decode(file_get_contents('php://input'), true);
    
    $id=$jsonData["groupfareid"];
    $segment=$jsonData["segment"];
    $adtBaseFare=$jsonData["AdultBaseFare"];
    $totalSeat=$jsonData["TotalSeat"];
    $transitTime=$jsonData["TransitTime"];

    if(empty($id))
    {
        $response["status"] = "Error";
        $response["message"] = "groupfareid Is Missing";
        echo json_encode($response);
        exit();
    }
    if($segment==1)
    {
        $deptFrom=$jsonData[0]["DepartureFrom"];
        $deptTime=$jsonData[0]["DepartureTime"];
        $arriveTo=$jsonData[0]["ArriveTo"];
        $arriveTime=$jsonData[0]["ArrivalTime"];
        $carrierName=$jsonData[0]["CarrierName"];
        $flightNum=$jsonData[0]["FlightNumber"];
        $flightCode=$jsonData[0]["FlightCode"];
        $cabin=$jsonData[0]["Cabin"];
        $class=$jsonData[0]["Class"];
        $baggage=$jsonData[0]["Baggage"];
        $travelTime=$jsonData[0]["TravelTime"];
       
        
        $sql="UPDATE groupfare 
        SET segment='$segment', dept1='$deptFrom', deptTime1='$deptTime',  
        arrive1='$arriveTo',  arriveTime1='$arriveTime',  carrierName1='$carrierName',  
        flightNum1='$flightNum',  flightCode1='$flightCode',
        cabin1='$cabin',  
        class1='$class',  baggage1='$baggage',  travelTime1='$travelTime', 
        transitTime='$transitTime', totalSeat='$totalSeat',
        adtBaseFare='$adtBaseFare'
        WHERE groupFareId='$id'";


        
    }
    else if($segment==2)
    {
        $deptFrom1=$jsonData[0]["DepartureFrom"];
        $deptTime1=$jsonData[0]["DepartureTime"];
        $arriveTo1=$jsonData[0]["ArriveTo"];
        $arriveTime1=$jsonData[0]["ArrivalTime"];
        $carrierName1=$jsonData[0]["CarrierName"];
        $flightNum1=$jsonData[0]["FlightNumber"];
        $flightCode1=$jsonData[0]["FlightCode"];
        $cabin1=$jsonData[0]["Cabin"];
        $class1=$jsonData[0]["Class"];
        $baggage1=$jsonData[0]["Baggage"];
        $travelTime1=$jsonData[0]["TravelTime"];
        

        $deptFrom2=$jsonData[1]["DepartureFrom"];
        $deptTime2=$jsonData[1]["DepartureTime"];
        $arriveTo2=$jsonData[1]["ArriveTo"];
        $arriveTime2=$jsonData[1]["ArrivalTime"];
        $carrierName2=$jsonData[1]["CarrierName"];
        $flightNum2=$jsonData[1]["FlightNumber"];
        $flightCode2=$jsonData[1]["FlightCode"];
        $cabin2=$jsonData[1]["Cabin"];
        $class2=$jsonData[1]["Class"];
        $baggage2=$jsonData[1]["Baggage"];
        $travelTime2=$jsonData[1]["TravelTime"];
       
        
        
        
        $sql="UPDATE groupfare SET
        segment='$segment', dept1='$deptFrom1', dept2='$deptFrom2', deptTime1='$deptTime1', deptTime2='$deptTime2', 
        arrive1='$arriveTo1', arrive2='$arriveTo2', arriveTime1='$arriveTime1', arriveTime2='$arriveTime2', 
        carrierName1='$carrierName1', carrierName2='$carrierName2', 
        flightNum1='$flightNum1', flightNum2='$flightNum2', flightCode1='$flightCode1', flightCode2='$flightCode2', 
        cabin1='$cabin1', cabin2='$cabin2', class1='$class1', class2='$class2', baggage1='$baggage1', 
        baggage2='$baggage2', travelTime1='$travelTime1', travelTime2='$travelTime2', transitTime='$transitTime', totalSeat='$totalSeat',
        adtBaseFare='$adtBaseFare'
        WHERE groupFareId='$id'";
    }

    if ($conn->query($sql)) {
        
        $response["status"] = "Success";
        $response["message"] = $id." Data Updated Successfully";
    }
    else
    {
        $response["status"] = "Failed";
        $response["message"] = "Query Failed";
    }
    
    echo json_encode($response);
    

}
else
{
    $response["status"] = "Failed";
    $response["message"] = "Wrong Request Method";
    
    echo json_encode($response);
}

$conn->close();


?>