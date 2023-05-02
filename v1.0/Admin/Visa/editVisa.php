<?php 

require '../../config.php';
  
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if(array_key_exists('entry',$_GET)){
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        $_POST = json_decode(file_get_contents('php://input'), true);
        $entry = $_POST['entry'];       
                $id = $entry['id'];
                $duration = $entry['duration'];
                $maximumStay = $entry['maximumStay'];
                $processingTime = $entry['processingTime'];
                $interview = $entry['interview'];
                $cost = $entry['cost'];
                $embassyFee = $entry['embassyFee'];
                $agentFee = $entry['agentFee'];
                $agencyFee = $entry['agencyFee'];
                $FFIServiceCharge = $entry['ffiServiceCharge'];
                $total = $entry['total'];

                $sql = "UPDATE `visa_info` SET
                        `duration`='$duration',
                        `maximumStay`='$maximumStay',
                        `processingTime`='$processingTime',
                        `interview`='$interview',
                        `cost`='$cost',
                        `embassyFee`='$embassyFee',
                        `agentFee`='$agentFee',
                        `agencyFee`='$agencyFee',
                        `FFIServiceCharge`='$FFIServiceCharge',
                        `total`='$total' where id='$id'";

        if ($conn->query($sql)) {
            $response['status'] = "success";
            $response['message'] = "Data Updated";
            echo json_encode($response);
        }          
            
    }
}else if(array_key_exists('ckId',$_GET)){
     if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $Id = $_GET['ckId'];
        
        $_POST = json_decode(file_get_contents('php://input'), true);
        
            $datas = $_POST['data'];

            $sql = "UPDATE `visa_check_list` SET `checkList`='$datas' where id='$Id'";
            
            if($conn->query($sql)){
                $response['status'] = "success";
                $response['message'] = "Data Saved";
                echo json_encode($response);
            }else{
                $response['status'] = "error";
                $response['message'] = "Data Saved Failed";
                echo json_encode($response);
            }
        }
            
    
}
                                            
                                      
            
            
?>