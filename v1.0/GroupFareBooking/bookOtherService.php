<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



function uploadImage($imagename, $acceptablesize, $cdnpath, $fileName, $name)
{           
            $tempname=$_FILES[$imagename]['tmp_name'];
            $filesize=$_FILES[$imagename]['size'];

            $validExt=['jpg', 'jpeg', 'png'];
            $fileExt= strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $imageType=substr($imagename,0,-1);
            $savedFileName=$name.$imageType.'.'.$fileExt;
            
            if (!file_exists($cdnpath)) {
                mkdir($cdnpath, 0777, true);
            }

            if(in_array($fileExt, $validExt))
            {
                if($filesize<$acceptablesize)
                {
                    move_uploaded_file($tempname, $cdnpath.$savedFileName);
                    return $savedFileName;
                }
                else
                {
                    echo json_encode(
                        array(
                            "status" => "error",
                            "message" => "Large Image Size"
                        )
                
                        );
                        return 'Not Found';
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
                    return 'Not Found';
            }
}




if($_SERVER["REQUEST_METHOD"] == "POST")
{   


    
    $jsonData=json_decode($_POST["requestedBody"], true);

    $airTicketData=$jsonData["airTicketData"];

    $agentId=$jsonData["agentId"];
    $agentLastAmount=$jsonData["lastBalance"];
    $travelType=$jsonData["travelType"];
    $from=$jsonData["from"];
    $to=$jsonData["to"];
    $pax=$jsonData["pax"];
    $gds=$jsonData["system"];
    $gdsPnr=$jsonData["gdsPnr"];
    $airlinesPnr=$jsonData["ailinesPnr"];
    $netCost=$jsonData["netCost"];
    $agentCost=$jsonData["agentCost"];
    $vendorCost=$jsonData["vendorCost"];
    $lossProfitAmount=$jsonData["lossProfitAmount"];



    $values="";
    $paxId ="";
    $sql = "SELECT paxId FROM passengers ORDER BY id DESC LIMIT 1";
    for($i=0; $i<$pax; $i++)
    {
        
        $result = $conn->query($sql);
        if ($result->num_rows > 0) 
        {
            $row = $result->fetch_assoc();
            $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
            $number= (int)$outputString + 1;
            $ticketId = "STP$number"; 								
        }
        else 
        {
            $ticketId ="STP1000";
        }
        
        $passInd="passport".$i;
        $visaInd="visa".$i;
        $passCopy= $_FILES[$passInd]["name"];
        $visaCopy= $_FILES[$visaInd]["name"];
        $passenger="traveler".$i;
        $name=$_POST[$passenger];

        $passCopy=uploadImage($passInd, 5000000, "../../asset/Passenger/$agentId/$bookingId/PassportCopy/", $passCopy, $name);
        $visaCopy=uploadImage($visaInd, 5000000, "../../asset/Passenger/$agentId/$bookingId/VisaCopy/", $visaCopy, $name);
        $values=$values."('$name','$paxId','$agentId','$bookingId','$passInd','$visaInd', '$currentDateTime'),";

    }

    $newValues=substr($values,0,-1);

    $sql="INSERT INTO passengers 
    (fName,paxId,agentId, bookingId, passportCopy, visaCopy, created)
    VALUES".$newValues;

    if($conn->query($sql))
    {
        $response["status"] = "Success";
        $response["message"] = "Booking Others Successfull";
        echo json_encode($response);
    
    }
    else
    {

    }

}
else
{
    $response["status"] = "Failed!";
    $response["message"] = "Invalid Request";
    echo json_encode($response);
}
?>