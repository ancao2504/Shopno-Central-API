<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);


        $paxId ="";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                    $number= (int)$outputString + 1;
                    $paxId = "STP$number"; 								
                }
            } else {
                    $paxId ="STP1000";
            }
        
        $agentId = $_POST['agentId'];  
        $fname = strtoupper($_POST['fname']);
        $lname = strtoupper($_POST['lname']);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $type = $_POST['type'];
        $nationality = $_POST['nationality'];
        $passportno = strtoupper($_POST['passportno']);
        $passexpireDate = $_POST['passexpireDate'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $sql = "INSERT INTO `passengers`(
                        `paxId`,
                        `agentId`,
                        `fName`,
                        `lName`,
                        `dob`,
                        `gender`,
                        `type`,
                        `passNation`,
                        `passNo`,
                        `passEx`,
                        `phone`,
                        `email`
                )
                VALUES('$paxId','$agentId','$fname','$lname','$dob','$gender','$type','$nationality',
                    '$passportno','$passexpireDate',' $phone','$email')";

        if ($conn->query($sql) === TRUE) {
            $response['status']="success";
            $response['message']="Traveler Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Traveler Added Failed";
        }
    
        
        echo json_encode($response);
    }



?>