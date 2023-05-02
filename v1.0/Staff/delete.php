<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);
    
    $staffId = $_POST['staffId'];
    $agentId = $_POST['agentId'];
    $createdTime = date("Y-m-d H:i:s");
    $checker = mysqli_query($conn, "SELECT staffId, agentId FROM `staffList` WHERE staffId='$staffId' AND agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    if(!empty($checker)){
        $sql = "DELETE FROM `staffList` WHERE staffId='$staffId' AND agentId='$agentId'";
   

        if ($conn->query($sql) === TRUE) {
            $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                        VALUES ('$staffId','$agentId','Deleted','Agent Delete Staff','Agent','$createdTime')");
            $response['status']="success";
            $response['message']="Staff Deleted Successfully";                     
        }else{
            $response['status']="error";
            $response['message']="Deleted Failed Successfully";
        }
    }else{
        $response['status']="error";
        $response['message']="Staff Not Found";
    }
   
         
    echo json_encode($response);
    
}



?>