<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
        $_POST = json_decode(file_get_contents('php://input'), true);


        $Activity_Id ="";
        $sql1 = "SELECT * FROM activitylog ORDER BY activityId DESC LIMIT 1";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["activityId"]); 
                $number= (int)$outputString + 1;
                $Search_Id = "STAC$number"; 								
            }
        } else {
                $Search_Id ="STAC1000";
        }

        $Agent_Id = $_POST['agentId'];
        $referenceId = $_POST['referenceId'];
        $empName = $_POST['empName'];
        $empId = $_POST['empId'];
        $activity = $_POST['activityDetails'];

        $searchTime = date('Y-m-d H:i:s');

            $sql = "INSERT INTO `activitylog`(
                `activityId`,
                `agentId`,
                `reference`,
                `name`,
                `activity`,             
                `searchTime`)
            VALUES(
                '$Search_Id',
                '$Agent_Id',
                '$empName',
                '$empId', 
                '$activity',              
                '$searchTime'
            )";

            if ($conn->query($sql) === TRUE) {
                $response['status']="success";
  	            $response['message']="Saved Successfully";          
            } else {
                $response['status']="error";
  	            $response['message']="Saving failed";
            }

            echo json_encode($response);
        }
        
    
?>