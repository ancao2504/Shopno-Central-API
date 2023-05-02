<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'];
        $code = $_POST['code'];    
        $name = $_POST['nameEnglish'];
        $nameBangla = $_POST['nameBangla'];
        $commission = $_POST['commission'];

              
        $sql = "UPDATE `airlines` SET `code`='$code',`name`='$name',`nameBangla`='$nameBangla',`commission`='$commission' WHERE id='$id'";

        if ($conn->query($sql) === TRUE) {
            $response['status']="sucess";
            $response['message']="Airlines Updated Successfully";                     
        }else{
            $response['status']="error";
            $response['message']="Updated Failed Successfully";
        }
         
    echo json_encode($response);
    
}



?>