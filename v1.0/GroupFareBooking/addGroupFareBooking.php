<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");





function uploadImage($imagename, $acceptablesize, $cdnpath, $fileName)
{           
            $tempname=$_FILES[$imagename]['tmp_name'];
            $filesize=$_FILES[$imagename]['size'];

            $validExt=['jpg', 'jpeg', 'png'];
            $fileExt= strtolower(pathinfo($fileName, PATHINFO_EXTENSION));


            if(in_array($fileExt, $validExt))
            {
                if($filesize<$acceptablesize)
                {
                    move_uploaded_file($tempname, $cdnpath.$fileName);
                    return true;
                }
                else
                {
                    echo json_encode(
                        array(
                            "status" => "error",
                            "message" => "Large Image Size"
                        )
                
                        );
                        return false;
                }
            }
            else
            {
                echo json_encode(
                    array(
                        "status" => "error",
                        "message" => "Invalid Extension"
                    )
                    );
                    return false;
            }
}


if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $jsonData = json_decode(file_get_contents('php://input'), true);
    // $flightPassengerData=$jsonData["groupFarePassengerData"];
    // $bookingData=$jsonData["groupFareDetails"];// $saveBookingData=$jsonData["saveBooking"];
    // $saveBookingFlightData=$saveBookingData["flightData"];
    // $passengerData=$jsonData["passengers"];
    echo json_encode($jsonData);
    $flightData=$jsonData["flightData"];
    $passport=$jsonData["passportImg"];
    $visa=$jsonData["visaImg"];
    


    $agentId=$jsonData["agentId"];
    $name=$jsonData["name"];
    $phone=$jsonData["phone"];
    $email=$jsonData["email"];

    $segment=$flightData["segment"];
    
    $dept1=$flightData["dept1"]["name"];
    $arrive1=$flightData["arrive1"]["name"];
    $dept2=$flightData["dept2"]["name"];
    $arrive2=$flightData["arrive2"]["name"];
    $carrierName1=$flightData["carrierName1"]["name"];
    $carrierName2=$flightData["carrierName2"]["name"];
    $flightNum1=$flightData["flightNum1"];
    $flightNum2=$flightData["flightNum2"];
    $flightCode1=$flightData["flightCode1"];
    $flightCode2=$flightData["flightCode2"];
    $cabin1=$flightData["cabin1"];
    $cabin2=$flightData["cabin2"];
    $class1=$flightData["cabin1"];
    $class2=$flightData["cabin2"];
    $baggage1=$flightData["baggage1"];
    $baggage2=$flightData["baggage2"];
    $travelTime1=$flightData["travelTime1"];
    $travelTime2=$flightData["travelTime2"];
    $transitTime=$flightData["transitTime"];
    $netCost=$flightData["netCost"];
    $pax=$flightData["pax"];
    
    $currentDateTime = date('Y-m-d H:i:s');
    // $adultCount=$bookingData["adultcount"];
    // $childCount=$bookingData["childcount"];
    // $infantCount=$bookingData["infantcount"];
    // $adultCostTax=$bookingData["adultcosttax"];
    // $childCostTax=$bookingData["childcosttax"];
    // $infantCostTax=$bookingData["infantcosttax"];
    // $grossCost=$bookingData["grosscost"];
    // $baseFare=$bookingData["basefare"];
    // $Tax=$bookingData["tax"];
    // $timeLimit=$bookingData["timelimit"];
    // $searchId=$bookingData["SearchID"];
    // $resultId=$bookingData["ResultID"];
    // $journeyType=$bookingData["journeyType"];
    // $ticketCoupon=$bookingData["coupon"];
    // $adultBag=$bookingData["adultbag"];
    // $childBag=$bookingData["childbag"];
    // $infantBag=$bookingData["infantbag"];
    // $refundable=$bookingData["refundable"];
    // $platform=$bookingData["platform"];
    $arrival= (isset($dept2))? $dept2:$dept1;
    $airlines= $carrierName1." and ".$carrierName2;

    if($segment===1)
    {
        
        
    }
    else if ($segment===2)
    {

    }



    
    // $uid=$saveBookingFlightData["uId"];
    
    // $platform=$bookingData["platform"];
    // $platform=$bookingData["platform"];
    // $platform=$bookingData["platform"];
    // $platform=$bookingData["platform"];


    $sql="
    INSERT booking
    (agentId, email, phone, name, pax, deptFrom, airlines, arriveTo, gds, status, travelDate, 
    bookedAt, platform, netCost )
    VALUES ('$agentId',  '$email', '$phone', '$name', '$pax', '$dept1', '$airlines', '$arrival', '$segment', 'Hold', '$travelTime1', '$currentDateTime',
    'GF', '$netCost')";


    $bookingId="";
    $message="";
    $book=false;
    if($conn->query($sql))
    {
        $result = $conn->query("SELECT bookingId FROM booking ORDER BY id DESC LIMIT 1");
        $row = $result->fetch_assoc();
        $bookingId = $row['bookingId'];
        $book=true;
    }
    else
    {
            $book=false;
    }
    
    $values="";
    $count=0;
            foreach($passport as $pass)
            {
                // $type= $passenger["type"]; 
                // $fName= $passenger["fName"]; 
                // $lName= $passenger["lName"]; 
                // $gender= $passenger["gender"]; 
                // $dob= $passenger["dob"]; 
                // $passNation= $passenger["passNation"]; 
                // $passNo= $passenger["passNo"]; 
                // $passEx= $passenger["passEx"];
                $passCopy= $pass[$count][$_FILES["image"]["name"]];
                $visaCopy= $visa[$count][$_FILES["image"]["name"]];

                uploadImage('image', 5000000, "../../asset/Passenger/$agentId/$bookingId/PassportCopy/", $passCopy);
                uploadImage('image', 5000000, "../../asset/Passenger/$agentId/$bookingId/VisaCopy/", $visaCopy);
                $values=$values."('$bookingId','$passCopy','$visaCopy'),";

            }

            $newValues=substr($values,0,-1);

            $sql="INSERT INTO passengers 
            (bookingId, passportCopy, visaCopy)
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