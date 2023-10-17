<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){
     
    if (array_key_exists('agentId',$_GET) && array_key_exists('actionBy',$_GET)){
            $agentId= $_GET['agentId'];
            $actionBy = $_GET['actionBy'];
            $createdTime = date("Y-m-d H:i:s");
            $checker = $conn->query("SELECT * FROM agent WHERE agentId = '$agentId'")->fetch_all(MYSQLI_ASSOC);

                if(!empty($checker)) {

                    $sql="UPDATE `agent` SET `status`='pending' WHERE agentId='$agentId'";
                    if($conn->query($sql) === TRUE){     
                        $response['status']="success";
                        $response['agentId']="$agentId";
                        $response['message']="Agent Pending";
                            
                    }

                }else {
                    $response['status']="error";
                    $response['agentId']="$agentId";
                    $response['message']="Agent Not Found";
                }

                echo json_encode($response);
    }
}else{
  authorization($conn);
}
        

        
?>