<?php

require '../config.php';

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


  if($_SERVER['REMOTE_ADDR']){
    $ip = $_SERVER['REMOTE_ADDR'];
    include 'Browser.php';
    $ua = getBrowser();
    $browser = $ua['name'];
    $platform = $ua['platform'];

  }else{
    $ip = 'No IP';
  }
  

  $agentSql = mysqli_query($conn,"SELECT email, status FROM agent WHERE email='$email'");
  $agentrow = mysqli_fetch_array($agentSql,MYSQLI_ASSOC);

  $staffSql = mysqli_query($conn,"SELECT email, status FROM staffList WHERE email='$email'");
  $staffrow = mysqli_fetch_array($staffSql,MYSQLI_ASSOC);

  if(empty($agentrow) && empty($staffrow)){
      $response['status']="error";
      $response['message']="User does not exist";   						
  }else if(!empty($agentrow)){
    $checkUserquery="SELECT agentId, email, name, company, phone, status FROM agent WHERE email='$email' AND `password`=convert('$password' using utf8mb4) collate utf8mb4_bin";
    $resultant=mysqli_query($conn,$checkUserquery);

    if(mysqli_num_rows($resultant)>0){
      while($row=$resultant->fetch_assoc()){
          if($row['status'] == 'active'){
              $agentId = $row['agentId'];
              $agencyName = $row['company'];     
              $response['user']=$row;
              $response['action']="complete";
              $response['message']="success";
              $conn->query("UPDATE `agent` SET `isActive`='yes',`loginIp`='$ip',`browser`='$browser',`platform`='$platform' WHERE email='$email'");
              $conn->query("INSERT INTO `lastLogin`(`agentId`, `agencyName`, `StaffName`, `loginIp`, `success`,`browser`,`platform`, `craetedTime`)
               VALUES ('$agentId','$agencyName','No','$ip','yes','$browser','$platform','$currentTime')");                 
          }else if($row['status'] == 'pending'){
              $response['action']="pending";
              $response['message']="Your agency registration process is pending.";              
          }else if($row['status'] == 'deactive'){
              $response['action']="deactive";
              $response['message']="Status Is Deactive";              
          } else{
            $response['action']="rejected";
              $response['message']="Status Is Rejected"; 
          }         
      }
           
    }else{
          $response['action']="incomplete";
          $response['message']="Wrong Password";
    }   
    
  }else if(!empty($staffrow)){
    $checkUserquery="SELECT staffId, agentId, email, phone, name fROM staffList WHERE email='$email' AND password=convert('$password' using utf8mb4) collate utf8mb4_bin";   
    $resultant=mysqli_query($conn,$checkUserquery);

    if(mysqli_num_rows($resultant)>0){
      while($row=$resultant->fetch_assoc()){
          $agentId = $row['agentId'];
          $checkAgent = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'"),MYSQLI_ASSOC);        
          $agentSatus = $checkAgent['status'];

          if(isset($agentSatus)){          
            if($agentSatus == 'deactive'){
               $response['action']="incomplete";
               $response['message']="Agent Is Deactive";
              
            }else if($agentSatus == 'active'){
                $agencyName = $checkAgent['company'];
                $response['user']=$row;
                $response['action']="complete";
                $response['message']="success";
                $conn->query("INSERT INTO `lastLogin`(`agentId`, `agencyName`, `StaffName`, `loginIp`, `success`,`browser`,`platform`, `craetedTime`)
                VALUES ('$agentId','$agencyName','No','$ip','yes','$browser','$platform','$currentTime')");   
            }else{
              $response['action']="incomplete";
              $response['message']="Agent Is Deactive";
            }
          }else{
              $response['action']="incomplete";
              $response['message']="Agent doesnt Exists";
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