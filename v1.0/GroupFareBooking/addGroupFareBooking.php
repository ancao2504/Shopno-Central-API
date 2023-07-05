<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");








if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $jsonData = json_decode(file_get_contents('php://input'), true);
    // $flightPassengerData=$jsonData["groupFarePassengerData"];
    $passengerData=$jsonData["passengers"];
    // $bookingData=$jsonData["groupFareDetails"];
    $flightData=$jsonData["flightData"];
    // $saveBookingData=$jsonData["saveBooking"];
    // $saveBookingFlightData=$saveBookingData["flightData"];
    


    $agentId=$jsonData["agentId"];
    $name=$jsonData["name"];
    $phone=$jsonData["phone"];
    $email=$jsonData["email"];

    $segment=$flightData["segment"];
    $gds=$flightData["system"];
    $dept1=$flightData["dept1"];
    $arrive1=$flightData["arrive1"];
    $dept2=$flightData["dept2"];
    $arrive2=$flightData["arrive2"];

    if($segment===1)
    {
        
        
    }
    else if ($segment===2)
    {

    }



    $airlines=$bookingData["airlines"];
    $tripType=$bookingData["tripType"];
    $travelDate=$bookingData["travelDate"];
    $pax=$bookingData["pax"];
    $netCost=$bookingData["netcost"];
    $adultCostBase=$bookingData["adultcostbase"];
    $childCostBase=$bookingData["childcostbase"];
    $infantCostBase=$bookingData["infantcostbase"];
    $adultCount=$bookingData["adultcount"];
    $childCount=$bookingData["childcount"];
    $infantCount=$bookingData["infantcount"];
    $adultCostTax=$bookingData["adultcosttax"];
    $childCostTax=$bookingData["childcosttax"];
    $infantCostTax=$bookingData["infantcosttax"];
    $grossCost=$bookingData["grosscost"];
    $baseFare=$bookingData["basefare"];
    $Tax=$bookingData["tax"];
    $timeLimit=$bookingData["timelimit"];
    $searchId=$bookingData["SearchID"];
    $resultId=$bookingData["ResultID"];
    $journeyType=$bookingData["journeyType"];
    $ticketCoupon=$bookingData["coupon"];
    $adultBag=$bookingData["adultbag"];
    $childBag=$bookingData["childbag"];
    $infantBag=$bookingData["infantbag"];
    $refundable=$bookingData["refundable"];
    $platform=$bookingData["platform"];
    // $uid=$saveBookingFlightData["uId"];
    $currentDateTime = date('Y-m-d H:i:s');
    // $platform=$bookingData["platform"];
    // $platform=$bookingData["platform"];
    // $platform=$bookingData["platform"];
    // $platform=$bookingData["platform"];


    $sql="
    INSERT INTO group_fare_booking
    (agentId, staffId, email, phone, name, refundable,  tripType,
    journeyType, pax, adultBag, childBag, infantBag, adultCount, 
    childCount, infantCount, netCost, adultCostBase, childCostBase, 
    infantCostBase, adultCostTax, childCostTax, infantCostTax, grossCost, 
    baseFare, Tax, deptFrom, airlines, arriveTo, gds, status, travelDate, 
    bookedAt, timeLimit, searchId, resultId, platform, ticketCoupon)
    VALUES ('$agentId', '$staffId', '$email', '$phone', '$name','$refundable',
    '$tripType', '$journeyType', '$pax','$adultBag','$childBag','$infantBag','$adultCount',
    '$childCount','$infantCount','$netCost','$adultCostBase','$childCostBase','$infantCostBase',
    '$adultCostTax','$childCostTax','$infantCostTax','$grossCost','$baseFare','$Tax','$deptFrom',
    '$airlines','$arriveTo','$gds','Purchase','$travelDate','$currentDateTime','$timeLimit','$searchId',
    '$resultId','$platform','$ticketCoupon')";


    $bookingId="";
    $message="";
    $book=false;
    if($conn->query($sql))
    {
        $result = $conn->query("SELECT bookingId FROM group_fare_booking ORDER BY id DESC LIMIT 1");
        $row = $result->fetch_assoc();
        $bookingId = $row['bookingId'];
        $book=true;
    }
    else
    {
            $book=false;
    }
    
    $values="";
           
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


        
                $values=$values."('$bookingId','$agentId','$fName','$lName','$dob','$type','
                $passNation','$passNo','$passEx','$phone', '$email', '$gender', '$currentDateTime'),";

            }

            $newValues=substr($values,0,-1);

            $sql="INSERT INTO passengers 
            (bookingId, agentId, fName, lName, dob, type, passNation, passNo, passEx, 
            phone, email, gender, created)
            VALUES".$newValues;
            
            if($conn->query($sql))
            {
                if($book)
                {
                    $response["status"] = "Success";
                    $response["message"] = "Booking and Passenger Added Successfully";
                    
                }
                else
                {
                    $response["status"] = "Failed";
                    $response["message"] = "Passenger Add Done But Boooking Failed";
                }
                
            }
            else
            {
                if($book)
                {
                    $response["status"] = "Failed";
                    $response["message"] = "Booking Done But Passenger Add Failed";
                }
                else
                {
                    $response["status"] = "Failed";
                    $response["message"] = "Booking and Passenger Add Failed";
                }
            }        

            echo json_encode($response);
            
}
else
{
    $response["status"] = "Failed";
    $response["message"] = "Wrong Request Method";
        
    echo json_encode($response);
}


?>