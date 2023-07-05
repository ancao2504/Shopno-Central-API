<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $id=$_POST["groupfareid"];
    $sql="DELETE FROM groupfare WHERE groupFareId='$id'";
    
    if(empty($id))
    {
        $response["status"] = "Error";
        $response["message"] = "groupfareid Is Missing";
        echo json_encode($response);
        exit();
    }
    else if($conn->query($sql))
    {
        $response["status"] = "Success";
        $response["message"] = $id."Deleted";
        
        echo json_encode($response);
    }
    else
    {
        $response["status"] = "Failed";
        $response["message"] = "Delete Failed";
        
        echo json_encode($response);
    }
}
else
{
    $response["status"] = "Failed";
    $response["message"] = "Wrong Request Method";
    
    echo json_encode($response);
}

$conn->close();

?>