<?php

include_once('../../config.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("auth",$_GET)){
    $Link = $_GET['auth'];  
    $sql = mysqli_query($conn,"SELECT * FROM forgetpassword WHERE link='$Link'");
    $row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

    if(!empty($row)){  
        $Email = $row['email']; 
        $response['status'] = "active";
        $response['userId'] = $row['userId'];
        $response['email'] = $row['email']; 
        $response['link'] = $row['link'];
        $response['expire'] = $row['expire'];             
        $response['created'] = $row['created'];
        $response['message'] = "Active Linked"; 
        
    }else{
        $response['status'] = "error";
        $response['message'] = "Invalid Linked";
        
    }
  

    echo json_encode($response);

}

?>