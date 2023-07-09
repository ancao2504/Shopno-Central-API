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

            
            if (!file_exists($cdnpath)) {
                mkdir($cdnpath, 0777, true);
            }

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


    // echo $_POST["requestedBody"];
    $jsonData = json_decode($_POST["requestedBody"], true);
    // echo json_encode($jsonData);
    $flightData=$jsonData["flightData"];
    
    // $passengersNames=json_decode($_POST["travelerNames"], true);
    $gfId=$jsonData["groupFareId"];
    $agentId=$jsonData["agentId"];
    $name=$jsonData["name"];
    $phone=$jsonData["phone"];
    $email=$jsonData["email"];
    $platform=$jsonData["platform"];
    $segment=$flightData["segment"];
    
    $dept1=$flightData["dept1"];
    $arrive1=$flightData["arrive1"];
    $dept2=$flightData["dept2"];
    $arrive2=$flightData["arrive2"];
    $carrierName1=$flightData["carrierName1"];
    $carrierName2=$flightData["carrierName2"];
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
    
    $arrival= (isset($dept2))? $dept2:$dept1;
    $airlines= $carrierName1." and ".$carrierName2;

   




    $bookingId ="";
        $sql = "SELECT * FROM booking ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                
                $outputString = preg_replace('/[^0-9]/', '', $row["bookingId"]); 
                
                $number= (int)$outputString + 1;
                
                $bookingId = "STB$number"; 								
            }
        } else {
            $bookingId ="STB1000";
        }

        
    $sql="
    INSERT booking
    (bookingId, agentId, email, phone, name, pax, deptFrom, airlines, arriveTo, gds, status, travelDate, 
    bookedAt, platform, netCost, bookingType, groupFareId)
    VALUES ('$bookingId','$agentId',  '$email', '$phone', '$name', '$pax', '$dept1', '$airlines', '$arrival', '$segment', 'Hold', '$travelTime1', '$currentDateTime',
    '$platform', '$netCost', 'groupfare', '$gfId')";

        
    
    $message="";
    $book=($conn->query($sql))?true:false;
    
    $sql="UPDATE groupfare SET 
        availableSeat=availableSeat-'$pax' 
        WHERE groupFareId='$gfId'";

   
    if($conn->query($sql))
    {
        
    }
    else
    {   $response["status"]= "Failed";
        $response["message"] = "Available Seat Update Failed";
        echo json_encode($response);
    }


    

    $values="";
            for($i=0; $i<$pax; $i++)
            {
                
                $paxId ="";
                $sql = "SELECT * FROM passengers ORDER BY id DESC LIMIT 1";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        
                        $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                        
                        $number= (int)$outputString + 1;
                        
                        $paxId = "STP$number"; 								
                    }
                } else {
                    $paxId ="STP1000";
                }
                $passInd="passport".$i;
                $visaInd="visa".$i;
                $passCopy= $_FILES[$passInd]["name"];
                $visaCopy= $_FILES[$visaInd]["name"];
                $passenger="travelername".$i;
                $name=$_POST[$passenger];

                uploadImage($passInd, 5000000, "../../asset/Passenger/$agentId/$bookingId/PassportCopy/", $passCopy);
                uploadImage($visaInd, 5000000, "../../asset/Passenger/$agentId/$bookingId/VisaCopy/", $visaCopy);
                $values=$values."('$name','$paxId','$agentId','$bookingId','$passCopy','$visaCopy', '$currentDateTime'),";

            }

            $newValues=substr($values,0,-1);

            $sql="INSERT INTO passengers 
            (fName,paxId,agentId, bookingId, passportCopy, visaCopy, created)
            VALUES".$newValues;
            
            if($conn->query($sql))
            {
                if($book)
                {
                    $response["status"] = "Success";
                    $response["message"] = "Booking and Passenger Added Successfully";
                    $response["bookingId"]=$bookingId;
                    
                    
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