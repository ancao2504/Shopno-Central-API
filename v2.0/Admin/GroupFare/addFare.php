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
            $segment = $_POST['segment'];
            $career = $_POST['career'];
            $invoice = $_POST['invoice'];
            $vendor = $_POST['vendor'];
            $BasePrice = $_POST['basePrice'];
            $Taxes = $_POST['taxes'];
            $price = $_POST['price'];
            $bags = $_POST['bags'];
            $seat = $_POST['seat'];
            $journeyTime = $_POST['journeyTime'];

            $GroupId ="";
            $sql = "SELECT * FROM groupfare ORDER BY groupId DESC LIMIT 1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["groupId"]);
                    $number= (int)$outputString + 1;
                    $GroupId = "FFIG-$number";								
            }
            } else {
                $GroupId ="FFIG-1000";
            }

            if($segment == 1){
                $depFrom = $_POST["segments"][0]['depFrom'];
                $arrTo = $_POST["segments"][0]['arrTo'];
                $depTime = $_POST['segments'][0]['depTime'];
                $arrTime = $_POST['segments'][0]['arrTime'];
                $flightNumber = $_POST['segments'][0]['flightNumber'];

                $sql="INSERT INTO `groupfare`(`groupId`,`vendor`,`invoice`,`segment`, `career`, `price`, `departure1`, `depTime1`, `arrival1`,  `arrTime1`, `seat`, `bags`, `flightNum1`, `journeyTime`)
                VALUES ('$GroupId','$vendor','$invoice','$segment','$career','$price','$depFrom','$depTime','$arrTo','$arrTime','$seat','$bags',' $flightNumber','$journeyTime')";
    

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

                $sql="INSERT INTO `groupfare`(`groupId`,`vendor`,`invoice`,`segment`, `career`,`price`, `departure1`,`departure2`, `depTime1`,`depTime2`, `arrival1`,`arrival2`,  `arrTime1`, `arrTime2`, `seat`, `bags`, `flightNum1`,`flightNum2`, `journeyTime`,`transit1`)
                VALUES ('$GroupId','$vendor','$invoice','$segment','$career','$price','$depFrom','$depFrom1','$depTime','$depTime1','$arrTo','$arrTo1','$arrTime','$arrTime1','$seat','$bags','$flightNumber','$flightNumber1','$journeyTime','$Transit')";           

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

                $sql="INSERT INTO `groupfare`(`groupId`,`vendor`,`invoice`,`segment`, `career`, `BasePrice`, `Taxes`, `price`, `departure1`,`departure2`,`departure3`, `depTime1`,`depTime2`,`depTime3`,`arrival1`,`arrival2`,`arrival3`,`arrTime1`, `arrTime2`,`arrTime3`, `seat`, `bags`, `flightNum1`,`flightNum2`,`flightNum3`,`journeyTime`,`transit1`,`transit2`)
                VALUES ('$GroupId','$vendor','$invoice','$segment','$career','$BasePrice','$Taxes','$price','$depFrom','$depFrom1','$depFrom2','$depTime','$depTime1','$depTime2','$arrTo','$arrTo1','$arrTo2','$arrTime','$arrTime1','$arrTime2','$seat','$bags','$flightNumber','$flightNumber1','$flightNumber2','$journeyTime','$Transit','$Transit1')";

            }
            
        
            if ($conn->query($sql) === TRUE) {
                $response["action"] = "success";
                $response["message"] = "Group Fare Added";
            }
        echo json_encode($response);
                            
    }
}else{
  authorization($conn);
}



?>