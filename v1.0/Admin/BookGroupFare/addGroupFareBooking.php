<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $jsonData = json_decode(file_get_contents('php://input'), true);
    $flightPassengerData=$jsonData["flightPassengerData"];
    $passengerData=$jsonData["flightPassengerData"]["passenger"];
    $bookingData=$jsonData["bookingInfo"];
    // $saveBookingData=$jsonData["saveBooking"];
    // $saveBookingFlightData=$saveBookingData["flightData"];
    


    $agentId=$bookingData["agentId"];
    $staffId=$bookingData["staffId"];
    $deptFrom=$boookingData["from"];
    $gds=$boookingData["system"];
    $arriveTo=$boookingData["to"];
    $airlines=$boookingData["airlines"];
    $tripType=$boookingData["tripType"];
    $travelDate=$boookingData["travelDate"];
    $name=$boookingData["name"];
    $phone=$boookingData["phone"];
    $email=$boookingData["email"];
    $pax=$boookingData["pax"];
    $netCost=$boookingData["netcost"];
    $adultCostBase=$boookingData["adultcostbase"];
    $childCostBase=$boookingData["childcostbase"];
    $infantCostBase=$boookingData["infantcostbase"];
    $adultCount=$boookingData["adultcount"];
    $childCount=$boookingData["childcount"];
    $infantCount=$boookingData["infantcount"];
    $adultCostTax=$boookingData["adultcosttax"];
    $childCostTax=$boookingData["childcosttax"];
    $infantCostTax=$boookingData["infantcosttax"];
    $grossCost=$boookingData["grosscost"];
    $baseFare=$boookingData["basefare"];
    $Tax=$boookingData["tax"];
    $timeLimit=$boookingData["timelimit"];
    $searchId=$boookingData["SearchID"];
    $resultId=$boookingData["ResultID"];
    $journeyType=$boookingData["journeyType"];
    $ticketCoupon=$boookingData["coupon"];
    $adultBag=$boookingData["adultbag"];
    $childBag=$boookingData["childbag"];
    $infantBag=$boookingData["infantbag"];
    $refundable=$boookingData["refundable"];
    $platform=$boookingData["platform"];
    // $uid=$saveBookingFlightData["uId"];
    $currentDateTime = date('Y-m-d H:i:s');
    // $platform=$boookingData["platform"];
    // $platform=$boookingData["platform"];
    // $platform=$boookingData["platform"];
    // $platform=$boookingData["platform"];


    $sql="INSERT INTO group_fare_boooking
    (/*uid*/, agentId,
   staffId, email, phone, name, refundable,  tripType,
  journeyType, pax, adultBag, childBag, infantBag, adultCount, 
  childCount, infantCount,
   netCost, adultCostBase, childCostBase, infantCostBase,
  adultCostTax, childCostTax, infantCostTax, grossCost, 
  baseFare, Tax, 
  deptFrom, airlines, arriveTo, gds, 
  status, travelDate, bookedAt, timeLimit, 
  searchId, resultId,
  platform, ticketCoupon)
  VALUES (/*'$uid'*/, '$agentId', '$staffId', '$email', '$phone', '$name',
  '$refundable', '$tripType', '$journeyType', '$pax','$adultBag','$childBag','$infantBag',
  '$adultCount','$childCount','$infantCount','$netCost','$adultCostBase','$childCostBase',
  '$infantCostBase','$adultCostTax','$childCostTax','$infantCostTax','$grossCost','$baseFare',
  '$Tax','$deptFrom','$airlines','$arriveTo','$gds','Hold','$travelDate','$currentDateTime','$timeLimit','$searchId','$resultId','$platform','$ticketCoupon'
  )";




foreach($passengerData as $passenger)
{
    $type= $passenger["type"]; 
    $fName= $passenger["fName"]; 
    $lName= $passenger["lName"]; 
    $gender= $passenger["gender"]; 
    $dob= $passenger["dob"]; 
    $passNation= $passenger["passNation"]; 
    $passNo= $passenger["passNo"]; 
    $passEx= $passenger["passEx"]; 
    $
    $sql="INSERT INTO passengers 
    (bookingId, agentId, fName, lName, dob, type, passNation, passNo, passEx, 
    phone, email, gender, created)
    VALUES
    ('$agentId','$fName','$lName','$dob','$type','$passNation',
    '$passNo','$passEx','$phone', '$email', '$gender', '$currentDateTime')
    ";



}
    


}
else
{
    $response["status"] = "Failed";
    $response["message"] = "Wrong Request Method";
        
    echo json_encode($response);
}


?>