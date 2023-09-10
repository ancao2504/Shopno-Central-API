<?php

  require '../../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
  $_POST = json_decode(file_get_contents('php://input'), true);

  $email =$_POST['email'];
  $password =$_POST['password'];

  $userSql = mysqli_query($conn,"SELECT email status FROM admin WHERE email='$email'");
  $userrow = mysqli_fetch_array($userSql,MYSQLI_ASSOC);

  if(empty($userrow)){
      $response['status']="error";
      $response['message']="User does not exist"; 						
  }else if(!empty($userrow)){
    $checkUserquery="SELECT * FROM admin WHERE email='$email' and password='$password'";
    $resultant=mysqli_query($conn,$checkUserquery);

    if(mysqli_num_rows($resultant)>0){

      while($row=$resultant->fetch_assoc()){
              $response['user']=$row;
              $response['status']="complete";
              $response['message']="success";                     
      }
           
    }else{
          $response['status']="Incomplete";
          $response['message']="Wrong Password";
    }   
    
  }
  echo json_encode($response);
  mysqli_close($conn);
}
    
?>