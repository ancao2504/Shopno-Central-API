<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){  

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
                
        $_POST = json_decode(file_get_contents('php://input'), true);
            $groupId = $_POST['groupId'];
            $segment = $_POST['segment'];
            $career = $_POST['career'];
            $BasePrice = $_POST['basePrice'];
            $Taxes = $_POST['taxes'];
            $price = $_POST['price'];
            $bags = $_POST['bags'];
            $seat = $_POST['seat'];
            $journeyTime = $_POST['journeyTime'];


            if($segment == 1){
                $depFrom = $_POST["segments"][0]['depFrom'];
                $arrTo = $_POST["segments"][0]['arrTo'];
                $depTime = $_POST['segments'][0]['depTime'];
                $arrTime = $_POST['segments'][0]['arrTime'];
                $flightNumber = $_POST['segments'][0]['flightNumber'];


                $sql = "UPDATE `groupfare` SET `segment`='$segment',`career`='$career',`BasePrice`='$BasePrice',`Taxes`='$Taxes', `price`='$price', `departure1`='$depFrom', `depTime1`='$depTime', `arrival1`='$arrTo', `arrTime1`='$arrTime', `seat`='$seat', `bags`='$bags', `flightNum1`='$flightNumber', `journeyTime`='$journeyTime' WHERE groupId='$GroupId'";
                

            }else if($segment == 2){
                $depFrom = $_POST["segments"][0]['depFrom'];
                $arrTo = $_POST["segments"][0]['arrTo'];
                $depTime = $_POST['segments'][0]['depTime'];
                $arrTime = $_POST['segments'][0]['arrTime'];
                $flightNumber = $_POST['segments'][0]['flightNumber'];

                //Segment 2
                $depFrom1 = $_POST["segments"][1]['depFrom'];
                $arrTo1 = $_POST["segments"][1]['arrTo'];
                $depTime1 = $_POST['segments'][1]['depTime'];
                $arrTime1 = $_POST['segments'][1]['arrTime'];
                $flightNumber1 = $_POST['segments'][1]['flightNumber'];
                $Transit = $_POST['transit'][0]['time'];

            

                $sql="UPDATE `groupfare` SET `segment`='$segment', `career`='$career', `BasePrice`='$BasePrice', `Taxes`='$Taxes', `price`='$price', `departure1`='$depFrom', `departure2`='$depFrom1', `depTime1`='$depTime',`depTime2`='$depTime1', `arrival1`='$arrTo', `arrival2`='$arrTo1',  `arrTime1`='$arrTime', `arrTime2`='$arrTime1', `seat`='$seat', `bags`='$bags', `flightNum1`='$flightNumber', `flightNum2`='$flightNumber1', `journeyTime`='$journeyTime', `transit1`='$Transit' WHERE groupId='$GroupId'";


            }else if($segment == 3){
                $depFrom = $_POST["segments"][0]['depFrom'];
                $arrTo = $_POST["segments"][0]['arrTo'];
                $depTime = $_POST['segments'][0]['depTime'];
                $arrTime = $_POST['segments'][0]['arrTime'];
                $flightNumber = $_POST['segments'][0]['flightNumber'];

                //Segment 2
                $depFrom1 = $_POST["segments"][1]['depFrom'];
                $arrTo1 = $_POST["segments"][1]['arrTo'];
                $depTime1 = $_POST['segments'][1]['depTime'];
                $arrTime1 = $_POST['segments'][1]['arrTime'];
                $flightNumber1 = $_POST['segments'][1]['flightNumber'];

                //Segment 3

                $depFrom2 = $_POST["segments"][2]['depFrom'];
                $arrTo2 = $_POST["segments"][2]['arrTo'];
                $depTime2 = $_POST['segments'][2]['depTime'];
                $arrTime2 = $_POST['segments'][2]['arrTime'];
                $flightNumber2 = $_POST['segments'][2]['flightNumber'];

                $Transit = $_POST['transit'][0]['time'];
                $Transit1 = $_POST['transit'][1]['time'];

                
                $sql="UPDATE `groupfare` SET `segment`='$segment', `career`='$career', `BasePrice`='$BasePrice', `Taxes`='$Taxes', `price`='$price', `departure1`='$depFrom',`departure2`='$depFrom1', `departure3`='$depFrom2', `depTime1`='$depTime', `depTime2`='$depTime1',`depTime3`='$depTime2',`arrival1`='$arrTo', `arrival2`='$arrTo1',`arrival3`='$arrTo2',`arrTime1`='$arrTime', `arrTime2`='$arrTime1', `arrTime3`='$arrTime2', `seat`='$seat', `bags`='$bags', `flightNum1`='$flightNumber', `flightNum2`='$flightNumber1', `flightNum3`='$flightNumber2', `journeyTime`='$journeyTime', `transit1`='$Transit', `transit2`='$Transit1' WHERE groupId='$GroupId'";

            }
            
        
            if ($conn->query($sql) === TRUE) {
                $response["action"] = "success";
                $response["message"] = "Group Updated Added";
            }
        echo json_encode($response);
                            
    }
}else{
  authorization($conn);
}


?>