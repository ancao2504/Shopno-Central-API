<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);
        $code = $_POST['code'];    
        $name = $_POST['name'];
        $city = $_POST['city'];    
        $country = $_POST['country'];

        $sqlCr = mysqli_query($conn,"SELECT code, name FROM allairport WHERE code='$code' ");
        $rowCr = mysqli_fetch_array($sqlCr,MYSQLI_ASSOC);

        if(!empty($rowCr)){
            $response['status']="error";
            $response['message']="Airport Code Already Added";							
        }else{
              
        $sql = "INSERT INTO `allairport`(`code`,`name`,`city`,`country`)
                VALUES('$code','$name','$city','$country')";

        if ($conn->query($sql) === TRUE) {
            $response['status']="success";
            $response['message']="Airport Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Airport Added Failed";
        }
    
    }
         
        echo json_encode($response);
    
    }

?>