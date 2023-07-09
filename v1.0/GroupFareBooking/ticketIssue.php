<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if($_SERVER["REQUEST_METHOD"] == 'POST')
{
    $jsonData=json_decode(file_get_contents("php://input"), true);
    
    $passengers=$jsonData['passengerData'];

    $agentId=$jsonData["agentId"];
    $bookingId=$jsonData["bookingId"];
    $netCost=$jsonData["netCost"];
    $actionBy=$jsonData["ticketedBy"];
    $dateTime= date("Y-m-d H:i:s");
    $platform=$jsonData["platform"];
    
    $airlinesPNR="HARD_CODED_PNR";
    $transactionId="HARD_CODED_TRANSACTIONID";
    $paidAmount="HARD_CODED_PA";
    $paidDate=$dateTime;


    $agentLedge=$conn->query("SELECT lastAmount FROM agent_ledger WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $lastAmount=$agentLedge["lastAmount"];
    $newLastAmount=NULL;
    
    $response["status"]="N/A";
    $response["message"]="N/A";
    // echo json_encode($jsonData);
    if($netCost<=$lastAmount)
    {   
        $newLastAmount=$lastAmount-$netCost;
        $values="";
        $ticketId ="";
        $sql = "SELECT * FROM ticketed ORDER BY id DESC LIMIT 1";

        foreach($passengers as $p)
        {
            
            $result = $conn->query($sql);
            if ($result->num_rows > 0) 
            {
                $row = $result->fetch_assoc();
                $outputString = preg_replace('/[^0-9]/', '', $row["ticketId"]); 
                $number= (int)$outputString + 1;
                $ticketId = "STT$number"; 								
            }
            else 
            {
                $ticketId ="STT1000";
            }

            $passengerName=$p["fName"];
            $passportCopy=$p["passportCopy"];
            $passengerName=$p["passportCopy"];

            $values=$values.
            "('$agentId', '$bookingId', '$ticketId', '$passengerName', '$passportCopy',                 
            '$dateTime', '$actionBy'),";
            /*$gds, $airlinesPnr, $gdsPnr, $ticketno,*/
           
            
        }

        $newValues=substr($values, 0, -1);
        
        $sql="INSERT INTO ticketed (agentId, bookingId, ticketId, passengerName, passportCopy, ticketedAt, ticketedBy)
        VALUES".$newValues;
         /*gds, airlinesPnr, gdsPnr, ticketno,*/ 

        if($conn->query($sql))
        {
            $details=$netCost ."Tk Group Fare tickted by $actionBy";

            $sql="INSERT INTO agent_ledger
            (agentId, purchase, lastAmount, transactionId, details,
            reference, platform, actionBy, createdAt)
            VALUES('$agentId','$netCost','$newLastAmount','$transactionId',
            '$details', '$transactionId', '$platform', '$actionBy', 
            '$dateTime')";

            if($conn->query($sql))
            {
                $sql="UPDATE booking SET
                airlinesPNR='$airlinesPNR',
                paidAmount= '$paidAmount',
                paidDate='$paidDate',
                status='Ticketed'
                WHERE bookingId ='$bookingId'";

                if($conn->query($sql))
                {
                    $response["status"]="Success!";
                    $response["message"]="Ticket Issue Successfull.";
                }
                else
                {
                    $response["status"]="Failed!";
                    $response["message"]="Booking Data Add Error.";
                }
            }
            else
            {
                $response["status"]="Failed!";
                $response["message"]="Agent Ledger Add Error.";
            }
        }
        else
        {
            $response["status"]="Failed!";
            $response["message"]="Not Ticketed.";
        }
    }
    else
    {
        $response["status"]="Failed!";
        $response["message"]="Not Enough Balance Available.";
    }

    echo json_encode($response);
    
}




?>