<?php

  require '../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
  $_POST = json_decode(file_get_contents('php://input'), true);

  $agentId =$_POST['agentId'];
  $coupon =$_POST['coupon'];
  

  $couponSql = mysqli_query($conn,"SELECT * FROM coupon WHERE coupon='$coupon'");
  $couponRow = mysqli_fetch_array($couponSql,MYSQLI_ASSOC);

    if(!empty($couponRow)){
        if($couponRow['status'] == 'active'){
            
            $useCount="SELECT * FROM booking WHERE agentId ='$agentId' AND coupon='$coupon'";
            $useCount=mysqli_query($conn,$useCount);

            if(mysqli_num_rows($useCount) <= $couponRow['uselimit']){
                $response['status'] = "success";
                $response['message'] = "Coupon Applied";
                
            }else{
                $response['status'] = "error";
                $response['message'] = "You have already exceed the use of this coupon limit";
            }
        }else{
            $response['status'] = "error";
            $response['message'] = "Coupon is Expired";
        }

          
        
    }else{
        $response['status'] = "error";
        $response['message'] = "Coupon not found";
    }

  echo json_encode($response);
}
    
?>