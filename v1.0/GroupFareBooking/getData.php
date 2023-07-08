<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if(array_key_exists("all", $_GET))
{   
    $sql="SELECT p.paxId, p.bookingId, p.agentId, b.status, p.fName, b.platform, p.lName, p.gender, p.dob, p.passNo, p.passEx, b.platform 
    FROM passengers p
    JOIN booking b ON p.bookingId=b.bookingId
    WHERE b.bookingType='group fare'
    ";
    
    $response=$conn->query($sql)->fetch_all(MYSQLI_ASSOC);


    if(!empty($response))
    {
        echo json_encode($response);
    }
    else
    {
        $response["status"] = "Failed";
        $response["message"] = "Data Not Found";
        
        echo json_encode($response);
    }

}else if(array_key_exists("bookingId", $_GET) ){
       
    $bookingId = $_GET["bookingId"];
       
       $sql1="SELECT *
       FROM passengers p 
       WHERE p.bookingId='$bookingId'
        ";
        

        $passengerData=$conn->query($sql1)->fetch_all(MYSQLI_ASSOC);
        $response["passengerData"]=$passengerData;

        $sql2="SELECT *
        FROM groupfare  
        WHERE groupFareId=(
        SELECT groupFareId 
        FROM booking b   
        WHERE b.bookingId='$bookingId'
        )
        ";

        $groupFareData=$conn->query($sql2)->fetch_all(MYSQLI_ASSOC);
        $response["groupFareData"]=$groupFareData;



    
        if(!empty($response))
        {
           echo json_encode($response);
        }
        else
        {
            $response["status"] = "Failed";
            $response["message"] = "Data Not Found";
            
            echo json_encode($response);
        }
    
    
}
    

?>