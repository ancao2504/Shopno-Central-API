<?php

require '../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if (array_key_exists("agentId", $_GET)) {
    
  $agentId = $_GET["agentId"];
  $result = $conn->query("SELECT * FROM `agent` where agentId = '$agentId' ");
  $return_arr = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
      
      $balanced = $conn->query("SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
      
      $response = $row;
      $response['lastAmount'] = $balanced[0]['lastAmount'];
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}else if(array_key_exists("action",$_GET)){
  
  $action = $_GET['action'];
  if($action == 'update'){   
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
        $_POST = json_decode(file_get_contents('php://input'), true);
        
        $agentId = $_POST["agentId"];
        $name = $_POST["name"];
        $phone = $_POST["phone"];
        $company = $_POST["company"];
        $companyadd = $_POST["companyadd"];  

        $sql = "UPDATE `agent` SET `name`='$name',`phone`='$phone'
        ,`company`='$company',`companyadd`='$companyadd' WHERE agentId ='$agentId'";
     
        if ($conn->query($sql) === TRUE) {
            $response['status']="success";
            $response['message']="Updated Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Updated failed";
        }

      echo json_encode($response);
    }
      
  }else if($action== 'changepassword'){
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
        $_POST = json_decode(file_get_contents('php://input'), true); 
        
        $agentId = $_POST["agentId"];
        $oldPassword = $_POST["oldpassword"];
        $newPassword = $_POST["newpassword"];

        
        $sql = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
        $row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

        if(!empty($row)){
            $currentPassword = $row['password'];							

            if($currentPassword == $oldPassword){
              
              $updatesql = "UPDATE `agent` SET `password`='$newPassword' WHERE agentId='$agentId'";
          
              if ($conn->query($updatesql) === TRUE) {
                  $response['status']="success";
                  $response['message']="Password Updated Successfully";          
              } else {
                  $response['status']="error";
                  $response['message']="Updated failed";
              }
            }else{
                  $response['status']="error";
                  $response['message']="Current Password Wrong";
            }
      }
      echo json_encode($response);
    }
  }else if($action== 'resetpassword'){
    if ($_SERVER["REQUEST_METHOD"] == "POST"){  

      $_POST = json_decode(file_get_contents('php://input'), true); 
            
        $agentId = $_POST["agentId"];
        $newPassword = $_POST["newpassword"];
         
        $updatesql = "UPDATE `agent` SET `password`='$newPassword' WHERE agentId='$agentId'";
      
        if ($conn->query($updatesql) === TRUE) {
              $response['status']="success";
              $response['message']="Password Updated Successfully";          
        } else {
              $response['status']="error";
              $response['message']="Updated failed";
        }

      echo json_encode($response);
    }
  }else if($action== 'changestaffpassword'){
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
        $_POST = json_decode(file_get_contents('php://input'), true); 
        
        $agentId = $_POST["agentId"];
        $staffId = $_POST["staffId"];
        $oldPassword = $_POST["oldpassword"];
        $newPassword = $_POST["newpassword"];

        
        $sql = mysqli_query($conn,"SELECT * FROM staffList WHERE agentId='$agentId' AND staffId='$staffId'");
        $row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

        if(!empty($row)){
            $currentPassword = $row['password'];							

            if($currentPassword == $oldPassword){
              
              $updatesql = "UPDATE `staffList` SET `password`='$newPassword' WHERE agentId='$agentId' AND staffId='$staffId'";
          
              if ($conn->query($updatesql) === TRUE) {
                  $response['status']="success";
                  $response['message']="Password Updated Successfully";          
              } else {
                  $response['status']="error";
                  $response['message']="Updated failed";
              }
            }else{
                  $response['status']="error";
                  $response['message']="Current Password Wrong";
            }
      }
      echo json_encode($response);
    }
  }
}