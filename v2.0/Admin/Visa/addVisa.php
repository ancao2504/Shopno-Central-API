<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){  
    if(array_key_exists('all',$_GET)){
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            $data = json_decode(file_get_contents('php://input'), true);

            $iddata = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM visa_info ORDER BY id DESC LIMIT 1"));
            if(isset($iddata)){
                $id = substr($iddata['visaId'],4) + 1;
                $visaId = "FFVS$id";
            }else{
                $visaId = "FFVS1000";
            }

            $createdAt = date('Y-m-d H:i:s');
            $country =  $data['country'];
            $category = $data['category'];
            $visatype = $data['visatype'];   
            $Entry = $data['entry'];
            $checkList = $data['checklist'];
            $passengertype = $data['passengertype'];

            $res = 0 ;
            
            
            foreach($Entry as $entry) {
                $entrytype = $entry["entrytype"]; 
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

                $sql = "INSERT INTO `visa_info`( 
                        `visaId`,
                        `country`,
                        `visaType`,
                        `visaCategory`,
                        `entryType`,
                        `duration`,
                        `maximumStay`,
                        `processingTime`,
                        `interview`,
                        `cost`,
                        `embassyFee`,
                        `agentFee`,
                        `agencyFee`,
                        `FFIServiceCharge`,
                        `total`,
                        `createdAt`
                    ) VALUES(
                        '$visaId',
                        '$country',
                        '$visatype',
                        '$category',
                        '$entrytype',
                        '$duration',
                        '$maximumStay',
                        '$processingTime',
                        '$interview',
                        '$cost',
                        '$embassyFee',
                        '$agentFee',
                        '$agencyFee',
                        '$FFIServiceCharge',
                        '$total',
                        '$createdAt'            
                    )";
                    
                    if($conn->query($sql)){
                        
                }
            }

            foreach($checkList as $checklist){
                $datas = $checklist["data"]; 
                $sqlData = "INSERT INTO `visa_check_list`(`visaId`,`country`,`category`,`visatype`,`passengertype`,`checkList`) 
                                VALUES('$visaId','$country','$category','$visatype','$passengertype','$datas')";

                if($conn->query($sqlData)){
                    $res = 1;
                }          
            }

                


            if($res == 1){
                $response['status'] = "success";
                $response['message'] = "Data Saved";
                echo json_encode($response);
            }else if($res == 2){
                $response['status'] = "success";
                $response['message'] = "Data Already Saved";
                echo json_encode($response);
            }else{
                $response['status'] = "error";
                $response['message'] = "Data Not Saved";
                echo json_encode($response);
            }
            
        }
    }else if(array_key_exists('checklist',$_GET)){
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            $data = json_decode(file_get_contents('php://input'), true);    

            $visaId = $data['visaId'];
            $country =  $data['country'];
            $category = $data['category'];
            $visatype = $data['visatype'];   
            $checkList = $data['checklist'];
            $passengertype = $data['passengertype'];
            

            foreach($checkList as $checklist){
                $datas = $checklist["data"]; 
                $sqlData = "INSERT INTO `visa_check_list`(`visaId`,`country`,`category`,`visatype`,`passengertype`,`checkList`) 
                                VALUES('$visaId','$country','$category','$visatype','$passengertype','$datas')";

                if($conn->query($sqlData)){
                    $res = 1;
                }          
            }

                
            if($res == 1){
                $response['status'] = "success";
                $response['message'] = "Data Saved";
                echo json_encode($response);
            }else if($res == 2){
                $response['status'] = "success";
                $response['message'] = "Data Already Saved";
                echo json_encode($response);
            }else{
                $response['status'] = "error";
                $response['message'] = "Data Not Saved";
                echo json_encode($response);
            }
            
        }
    }

}else{
  authorization($conn);
}