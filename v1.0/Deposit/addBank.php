<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);

        $agentId  = $_POST['agentId'];
        $accname = $_POST['accname'];
        $bankname  = $_POST['bankname'];
        $accno  = $_POST['accno'];
        $branch  = $_POST['branch'];
        $swift  = $_POST['swift'];      
        $routing = $_POST['routing'];
        $address = $_POST['address'];

        $Date = date("Y-m-d H:i:s");
  
        $checkUser="SELECT * FROM bank_accounts WHERE bankname='$bankname' AND accno='$accno' AND agentId='$agentId'";
        $result=mysqli_query($conn,$checkUser);

        if(mysqli_num_rows($result)> 0){ 
            $response['status']="error";
  	        $response['message']="Already Exists"; 
            echo json_encode($response);      
        }else{
            $sql = "INSERT INTO `bank_accounts`(
                `agentId`,
                `accname`,
                `bankname`,
                `accno`,
                `branch`,
                `swift`,
                `address`,
                `routing`,
                `createdAt`
              )
            VALUES(
                '$agentId',
                '$accname',
                '$bankname',
                '$accno',
                '$branch',               
                '$swift',
                '$address',
                '$routing',               
                '$Date'
            )";

            if ($conn->query($sql) === TRUE) {               
                $response['status']="success";
                $response['message']="Bank Account Added Successful";

            }else{
                $response['status']="success";
                $response['message']="Bank Account Added Successful";
            } 
            echo json_encode($response);                                   
        }
        
}

?>