<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require '../../../vendor/autoload.php';


if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
  $_POST = json_decode(file_get_contents('php://input'), true);

  $email =$_POST['email'];
  $password =$_POST['password'];

  $userSql = mysqli_query($conn,"SELECT email status FROM users WHERE email='$email'");
  $userrow = mysqli_fetch_array($userSql,MYSQLI_ASSOC);

  if(empty($userrow)){
      $response['status']="error";
      $response['message']="User does not exist"; 						
  }else if(!empty($userrow)){
    $checkUserquery="SELECT * FROM users WHERE email='$email' and password='$password'";
    $resultant=mysqli_query($conn,$checkUserquery);

    if(mysqli_num_rows($resultant)>0){

      while($row=$resultant->fetch_assoc()){
        if(strtolower($row['status']) == 'active') {
            $empId = $row['EMP_ID'];
         
            $secretKey = $SECRETE_KEY;
            $issuer = $AUTH_DOMAIN;
            $issuedAt = time();
            $expire = $issuedAt + 1200;

            
            $query = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM access_api_key"), MYSQLI_ASSOC);
            if(isset($query)){
                $admin_api_key = $query['admin_api_key'];
            }

            $data = [
                'user' => $row,
                'empId' => $empId,
                'access_token'  => $admin_api_key,
                'issue_time' => $issuedAt,
                'exp_time' => $expire,
            ];

            $token = JWT::encode($data, $secretKey, 'HS256');

            $response['status']="success";
            $response['message']="Login success"; 
            $response['token'] = $token;
        }else{
            $response['status']="error";
            $response['action']="incomplete";
            $response['message']="Account Deactivated"; 
        }                     
      }
      
           
    }else{
          $response['action']="Incomplete";
          $response['message']="Wrong Password"; 
    }   
    
  }
  echo json_encode($response);
  mysqli_close($conn);
}
    
?>