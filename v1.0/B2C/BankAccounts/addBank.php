<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);

        $userId  = $_POST['userId'];
        $accname = $_POST['accname'];
        $bankname  = $_POST['bankname'];
        $accno  = $_POST['accno'];
        $branch  = $_POST['branch'];
        $swift  = $_POST['swift'];      
        $routing = $_POST['routing'];
        $address = $_POST['address'];

        $Date = date("Y-m-d H:i:s");
  
        $checkUser="SELECT * FROM bank_accounts WHERE bankname='$bankname' AND accno='$accno' AND userId='$userId'";
        $result=mysqli_query($conn,$checkUser);

        if(mysqli_num_rows($result)> 0){ 
            $response['status']="error";
  	        $response['message']="Already Exists"; 
            echo json_encode($response);      
        }else{
            $sql = "INSERT INTO `bank_accounts`(
                `userId`,
                `accname`,
                `bankname`,
                `accno`,
                `branch`,
                `swift`,
                `address`,
                `routing`,
                `platform`,
                `createdAt`
              )
            VALUES(
                '$userId',
                '$accname',
                '$bankname',
                '$accno',
                '$branch',               
                '$swift',
                '$address',
                '$routing',
                'B2C',              
                '$Date'
            )";

            if ($conn->query($sql) === TRUE) {               
                $response['status']="success";
                $response['message']="Bank Account Added Successful";

            }else{
                $response['status']="error";
                $response['message']="Query Failed";
            } 
            echo json_encode($response);                                   
        }
        
}

?>