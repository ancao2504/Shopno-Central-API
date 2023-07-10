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
    $currentDateTime=date("Y-m-d H:i:s");
    
    $serviceType=$jsonData["serviceType"];
    $agentId=$jsonData["agentId"];
    $agentLastAmount=$jsonData["lastBalance"];
    $travelType=$jsonData["travelType"];
    $from=$jsonData["from"];
    $to=$jsonData["to"];
    $pax=$jsonData["pax"];
    $gds=$jsonData["system"];
    $gdsPnr=$jsonData["gdsPnr"];
    $airlinesPnr=$jsonData["airlinesPnr"];
    $netCost=$jsonData["netCost"];
    $agentCost=$jsonData["agentCost"];
    $vendorCost=$jsonData["vendorCost"];
    $lossProfitAmount=$jsonData["lossProfitAmount"];
    $travelDate=$jsonData["travelDate"];
    $airlines=$jsonData["airlines"];


    if($agentLastAmount>$agentCost)
    {   


        // $conn->query(U)
        $sql="SELECT othersId FROM others ORDER BY id DESC LIMIT 1";
        $result=$conn->query($sql)->fetch_assoc();
        $othersId="";
        if(!isset($result['othersId']))
        {
            $othersId='STO1000';
        }
        else
        {
            $othersId=$result['othersId'];
        }

        $attachment= $_FILES['attachment']["name"];
        $attachmentName=uploadImage($passInd, 5000000, "../../asset/Passenger/$agentId/$othersId/attachment/", $attachment, $othersId);

        $sql="INSERT INTO others (othersId, agentId, travelType, deptFrom, arriveTo, travelDate, airlines, pax, gds, gdsPNR, airlinesPNR
        netCost, agentCost, vendorCost, lossProfitAmount, createdAt,createdBy, serviceType, attachment)
        VALUES
        ( '$othersId', '$agentId', '$travelType', '$from', '$to', '$travelDate', '$airlines', '$pax', '$gds', '$gdsPnr', '$airlinesPnr', 
        '$netCost', '$agentCost', '$vendorCost', '$lossProfitAmount',  '$currentDateTime', '$serviceType', '$attachment'
        )";
        
        if($conn->query($sql))
        {
            $values="";
            $paxId ="";
            $sql = "SELECT passengerId FROM passengers ORDER BY id DESC LIMIT 1";
            for($i=0; $i<$pax; $i++)
            {
                
                $result = $conn->query($sql);
                if ($result->num_rows > 0) 
                {
                    $row = $result->fetch_assoc();
                    $outputString = preg_replace('/[^0-9]/', '', $row["passengerId"]); 
                    $number= (int)$outputString + 1;
                    $passengerId = "STP$number"; 								
                }
                else 
                {
                    $passengerId ="STP1000";
                }
                
                $passInd="passport".$i;
                $visaInd="visa".$i;
                $passCopy= $_FILES[$passInd]["name"];
                $visaCopy= $_FILES[$visaInd]["name"];
                $passenger="traveler".$i;
                $name=$_POST[$passenger];

                $passCopy=uploadImage($passInd, 5000000, "../../asset/Passenger/$agentId/$othersId/PassportCopy/", $passCopy, $name);
                $visaCopy=uploadImage($visaInd, 5000000, "../../asset/Passenger/$agentId/$othersId/VisaCopy/", $visaCopy, $name);
                $values=$values."('$name','$paxId','$agentId','$othersId','$passInd','$visaInd', '$currentDateTime'),";

            }

            $newValues=substr($values,0,-1);

            $sql="INSERT INTO passengers 
            (fName,paxId,agentId, bookingId, passportCopy, visaCopy, created)
            VALUES".$newValues;

            if($conn->query($sql))
            {
                $response["status"] = "Success";
                $response["message"] = "Others Added Successfull";
                echo json_encode($response);
            
            }
            else
            {
                $response["status"] = "error";
                $response["message"] = "Passengers Added Failed";
                echo json_encode($response);
            
            }


        }
        else
        {
            $response["status"] = "error";
            $response["message"] = "Others Added Failed";
            echo json_encode($response);
        }

    }
    else
    {
        $response["status"] = "error";
        $response["message"] = "Insufficient Balance";
        echo json_encode($response);
    }
    
    

}
else
{
    $response["status"] = "error";
    $response["message"] = "Invalid Request";
    echo json_encode($response);
}
?>