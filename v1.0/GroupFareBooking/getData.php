<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if(array_key_exists("all", $_GET))
{   
    $sql="SELECT gf_booking.*, agent.company 
    FROM gf_booking 
    LEFT JOIN agent ON gf_booking.agentId=agent.agentId 
    ORDER BY id DESC";
    
    $response=$conn->query($sql)->fetch_all(MYSQLI_ASSOC);


    if(!empty($response))
    {
        echo json_encode($response);
    }
    else
    {
        $response["status"] = "error";
        $response["message"] = "Data Not Found";
        
        echo json_encode($response);
    }

}else if(array_key_exists("bookingId", $_GET) && array_key_exists("gfId", $_GET) ){
       
    $bookingId = $_GET["bookingId"];
    $gfId= $_GET["gfId"];

       $sql0="SELECT * FROM gf_booking WHERE bookingId = '$bookingId' AND groupFareId = '$gfId'";
       $sql1="SELECT * FROM groupfare WHERE groupFareId = '$gfId'";
      
       $bookingInfo=$conn->query($sql0)->fetch_assoc();
       $groupFareInfo=$conn->query($sql1)->fetch_assoc();
       
       if(!empty($bookingInfo) && !empty($groupFareInfo))
        {   
            $response["bookingInfo"]=$bookingInfo;
            $response["groupFareInfo"]=$groupFareInfo;

           echo json_encode($response);
        }
        else
        {
            $response["status"] = "error";
            $response["message"] = "Data Not Found";
            echo json_encode($response);
        }
    
    
}
else if(array_key_exists("gfId", $_GET) ){
       
    $gfId = $_GET["gfId"];
       
       $sql1="SELECT * FROM gf_booking WHERE groupFareId = '$gfId'";
       
       $response=$conn->query($sql1)->fetch_all(MYSQLI_ASSOC);
       
       if(!empty($response))
        {
           echo json_encode($response);
        }
        else
        {
            $response["status"] = "error";
            $response["message"] = "Data Not Found";
            echo json_encode($response);
        }
    
    
}
