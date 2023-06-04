<?php

require '../../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST"){

  $currentTime = date("Y-m-d H:i:s");
            
  $_POST = json_decode(file_get_contents('php://input'), true);

  $email =  trim($_POST['email']);
  $password = trim($_POST['password']);


  $agentSql = mysqli_query($conn,"SELECT email, status FROM agent WHERE email='$email' AND platform = 'B2C'");
  $agentrow = mysqli_fetch_array($agentSql,MYSQLI_ASSOC);

  if(empty($agentrow)){
      $response['status']="error";
      $response['message']="User does not exist";   						
  }else if(!empty($agentrow)){
    $checkUserquery="SELECT agentId, email, name, company, phone, status FROM agent WHERE platform='B2C' AND email='$email' AND `password`=convert('$password' using utf8mb4) collate utf8mb4_bin";
    $resultant=mysqli_query($conn,$checkUserquery);

    if(mysqli_num_rows($resultant)>0){

      while($row=$resultant->fetch_assoc()){
          if($row['status'] == 'active'){
              $agencyName = $row['company'];     
              $response['user']=$row;
              $response['action']="complete";
              $response['message']="success";                
          }else if($row['status'] == 'pending'){
              $response['action']="pending";
              $response['message']="Your agency registration process is pending";              
          }else if($row['status'] == 'deactive'){
              $response['action']="deactive";
              $response['message']="Status Is Deactive";              
          }          
      }
           
    }else{
          $response['action']="incomplete";
          $response['message']="Wrong Password";
    }   
    
  } 
    echo json_encode($response); 
  }

  

    
?>