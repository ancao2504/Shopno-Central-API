<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Data = json_decode(file_get_contents('php://input'), true);
    $type = $Data['tripType'];
    $pnr = $Data['pnr'];
    $createdAt = date('Y-m-d H:i:s');
    $uId = sha1(md5(time()));

    if($type == 'oneway'){
        $_POST = $Data['flightData'];
        $system = $_POST["system"];
        $segment = $_POST["segment"];
        
        if(isset($_POST["SearchID"]) && isset($_POST["ResultID"])){
            $searchId = $_POST["SearchID"];
            $resultId = $_POST["ResultID"];
        }else{
            $searchId = '';
            $resultId = '';
        }
    
        if($segment==1){        
        $departure1 = $_POST['segments'][0]["departure"];
        $arrival1 = $_POST['segments'][0]["arrival"];
        $departureTime1 = $_POST['segments'][0]["departureTime"];
        $arrivalTime1 = $_POST['segments'][0]["arrivalTime"];
        $flightDuration1 = $_POST['segments'][0]["flightduration"];
        $transit1 = $_POST['transit']["transit1"];
        $marketingCareer1 = $_POST['segments'][0]["marketingcareer"];
        $marketingCareerName1 = $_POST['segments'][0]["marketingcareerName"];
        $marketingFlight1 = $_POST['segments'][0]["marketingflight"];
        $operatingCareer1 = $_POST['segments'][0]["operatingcareer"];
        $operatingFlight1 = $_POST['segments'][0]["operatingflight"];
        $departureAirport1 = str_replace("'", "''",$_POST['segments'][0]["departureAirport"]);
        $arrivalAirport1 = str_replace("'", "''",$_POST['segments'][0]["arrivalAirport"]);
        $departureTerminal1 = $_POST['segments'][0]["departureTerminal"];
        $arrivalTerminal1 = $_POST['segments'][0]["arrivalTerminal"];
        $departureLocation1 = $_POST['segments'][0]["departureLocation"];
        $arrivalLocation1 = $_POST['segments'][0]["arrivalLocation"];
        $bookingCode1 = $_POST['segments'][0]["bookingcode"];
        $seat = $_POST['segments'][0]["seat"];


        $sql1 = "INSERT INTO `segment_one_way`( 
            `system`,
            `segment`,
            `pnr`,
            `departure1`,
            `arrival1`,
            `departureTime1`,
            `arrivalTime1`,
            `flightDuration1`,
            `marketingCareer1`,
            `marketingCareerName1`,
            `marketingFlight1`,
            `operatingCareer1`,
            `operatingFlight1`,
            `departureAirport1`,
            `arrivalAirport1`,
            `departureTerminal1`,
            `arrivalTerminal1`,
            `departureLocation1`,
            `arrivalLocation1`,
            `bookingCode1`,
            `resultId`,
            `searchId`,
            `createdAt`,
            `uid`      
        )
        VALUES(
            '$system',
            '$segment',
            '$pnr',           
            '$departure1',
            '$arrival1',
            '$departureTime1',
            '$arrivalTime1',
            '$flightDuration1',
            '$marketingCareer1',
            '$marketingCareerName1',
            '$marketingFlight1',
            '$operatingCareer1',
            '$operatingFlight1',
            '$departureAirport1',
            '$arrivalAirport1',
            '$departureTerminal1',
            '$arrivalTerminal1',
            '$departureLocation1',
            '$arrivalLocation1',           
            '$bookingCode1',
            '$searchId',
            '$resultId',
            '$createdAt',
            '$uId'            
            
        )";

        $query1 = mysqli_query($conn, $sql1);
        if($query1==true){
            $response['status'] = "success";
            $response['uid'] = "$uId";
            $response['message'] = "Data Saved";
        }else{
            $response['status'] = "error";
            $response['message'] = "Data Not Saved";
        }

        }elseif($segment==2){
            $departure1 = $_POST['segments'][0]["departure"];
            $arrival1 = $_POST['segments'][0]["arrival"];
            $departureTime1 = $_POST['segments'][0]["departureTime"];
            $arrivalTime1 = $_POST['segments'][0]["arrivalTime"];
            $flightDuration1 = $_POST['segments'][0]["flightduration"];
            $transit1 = $_POST['transit']["transit1"];
            $marketingCareer1 = $_POST['segments'][0]["marketingcareer"];
            $marketingCareerName1 = $_POST['segments'][0]["marketingcareerName"];
            $marketingFlight1 = $_POST['segments'][0]["marketingflight"];
            $operatingCareer1 = $_POST['segments'][0]["operatingcareer"];
            $operatingFlight1 = $_POST['segments'][0]["operatingflight"];
            $departureAirport1 = str_replace("'", "''",$_POST['segments'][0]["departureAirport"]);
            $arrivalAirport1 = str_replace("'", "''",$_POST['segments'][0]["arrivalAirport"]);
            $departureTerminal1 = $_POST['segments'][0]["departureTerminal"];
            $arrivalTerminal1 = $_POST['segments'][0]["arrivalTerminal"];
            $departureLocation1 = $_POST['segments'][0]["departureLocation"];
            $arrivalLocation1 = $_POST['segments'][0]["arrivalLocation"];
            $bookingCode1 = $_POST['segments'][0]["bookingcode"];
            
            //segment 2
            $departure2 = $_POST['segments'][1]["departure"];
            $arrival2 = $_POST['segments'][1]["arrival"];
            $departureTime2 = $_POST['segments'][1]["departureTime"];
            $arrivalTime2 = $_POST['segments'][1]["arrivalTime"];
            $flightDuration2 = $_POST['segments'][1]["flightduration"];
            $marketingCareer2 = $_POST['segments'][1]["marketingcareer"];
            $marketingCareerName2 = $_POST['segments'][1]["marketingcareerName"];
            $marketingFlight2 = $_POST['segments'][1]["marketingflight"];
            $operatingCareer2 = $_POST['segments'][1]["operatingcareer"];
            $operatingFlight2 = $_POST['segments'][1]["operatingflight"];
            $departureAirport2 = str_replace("'", "''",$_POST['segments'][1]["departureAirport"]);
            $arrivalAirport2 = str_replace("'", "''",$_POST['segments'][1]["arrivalAirport"]);
            $departureTerminal2 = $_POST['segments'][1]["departureTerminal"];
            $arrivalTerminal2 = $_POST['segments'][1]["arrivalTerminal"];
            $departureLocation2 = $_POST['segments'][1]["departureLocation"];
            $arrivalLocation2 = $_POST['segments'][1]["arrivalLocation"];
            $bookingCode2 = $_POST['segments'][1]["bookingcode"];

            $sql2 = "INSERT INTO `segment_one_way`(
                `system`,
                `segment`,
                `pnr`,
                `departure1`,
                `departure2`,
                `arrival1`,
                `arrival2`,          
                `departureTime1`,
                `departureTime2`,          
                `arrivalTime1`,
                `arrivalTime2`,           
                `flightDuration1`,
                `flightDuration2`,           
                `transit1`,
                `marketingCareer1`,
                `marketingCareer2`,
                `marketingCareerName1`,
                `marketingCareerName2`,
                `marketingFlight1`,
                `marketingFlight2`,
                `operatingCareer1`,
                `operatingCareer2`,
                `operatingFlight1`,
                `operatingFlight2`,
                `departureAirport1`,
                `departureAirport2`,
                `arrivalAirport1`,
                `arrivalAirport2`,
                `arrivalTerminal1`,
                `arrivalTerminal2`,
                `departureTerminal1`,
                `departureTerminal2`,
                `departureLocation1`,
                `departureLocation2`,
                `arrivalLocation1`,
                `arrivalLocation2`,
                `bookingCode1`,
                `bookingCode2`,
                `resultId`,
                `searchId`,
                `createdAt`,
                `uid`           
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$departure1',
                '$departure2',
                '$arrival1',
                '$arrival2',
                '$departureTime1',
                '$departureTime2',
                '$arrivalTime1',
                '$arrivalTime2',
                '$flightDuration1',
                '$flightDuration2',
                '$transit1',
                '$marketingCareer1',
                '$marketingCareer2',
                '$marketingCareerName1',
                '$marketingCareerName2',           
                '$marketingFlight1',
                '$marketingFlight2',
                '$operatingCareer1',
                '$operatingCareer2',
                '$operatingFlight1',
                '$operatingFlight2',
                '$departureAirport1',
                '$departureAirport2',
                '$arrivalAirport1',
                '$arrivalAirport2',
                '$arrivalTerminal1`',
                '$arrivalTerminal2',
                '$departureTerminal1',
                '$departureTerminal2',
                '$departureLocation1',
                '$departureLocation2',
                '$arrivalLocation1',
                '$arrivalLocation2',            
                '$bookingCode1',
                '$bookingCode2',           
                '$searchId',
                '$resultId',
                '$createdAt',
                '$uId'     
                
            )";

            $query2 = mysqli_query($conn, $sql2);
            if($query2==true){
                $response['status'] = "success";
            $response['uid'] = "$uId";
                $response['message'] = "Data Saved";
            }else{
                $response['status'] = "error";
                $response['message'] = "Data Not Saved";
            }

        }elseif($segment==3){
            $departure1 = $_POST['segments'][0]["departure"];
            $arrival1 = $_POST['segments'][0]["arrival"];
            $departureTime1 = $_POST['segments'][0]["departureTime"];
            $arrivalTime1 = $_POST['segments'][0]["arrivalTime"];
            $flightDuration1 = $_POST['segments'][0]["flightduration"];
            $transit1 = $_POST['transit']["transit1"];
            $marketingCareer1 = $_POST['segments'][0]["marketingcareer"];
            $marketingCareerName1 = $_POST['segments'][0]["marketingcareerName"];
            $marketingFlight1 = $_POST['segments'][0]["marketingflight"];
            $operatingCareer1 = $_POST['segments'][0]["operatingcareer"];
            $operatingFlight1 = $_POST['segments'][0]["operatingflight"];
            $departureAirport1 = str_replace("'", "''",$_POST['segments'][0]["departureAirport"]);
            $arrivalAirport1 = str_replace("'", "''",$_POST['segments'][0]["arrivalAirport"]);
            $departureTerminal1 = $_POST['segments'][0]["departureTerminal"];
            $arrivalTerminal1 = $_POST['segments'][0]["arrivalTerminal"];
            $departureLocation1 = $_POST['segments'][0]["departureLocation"];
            $arrivalLocation1 = $_POST['segments'][0]["arrivalLocation"];
            $bookingCode1 = $_POST['segments'][0]["bookingcode"];
            
            //segment 2
            $departure2 = $_POST['segments'][1]["departure"];
            $arrival2 = $_POST['segments'][1]["arrival"];
            $departureTime2 = $_POST['segments'][1]["departureTime"];
            $arrivalTime2 = $_POST['segments'][1]["arrivalTime"];
            $flightDuration2 = $_POST['segments'][1]["flightduration"];
            $transit2 = $_POST['transit']["transit2"];
            $marketingCareer2 = $_POST['segments'][1]["marketingcareer"];
            $marketingCareerName2 = $_POST['segments'][1]["marketingcareerName"];
            $marketingFlight2 = $_POST['segments'][1]["marketingflight"];
            $operatingCareer2 = $_POST['segments'][1]["operatingcareer"];
            $operatingFlight2 = $_POST['segments'][1]["operatingflight"];
            $departureAirport2 = str_replace("'", "''",$_POST['segments'][1]["departureAirport"]);
            $arrivalAirport2 = str_replace("'", "''",$_POST['segments'][1]["arrivalAirport"]);
            $departureTerminal2 = $_POST['segments'][1]["departureTerminal"];
            $arrivalTerminal2 = $_POST['segments'][1]["arrivalTerminal"];
            $departureLocation2 = $_POST['segments'][1]["departureLocation"];
            $arrivalLocation2 = $_POST['segments'][1]["arrivalLocation"];
            $bookingCode2 = $_POST['segments'][1]["bookingcode"];

            //segment 3
            $departure3 = $_POST['segments'][2]["departure"];
            $arrival3 = $_POST['segments'][2]["arrival"];
            $departureTime3 = $_POST['segments'][2]["departureTime"];
            $arrivalTime3 = $_POST['segments'][2]["arrivalTime"];
            $flightDuration3 = $_POST['segments'][2]["flightduration"];
            $marketingCareer3 = $_POST['segments'][2]["marketingcareer"];
            $marketingCareerName3 = $_POST['segments'][2]["marketingcareerName"];
            $marketingFlight3 = $_POST['segments'][2]["marketingflight"];
            $operatingCareer3 = $_POST['segments'][2]["operatingcareer"];
            $operatingFlight3 = $_POST['segments'][2]["operatingflight"];
            $departureAirport3 = str_replace("'", "''",$_POST['segments'][2]["departureAirport"]);
            $arrivalAirport3 = str_replace("'", "''",$_POST['segments'][2]["arrivalAirport"]);
            $departureTerminal3 = $_POST['segments'][2]["departureTerminal"];
            $arrivalTerminal3 = $_POST['segments'][2]["arrivalTerminal"];
            $departureLocation3 = $_POST['segments'][2]["departureLocation"];
            $arrivalLocation3 = $_POST['segments'][2]["arrivalLocation"];
            $bookingCode3 = $_POST['segments'][2]["bookingcode"];

            $sql3 = "INSERT INTO `segment_one_way`(   
                `system`,
                `segment`,
                 `pnr`,
                `departure1`,
                `departure2`,
                `departure3`,
                `arrival1`,
                `arrival2`,
                `arrival3`,
                `departureTime1`,
                `departureTime2`,
                `departureTime3`,
                `arrivalTime1`,
                `arrivalTime2`,
                `arrivalTime3`,
                `flightDuration1`,
                `flightDuration2`,
                `flightDuration3`,
                `transit1`,
                `transit2`,
                `marketingCareer1`,
                `marketingCareer2`,
                `marketingCareer3`,
                `marketingCareerName1`,
                `marketingCareerName2`,
                `marketingCareerName3`,
                `marketingFlight1`,
                `marketingFlight2`,
                `marketingFlight3`,
                `operatingCareer1`,
                `operatingCareer2`,
                `operatingCareer3`,
                `operatingFlight1`,
                `operatingFlight2`,
                `operatingFlight3`,
                `departureAirport1`,
                `departureAirport2`,
                `departureAirport3`,
                `arrivalAirport1`,
                `arrivalAirport2`,
                `arrivalAirport3`,
                `departureTerminal1`,
                `departureTerminal2`,
                `departureTerminal3`,
                `arrivalTerminal1`,
                `arrivalTerminal2`,
                `arrivalTerminal3`,
                `departureLocation1`,
                `departureLocation2`,
                `departureLocation3`,
                `arrivalLocation1`,
                `arrivalLocation2`,
                `arrivalLocation3`,
                `bookingCode1`,
                `bookingCode2`,
                `bookingCode3`,
                `resultId`,
                `searchId`,
                `createdAt`,
                `uid`           
            )
            VALUES(
                '$system',
                '$segment', 
                '$pnr',         
                '$departure1',
                '$departure2',
                '$departure3',
                '$arrival1',
                '$arrival2',
                '$arrival3',
                '$departureTime1',
                '$departureTime2',
                '$departureTime3',
                '$arrivalTime1',
                '$arrivalTime2',
                '$arrivalTime3',
                '$flightDuration1',
                '$flightDuration2',
                '$flightDuration3',
                '$transit1',
                '$transit2',
                '$marketingCareer1',
                '$marketingCareer2',
                '$marketingCareer3',
                '$marketingCareerName1',
                '$marketingCareerName2',
                '$marketingCareerName3',
                '$marketingFlight1',
                '$marketingFlight2',
                '$marketingFlight3',
                '$operatingCareer1',
                '$operatingCareer2',
                '$operatingCareer3',
                '$operatingFlight1',
                '$operatingFlight2',
                '$operatingFlight3',
                '$departureAirport1',
                '$departureAirport2',
                '$departureAirport3',
                '$arrivalAirport1',
                '$arrivalAirport2',
                '$arrivalAirport3',
                '$departureTerminal1',
                '$departureTerminal2',
                '$departureTerminal3',
                '$arrivalTerminal1',
                '$arrivalTerminal2',
                '$arrivalTerminal3',
                '$departureLocation1',
                '$departureLocation2',
                '$departureLocation3',
                '$arrivalLocation1',
                '$arrivalLocation2',
                '$arrivalLocation3',
                '$bookingCode1',
                '$bookingCode2',
                '$bookingCode3',
                '$searchId',
                '$resultId',
                '$createdAt',
                '$uId'           
                
            )";
            $query3 = mysqli_query($conn, $sql3);
            if($query3==true){
                $response['status'] = "success";
            $response['uid'] = "$uId";
                $response['message'] = "Data Saved";
            }else{
                $response['status'] = "error";
                $response['message'] = "Data Not Saved";
            }

        }elseif($segment==4){
            $departure1 = $_POST['segments'][0]["departure"];
            $arrival1 = $_POST['segments'][0]["arrival"];
            $departureTime1 = $_POST['segments'][0]["departureTime"];
            $arrivalTime1 = $_POST['segments'][0]["arrivalTime"];
            $flightDuration1 = $_POST['segments'][0]["flightduration"];
            $transit1 = $_POST['transit']["transit1"];
            $marketingCareer1 = $_POST['segments'][0]["marketingcareer"];
            $marketingCareerName1 = $_POST['segments'][0]["marketingcareerName"];
            $marketingFlight1 = $_POST['segments'][0]["marketingflight"];
            $operatingCareer1 = $_POST['segments'][0]["operatingcareer"];
            $operatingFlight1 = $_POST['segments'][0]["operatingflight"];
            $departureAirport1 = str_replace("'", "''",$_POST['segments'][0]["departureAirport"]);
            $arrivalAirport1 = str_replace("'", "''",$_POST['segments'][0]["arrivalAirport"]);
            $departureTerminal1 = $_POST['segments'][0]["departureTerminal"];
            $arrivalTerminal1 = $_POST['segments'][0]["arrivalTerminal"];
            $departureLocation1 = $_POST['segments'][0]["departureLocation"];
            $arrivalLocation1 = $_POST['segments'][0]["arrivalLocation"];
            $bookingCode1 = $_POST['segments'][0]["bookingcode"];
            
            //segment 2
            $departure2 = $_POST['segments'][1]["departure"];
            $arrival2 = $_POST['segments'][1]["arrival"];
            $departureTime2 = $_POST['segments'][1]["departureTime"];
            $arrivalTime2 = $_POST['segments'][1]["arrivalTime"];
            $flightDuration2 = $_POST['segments'][1]["flightduration"];
            $transit2 = $_POST['transit']["transit2"];
            $marketingCareer2 = $_POST['segments'][1]["marketingcareer"];
            $marketingCareerName2 = $_POST['segments'][1]["marketingcareerName"];
            $marketingFlight2 = $_POST['segments'][1]["marketingflight"];
            $operatingCareer2 = $_POST['segments'][1]["operatingcareer"];
            $operatingFlight2 = $_POST['segments'][1]["operatingflight"];
            $departureAirport2 = str_replace("'", "''",$_POST['segments'][1]["departureAirport"]);
            $arrivalAirport2 = str_replace("'", "''",$_POST['segments'][1]["arrivalAirport"]);
            $departureTerminal2 = $_POST['segments'][1]["departureTerminal"];
            $arrivalTerminal2 = $_POST['segments'][1]["arrivalTerminal"];
            $departureLocation2 = $_POST['segments'][1]["departureLocation"];
            $arrivalLocation2 = $_POST['segments'][1]["arrivalLocation"];
            $bookingCode2 = $_POST['segments'][1]["bookingcode"];

            //segment 3
            $departure3 = $_POST['segments'][2]["departure"];
            $arrival3 = $_POST['segments'][2]["arrival"];
            $departureTime3 = $_POST['segments'][2]["departureTime"];
            $arrivalTime3 = $_POST['segments'][2]["arrivalTime"];
            $flightDuration3 = $_POST['segments'][2]["flightduration"];
            $transit3 = $_POST['transit']['transit1'];
            $marketingCareer3 = $_POST['segments'][2]["marketingcareer"];
            $marketingCareerName3 = $_POST['segments'][2]["marketingcareerName"];
            $marketingFlight3 = $_POST['segments'][2]["marketingflight"];
            $operatingCareer3 = $_POST['segments'][2]["operatingcareer"];
            $operatingFlight3 = $_POST['segments'][2]["operatingflight"];
            $departureAirport3 = str_replace("'", "''",$_POST['segments'][2]["departureAirport"]);
            $arrivalAirport3 = str_replace("'", "''",$_POST['segments'][2]["arrivalAirport"]);
            $departureTerminal3 = $_POST['segments'][2]["departureTerminal"];
            $arrivalTerminal3 = $_POST['segments'][2]["arrivalTerminal"];
            $departureLocation3 = $_POST['segments'][2]["departureLocation"];
            $arrivalLocation3 = $_POST['segments'][2]["arrivalLocation"];
            $bookingCode3 = $_POST['segments'][2]["bookingcode"];
            $seat = $_POST['segments'][2]["seat"];
            $infanBaseFare = $_POST['pricebreakdown'][0]['BaseFare'];
            $infantTax = $_POST['pricebreakdown'][0]['Tax'];
            $infantPax = $_POST['pricebreakdown'][0]['PaxCount'];
            
            //segment 4
            $departure4 = $_POST['segments'][3]["departure"];
            $arrival4 = $_POST['segments'][3]["arrival"];
            $departureTime4 = $_POST['segments'][3]["departureTime"];
            $arrivalTime4 = $_POST['segments'][3]["arrivalTime"];
            $flightDuration4 = $_POST['segments'][3]["flightduration"];
            $marketingCareer4 = $_POST['segments'][3]["marketingcareer"];
            $marketingCareerName4 = $_POST['segments'][3]["marketingcareerName"];
            $marketingFlight4 = $_POST['segments'][3]["marketingflight"];
            $operatingCareer4 = $_POST['segments'][3]["operatingcareer"];
            $operatingFlight4 = $_POST['segments'][3]["operatingflight"];
            $departureAirport4 = str_replace("'", "''",$_POST['segments'][3]["departureAirport"]);
            $arrivalAirport4 = str_replace("'", "''",$_POST['segments'][3]["arrivalAirport"]);
            $departureTerminal4 = $_POST['segments'][3]["departureTerminal"];
            $arrivalTerminal4 = $_POST['segments'][3]["arrivalTerminal"];
            $departureLocation4 = $_POST['segments'][3]["departureLocation"];
            $arrivalLocation4 = $_POST['segments'][3]["arrivalLocation"];
            $bookingCode4 = $_POST['segments'][3]["bookingcode"];
            $seat = $_POST['segments'][3]["seat"];

            $sql4 = "INSERT INTO `segment_one_way`(  
                `system`,
                `segment`,
                 `pnr`,
                `departure1`,
                `departure2`,
                `departure3`,
                `departure4`,
                `arrival1`,
                `arrival2`,
                `arrival3`,
                `arrival4`,
                `departureTime1`,
                `departureTime2`,
                `departureTime3`,
                `departureTime4`,
                `arrivalTime1`,
                `arrivalTime2`,
                `arrivalTime3`,
                `arrivalTime4`,
                `flightDuration1`,
                `flightDuration2`,
                `flightDuration3`,
                `flightDuration4`,
                `transit1`,
                `transit2`,
                `transit3`,
                `marketingCareer1`,
                `marketingCareer2`,
                `marketingCareer3`,
                `marketingCareer4`,
                `marketingCareerName1`,
                `marketingCareerName2`,
                `marketingCareerName3`,
                `marketingCareerName4`,
                `marketingFlight1`,
                `marketingFlight2`,
                `marketingFlight3`,
                `marketingFlight4`,
                `operatingCareer1`,
                `operatingCareer2`,
                `operatingCareer3`,
                `operatingCareer4`,
                `operatingFlight1`,
                `operatingFlight2`,
                `operatingFlight3`,
                `operatingFlight4`,
                `departureAirport1`,
                `departureAirport2`,
                `departureAirport3`,
                `departureAirport4`,
                `arrivalAirport1`,
                `arrivalAirport2`,
                `arrivalAirport3`,
                `arrivalAirport4`,
                `departureTerminal1`,
                `departureTerminal2`,
                `departureTerminal3`,
                `departureTerminal4`,
                `arrivalTerminal1`,
                `arrivalTerminal2`,
                `arrivalTerminal3`,
                `arrivalTerminal4`,
                `departureLocation1`,
                `departureLocation2`,
                `departureLocation3`,
                `departureLocation4`,
                `arrivalLocation1`,
                `arrivalLocation2`,
                `arrivalLocation3`,
                `arrivalLocation4`,
                `bookingCode1`,
                `bookingCode2`,
                `bookingCode3`,
                `bookingCode4`,
                `resultId`,
                `searchId`,
                `createdAt`,
                `uid`   
            )
            VALUES(
                '$system',
                '$segment', 
                '$pnr',         
                '$departure1',
                '$departure2',
                '$departure3',
                '$departure4',
                '$arrival1',
                '$arrival2',
                '$arrival3',
                '$arrival4',
                '$departureTime1',           
                '$departureTime2',
                '$departureTime3',
                '$departureTime4',
                '$arrivalTime1',
                '$arrivalTime2',
                '$arrivalTime3',
                '$arrivalTime4',
                '$flightDuration1',
                '$flightDuration2',
                '$flightDuration3',
                '$flightDuration4',
                '$transit1',
                '$transit2',
                '$transit3',
                '$marketingCareer1',
                '$marketingCareer2',
                '$marketingCareer3',
                '$marketingCareer4',
                '$marketingCareerName1',
                '$marketingCareerName2',
                '$marketingCareerName3',
                '$marketingCareerName4',
                '$marketingFlight1',
                '$marketingFlight2',
                '$marketingFlight3',
                '$marketingFlight4',
                '$operatingCareer1',
                '$operatingCareer2',
                '$operatingCareer3',
                '$operatingCareer4',
                '$operatingFlight1',
                '$operatingFlight2',
                '$operatingFlight3',
                '$operatingFlight4',
                '$departureAirport1',
                '$departureAirport2',
                '$departureAirport3',
                '$departureAirport4',                       
                '$arrivalAirport1',
                '$arrivalAirport2',
                '$arrivalAirport3',
                '$arrivalAirport4',
                '$departureTerminal1',
                '$departureTerminal2',
                '$departureTerminal3',
                '$departureTerminal4',
                '$arrivalTerminal1',
                '$arrivalTerminal2',
                '$arrivalTerminal3',
                '$arrivalTerminal4',
                '$departureLocation1',
                '$departureLocation2',
                '$departureLocation3',
                '$departureLocation4',
                '$arrivalLocation1',
                '$arrivalLocation2', 
                '$arrivalLocation3',
                '$arrivalLocation4',
                '$bookingCode1',
                '$bookingCode2',
                '$bookingCode3',
                '$bookingCode4',
                '$searchId',
                '$resultId',            
                '$createdAt',
                '$uId'           
                
            )";
            $query4 = mysqli_query($conn, $sql4);

            if($query4==true){
                $response['status'] = "success";
            $response['uid'] = "$uId";
                $response['message'] = "Data Saved";
            }else{
                $response['status'] = "error";
                $response['message'] = "Data Not Saved";
            }

        } 
        
        echo json_encode($response);
    }else if($type == 'return'){
        $_POST = $Data['roundData'];
        $system = $_POST["system"];
        $segment = $_POST["segment"];
        if(isset($_POST["SearchID"]) && isset($_POST["ResultID"])){
            $searchId = $_POST["SearchID"];
            $resultId = $_POST["ResultID"];
        }else{
            $searchId = '';
            $resultId = '';
        }
        
        
        if ($segment==1) {
        $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
        $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
        $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
        $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
        $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
        $goDeparture1 = $_POST['segments']['go'][0]["departure"];
        $goDepartureAirport1 = $_POST['segments']['go'][0]["departureAirport"];
        $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
        $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
        $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
        $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];
        $goArrival1 = $_POST['segments']['go'][0]["arrival"];
        $goArrivalAirport1 = str_replace("'", "''",$_POST['segments']['go'][0]["arrivalAirport"]);
        $goArrivalLocation1 = str_replace("'", "''",$_POST['segments']['go'][0]["arrivalLocation"]);
        $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
        $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
        $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];

        $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
        $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
        $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
        $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
        $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
        $backDeparture1 = $_POST['segments']['back'][0]["departure"];
        $backDepartureAirport1 = str_replace("'", "''",$_POST['segments']['back'][0]["departureAirport"]);
        $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
        $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
        $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
        $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
        $backArrival1 = $_POST['segments']['back'][0]["arrival"];
        $backArrivalAirport1 = str_replace("'", "''",$_POST['segments']['back'][0]["arrivalAirport"]);
        $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
        $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
        $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
        $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

        $seat = $_POST['segments']['go'][0]["seat"];


        $sql1 = "INSERT INTO `segment_return_way`( 
            `system`, 
            `segment`,
            `pnr`,                      
            `goMarketingCareer1`, 
            `goMarketingCareerName1`,
            `goMarketingFlight1`,
            `goOperatingCareer1`,
            `goOperatingFlight1`, 
            `goDeparture1`,
            `goArrival1`, 
            `goDepartureAirport1`,
            `goArrivalAirport1`,
            `goDepTerminal1`,
            `goArrTerminal1`,
            `goDepartureLocation1`,
            `goArrivalLocation1`,
            `goDepartureTime1`,
            `goArrivalTime1`,
            `goFlightDuration1`,  
            `goBookingCode1`,
            `backMarketingCareer1`, 
            `backMarketingCareerName1`,
            `backMarketingFlight1`,
            `backOperatingCareer1`,
            `backOperatingFlight1`, 
            `backDeparture1`,
            `backArrival1`, 
            `backDepartureAirport1`,
            `backArrivalAirport1`,
            `backdepTerminal1`,
            `backArrTerminal1`,
            `backDepartureLocation1`,
            `backArrivalLocation1`,
            `backDepartureTime1`,
            `backArrivalTime1`,
            `backFlightDuration1`,  
            `backBookingCode1`,
            `searchId`,
            `resultId`,                      
            `createdAt`,
            `uid`         
        )
        VALUES(
            '$system',
            '$segment',
            '$pnr',
            '$goMarketingCareer1', 
            '$goMarketingCareerName1', 
            '$goMarketingFlight1', 
            '$goOperatingCareer1', 
            '$goOperatingFlight1',
            '$goDeparture1',
            '$goArrival1',
            '$goDepartureAirport1',
            '$goArrivalAirport1',
            '$goDepTerminal1',
            '$goArrTerminal1',
            '$goDepartureLocation1',
            '$goArrivalLocation1',
            '$goDepartureTime1',
            '$goArrivalTime1',
            '$goFlightDuration1',
            '$goBookingCode1', 

            '$backMarketingCareer1',
            '$backMarketingCareerName1', 
            '$backMarketingFlight1',
            '$backOperatingCareer1', 
            '$backOperatingFlight1', 
            '$backDeparture1',
            '$backArrival1',
            '$backDepartureAirport1', 
            '$backArrivalAirport1',      
            '$backdepTerminal1',      
            '$backArrTerminal1',      
            '$backDepartureLocation1', 
            '$backArrivalLocation1',
            '$backDepartureTime1',
            '$backArrivalTime1',
            '$backFlightDuration1',
            '$backBookingCode1',
            '$searchId', 
            '$resultId', 
            '$createdAt',
            '$uId'                     
            
        )";

        $query1 = mysqli_query($conn, $sql1);
        if ($query1==true) {
            $response['status'] = "success";
            $response['uid'] = "$uId";
            $response['message'] = "Data Saved";
        } else {
            $response['status'] = "error";
            $response['message'] = "Data Not Saved";
        }
        }elseif($segment==2){
            // segment 1
            $goTransit1 = $_POST['transit']['go']['transit1'];
            $backTransit1 = $_POST['transit']['back']['transit1'];

            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = str_replace("'", "''",$_POST['segments']['go'][0]["departureAirport"]);
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = str_replace("'", "''",$_POST['segments']['go'][0]["arrivalAirport"]);

            $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];

            $goArrivalLocation1 = $_POST['segments']['go'][0]["arrivalLocation"];
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];

            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = str_replace("'", "''",$_POST['segments']['back'][0]["departureAirport"]);
            $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = str_replace("'", "''",$_POST['segments']['back'][0]["arrivalAirport"]);
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            // segment 2
            $goMarketingCareer2 = $_POST['segments']['go'][1]["marketingcareer"];
            $goMarketingCareerName2 = $_POST['segments']['go'][1]["marketingcareerName"];
            $goMarketingFlight2 = $_POST['segments']['go'][1]["marketingflight"];
            $goOperatingCareer2 = $_POST['segments']['go'][1]["operatingcareer"];
            $goOperatingFlight2 = $_POST['segments']['go'][1]["operatingflight"];
            $goDeparture2 = $_POST['segments']['go'][1]["departure"];
            $goDepartureAirport2 = str_replace("'", "''",$_POST['segments']['go'][1]["departureAirport"]);
            $goDepartureLocation2 = $_POST['segments']['go'][1]["departureLocation"];
            $goDepartureTime2 = $_POST['segments']['go'][1]["departureTime"];
            $goArrival2 = $_POST['segments']['go'][1]["arrival"];
            $goArrivalAirport2 = $_POST['segments']['go'][1]["arrivalAirport"];

            $goDepTerminal2 = $_POST['segments']['go'][0]["departureTerminal"];
            $goArrTerminal2 = $_POST['segments']['go'][0]["arrivalTerminal"];

            $goArrivalLocation2 = $_POST['segments']['go'][1]["arrivalLocation"];
            $goArrivalTime2 = $_POST['segments']['go'][1]["arrivalTime"];
            $goFlightDuration2 = $_POST['segments']['go'][1]["flightduration"];
            $goBookingCode2 = $_POST['segments']['go'][1]["bookingcode"];

            $backMarketingCareer2 = $_POST['segments']['back'][1]["marketingcareer"];
            $backMarketingCareerName2 = $_POST['segments']['back'][1]["marketingcareerName"];
            $backMarketingFlight2 = $_POST['segments']['back'][1]["marketingflight"];
            $backOperatingCareer2 = $_POST['segments']['back'][1]["operatingcareer"];
            $backOperatingFlight2 = $_POST['segments']['back'][1]["operatingflight"];
            $backDeparture2 = $_POST['segments']['back'][1]["departure"];
            $backDepartureAirport2 = str_replace("'", "''",$_POST['segments']['back'][1]["departureAirport"]);

            $backdepTerminal2 = $_POST['segments']['back'][0]["departureTerminal"];
            $backArrTerminal2 = $_POST['segments']['back'][0]["arrivalTerminal"];

            $backDepartureLocation2 = $_POST['segments']['back'][1]["departureLocation"];
            $backDepartureTime2 = $_POST['segments']['back'][1]["departureTime"];
            $backArrival2 = $_POST['segments']['back'][1]["arrival"];
            $backArrivalAirport2 = str_replace("'", "''",$_POST['segments']['back'][1]["arrivalAirport"]);
            $backArrivalLocation2 = $_POST['segments']['back'][1]["arrivalLocation"];
            $backArrivalTime2 = $_POST['segments']['back'][1]["arrivalTime"];
            $backFlightDuration2 = $_POST['segments']['back'][1]["flightduration"];
            $backBookingCode2 = $_POST['segments']['back'][1]["bookingcode"];

            $sql2 = "INSERT INTO `segment_return_way`( 
                `system`, 
                `segment`, 
                 `pnr`,                     
                `goMarketingCareer1`, 
                `goMarketingCareerName1`,
                `goMarketingFlight1`,
                `goOperatingCareer1`,
                `goOperatingFlight1`, 
                `goDeparture1`,
                `goArrival1`, 
                `goDepartureAirport1`,
                `goArrivalAirport1`,
                `goDepTerminal1`,
                `goArrTerminal1`,
                `goDepartureLocation1`,
                `goArrivalLocation1`,
                `goDepartureTime1`,
                `goArrivalTime1`,
                `goFlightDuration1`,  
                `goBookingCode1`,
                `goTransit1`,

                `backMarketingCareer1`, 
                `backMarketingCareerName1`,
                `backMarketingFlight1`,
                `backOperatingCareer1`,
                `backOperatingFlight1`, 
                `backDeparture1`,
                `backArrival1`, 
                `backDepartureAirport1`,
                `backArrivalAirport1`,
                `backdepTerminal1`,
                `backArrTerminal1`,
                `backDepartureLocation1`,
                `backArrivalLocation1`,
                `backDepartureTime1`,
                `backArrivalTime1`,
                `backFlightDuration1`,  
                `backBookingCode1`,
                `backTransit1`,
                `goMarketingCareer2`, 
                `goMarketingCareerName2`,
                `goMarketingFlight2`,
                `goOperatingCareer2`,
                `goOperatingFlight2`, 
                `goDeparture2`,
                `goArrival2`, 
                `goDepartureAirport2`,
                `goArrivalAirport2`,
                `goDepTerminal2`,
                `goArrTerminal2`,
                `goDepartureLocation2`,
                `goArrivalLocation2`,
                `goDepartureTime2`,
                `goArrivalTime2`,
                `goFlightDuration2`,  
                `goBookingCode2`,                        
                `backMarketingCareer2`, 
                `backMarketingCareerName2`,
                `backMarketingFlight2`,
                `backOperatingCareer2`,
                `backOperatingFlight2`, 
                `backDeparture2`,
                `backArrival2`, 
                `backDepartureAirport2`,
                `backArrivalAirport2`,
                `backdepTerminal2`,
                `backArrTerminal2`,
                `backDepartureLocation2`,
                `backArrivalLocation2`,
                `backDepartureTime2`,
                `backArrivalTime2`,
                `backFlightDuration2`,  
                `backBookingCode2`,            
                `searchId`,
                `resultId`,                      
                `createdAt`,
                `uid`         
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$goMarketingCareer1', 
                '$goMarketingCareerName1', 
                '$goMarketingFlight1', 
                '$goOperatingCareer1', 
                '$goOperatingFlight1',
                '$goDeparture1',
                '$goArrival1',
                '$goDepartureAirport1',
                '$goArrivalAirport1',
                '$goDepTerminal1',
                '$goArrTerminal1',
                '$goDepartureLocation1',
                '$goArrivalLocation1',
                '$goDepartureTime1',
                '$goArrivalTime1',
                '$goFlightDuration1',
                '$goBookingCode1',
                '$goTransit1', 

                '$backMarketingCareer1',
                '$backMarketingCareerName1', 
                '$backMarketingFlight1',
                '$backOperatingCareer1', 
                '$backOperatingFlight1', 
                '$backDeparture1',
                '$backArrival1',
                '$backDepartureAirport1', 
                '$backArrivalAirport1', 
                '$backdepTerminal1',
                '$backdepTerminal2',     
                '$backDepartureLocation1', 
                '$backArrivalLocation1',
                '$backDepartureTime1',
                '$backArrivalTime1',
                '$backFlightDuration1',
                '$backBookingCode1',
                '$backTransit1',

                '$goMarketingCareer2', 
                '$goMarketingCareerName2', 
                '$goMarketingFlight2', 
                '$goOperatingCareer2', 
                '$goOperatingFlight2',
                '$goDeparture2',
                '$goArrival2',
                '$goDepartureAirport2',
                '$goArrivalAirport2',
                '$goDepTerminal2',
                '$goArrTerminal2',
                '$goDepartureLocation2',
                '$goArrivalLocation2',
                '$goDepartureTime2',
                '$goArrivalTime2',
                '$goFlightDuration2',
                '$goBookingCode2',

                '$backMarketingCareer2',
                '$backMarketingCareerName2', 
                '$backMarketingFlight2',
                '$backOperatingCareer2', 
                '$backOperatingFlight2', 
                '$backDeparture2',
                '$backArrival2',
                '$backDepartureAirport2', 
                '$backArrivalAirport2', 
                '$backdepTerminal2',
                '$backArrTerminal2',     
                '$backDepartureLocation2', 
                '$backArrivalLocation2',
                '$backDepartureTime2',
                '$backArrivalTime2',
                '$backFlightDuration2',
                '$backBookingCode2',

                '$searchId', 
                '$resultId', 
                '$createdAt',
                '$uId'                      
                
            )";

            $query2 = mysqli_query($conn, $sql2);
            if($query2==true){
                $response['status'] = "success";
            $response['uid'] = "$uId";
                $response['message'] = "Data Saved";
        }else{
            $response['status'] = "error";
            $response['message'] = "Data Not Saved";
        }

        }elseif($segment==3){
            // segment 1
            $goTransit1 = $_POST['transit']['go']['transit1'];
            $goTransit2 = $_POST['transit']['go']['transit1'];
            $backTransit1 = $_POST['transit']['back']['transit1'];
            $backTransit2 = $_POST['transit']['back']['transit1'];

            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = str_replace("'", "''",$_POST['segments']['go'][0]["departureAirport"]);
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = str_replace("'", "''",$_POST['segments']['go'][0]["arrivalAirport"]);
            $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];
            $goArrivalLocation1 = $_POST['segments']['go'][0]["arrivalLocation"];
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];

            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = str_replace("'", "''",$_POST['segments']['back'][0]["departureAirport"]);
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = str_replace("'", "''",$_POST['segments']['back'][0]["arrivalAirport"]);
            $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            // segment 2
            $goMarketingCareer2 = $_POST['segments']['go'][1]["marketingcareer"];
            $goMarketingCareerName2 = $_POST['segments']['go'][1]["marketingcareerName"];
            $goMarketingFlight2 = $_POST['segments']['go'][1]["marketingflight"];
            $goOperatingCareer2 = $_POST['segments']['go'][1]["operatingcareer"];
            $goOperatingFlight2 = $_POST['segments']['go'][1]["operatingflight"];
            $goDeparture2 = $_POST['segments']['go'][1]["departure"];
            $goDepartureAirport2 = str_replace("'", "''",$_POST['segments']['go'][1]["departureAirport"]);
            $goDepartureLocation2 = $_POST['segments']['go'][1]["departureLocation"];
            $goDepartureTime2 = $_POST['segments']['go'][1]["departureTime"];
            $goArrival2 = $_POST['segments']['go'][1]["arrival"];
            $goArrivalAirport2 = str_replace("'", "''",$_POST['segments']['go'][1]["arrivalAirport"]);
            $goDepTerminal2 = $_POST['segments']['go'][1]["departureTerminal"];
            $goArrTerminal2 = $_POST['segments']['go'][1]["arrivalTerminal"];
            $goArrivalLocation2 = $_POST['segments']['go'][1]["arrivalLocation"];
            $goArrivalTime2 = $_POST['segments']['go'][1]["arrivalTime"];
            $goFlightDuration2 = $_POST['segments']['go'][1]["flightduration"];
            $goBookingCode2 = $_POST['segments']['go'][1]["bookingcode"];

            $backMarketingCareer2 = $_POST['segments']['back'][1]["marketingcareer"];
            $backMarketingCareerName2 = $_POST['segments']['back'][1]["marketingcareerName"];
            $backMarketingFlight2 = $_POST['segments']['back'][1]["marketingflight"];
            $backOperatingCareer2 = $_POST['segments']['back'][1]["operatingcareer"];
            $backOperatingFlight2 = $_POST['segments']['back'][1]["operatingflight"];
            $backDeparture2 = $_POST['segments']['back'][1]["departure"];
            $backDepartureAirport2 = str_replace("'", "''",$_POST['segments']['back'][1]["departureAirport"]);
            $backDepartureLocation2 = $_POST['segments']['back'][1]["departureLocation"];
            $backDepartureTime2 = $_POST['segments']['back'][1]["departureTime"];
            $backArrival2 = $_POST['segments']['back'][1]["arrival"];
            $backArrivalAirport2 = str_replace("'", "''",$_POST['segments']['back'][1]["arrivalAirport"]);
            $backdepTerminal2 = $_POST['segments']['back'][1]["departureTerminal"];
            $backArrTerminal2 = $_POST['segments']['back'][1]["arrivalTerminal"];
            $backArrivalLocation2 = $_POST['segments']['back'][1]["arrivalLocation"];
            $backArrivalTime2 = $_POST['segments']['back'][1]["arrivalTime"];
            $backFlightDuration2 = $_POST['segments']['back'][1]["flightduration"];
            $backBookingCode2 = $_POST['segments']['back'][1]["bookingcode"];
            
            // segment 3
            $goMarketingCareer3 = $_POST['segments']['go'][2]["marketingcareer"];
            $goMarketingCareerName3 = $_POST['segments']['go'][2]["marketingcareerName"];
            $goMarketingFlight3 = $_POST['segments']['go'][2]["marketingflight"];
            $goOperatingCareer3 = $_POST['segments']['go'][2]["operatingcareer"];
            $goOperatingFlight3 = $_POST['segments']['go'][2]["operatingflight"];
            $goDeparture3 = $_POST['segments']['go'][2]["departure"];
            $goDepartureAirport3 = str_replace("'", "''",$_POST['segments']['go'][2]["departureAirport"]);
            $goDepartureLocation3 = $_POST['segments']['go'][2]["departureLocation"];
            $goDepartureTime3 = $_POST['segments']['go'][2]["departureTime"];
            $goArrival3 = $_POST['segments']['go'][2]["arrival"];
            $goArrivalAirport3 = str_replace("'", "''",$_POST['segments']['go'][2]["arrivalAirport"]);
            $goDepTerminal3 = $_POST['segments']['go'][2]["departureTerminal"];
            $goArrTerminal3 = $_POST['segments']['go'][2]["arrivalTerminal"];
            $goArrivalLocation3 = $_POST['segments']['go'][2]["arrivalLocation"];
            $goArrivalTime3 = $_POST['segments']['go'][2]["arrivalTime"];
            $goFlightDuration3 = $_POST['segments']['go'][2]["flightduration"];
            $goBookingCode3 = $_POST['segments']['go'][2]["bookingcode"];

            $backMarketingCareer3 = $_POST['segments']['back'][2]["marketingcareer"];
            $backMarketingCareerName3 = $_POST['segments']['back'][2]["marketingcareerName"];
            $backMarketingFlight3 = $_POST['segments']['back'][2]["marketingflight"];
            $backOperatingCareer3 = $_POST['segments']['back'][2]["operatingcareer"];
            $backOperatingFlight3 = $_POST['segments']['back'][2]["operatingflight"];
            $backDeparture3 = $_POST['segments']['back'][2]["departure"];
            $backDepartureAirport3 = str_replace("'", "''",$_POST['segments']['back'][2]["departureAirport"]);
            $backDepartureLocation3 = $_POST['segments']['back'][2]["departureLocation"];
            $backDepartureTime3 = $_POST['segments']['back'][2]["departureTime"];
            $backArrival3 = $_POST['segments']['back'][2]["arrival"];
            $backArrivalAirport3 = str_replace("'", "''",$_POST['segments']['back'][2]["arrivalAirport"]);
            $backdepTerminal3 = $_POST['segments']['back'][2]["departureTerminal"];
            $backArrTerminal3 = $_POST['segments']['back'][2]["arrivalTerminal"];
            $backArrivalLocation3 = $_POST['segments']['back'][2]["arrivalLocation"];
            $backArrivalTime3 = $_POST['segments']['back'][2]["arrivalTime"];
            $backFlightDuration3 = $_POST['segments']['back'][2]["flightduration"];
            $backBookingCode3 = $_POST['segments']['back'][2]["bookingcode"];

            $sql3 = "INSERT INTO `segment_return_way`( 
                `system`, 
                `segment`, 
                `pnr`,                     
                `goMarketingCareer1`, 
                `goMarketingCareerName1`,
                `goMarketingFlight1`,
                `goOperatingCareer1`,
                `goOperatingFlight1`, 
                `goDeparture1`,
                `goArrival1`, 
                `goDepartureAirport1`,
                `goArrivalAirport1`,
                `goDepTerminal1`,
                `goArrTerminal1`,
                `goDepartureLocation1`,
                `goArrivalLocation1`,
                `goDepartureTime1`,
                `goArrivalTime1`,
                `goFlightDuration1`,  
                `goBookingCode1`,
                `goTransit1`,
                `goTransit2`,

                `backMarketingCareer1`, 
                `backMarketingCareerName1`,
                `backMarketingFlight1`,
                `backOperatingCareer1`,
                `backOperatingFlight1`, 
                `backDeparture1`,
                `backArrival1`, 
                `backDepartureAirport1`,
                `backArrivalAirport1`,
                `backdepTerminal1`,
                `backArrTerminal1`,
                `backDepartureLocation1`,
                `backArrivalLocation1`,
                `backDepartureTime1`,
                `backArrivalTime1`,
                `backFlightDuration1`,  
                `backBookingCode1`,
                `backTransit1`,
                `backTransit2`,

                `goMarketingCareer2`, 
                `goMarketingCareerName2`,
                `goMarketingFlight2`,
                `goOperatingCareer2`,
                `goOperatingFlight2`, 
                `goDeparture2`,
                `goArrival2`, 
                `goDepartureAirport2`,
                `goArrivalAirport2`,
                `goDepTerminal2`,
                `goArrTerminal2`,
                `goDepartureLocation2`,
                `goArrivalLocation2`,
                `goDepartureTime2`,
                `goArrivalTime2`,
                `goFlightDuration2`,  
                `goBookingCode2`,            
                
                `backMarketingCareer2`, 
                `backMarketingCareerName2`,
                `backMarketingFlight2`,
                `backOperatingCareer2`,
                `backOperatingFlight2`, 
                `backDeparture2`,
                `backArrival2`, 
                `backDepartureAirport2`,
                `backArrivalAirport2`,
                `backdepTerminal2`,
                `backArrTerminal2`,
                `backDepartureLocation2`,
                `backArrivalLocation2`,
                `backDepartureTime2`,
                `backArrivalTime2`,
                `backFlightDuration2`,  
                `backBookingCode2`, 

                `goMarketingCareer3`, 
                `goMarketingCareerName3`,
                `goMarketingFlight3`,
                `goOperatingCareer3`,
                `goOperatingFlight3`, 
                `goDeparture3`,
                `goArrival3`, 
                `goDepartureAirport3`,
                `goArrivalAirport3`,
                `goDepTerminal3`,
                `goArrTerminal3`,
                `goDepartureLocation3`,
                `goArrivalLocation3`,
                `goDepartureTime3`,
                `goArrivalTime3`,
                `goFlightDuration3`,  
                `goBookingCode3`,            
                
                `backMarketingCareer3`, 
                `backMarketingCareerName3`,
                `backMarketingFlight3`,
                `backOperatingCareer3`,
                `backOperatingFlight3`, 
                `backDeparture3`,
                `backArrival3`, 
                `backDepartureAirport3`,
                `backArrivalAirport3`,
                `backdepTerminal3`,
                `backArrTerminal3`,
                `backDepartureLocation3`,
                `backArrivalLocation3`,
                `backDepartureTime3`,
                `backArrivalTime3`,
                `backFlightDuration3`,  
                `backBookingCode3`,              

                `searchId`,
                `resultId`,                      
                `createdAt`,
                `uid`         
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$goMarketingCareer1', 
                '$goMarketingCareerName1', 
                '$goMarketingFlight1', 
                '$goOperatingCareer1', 
                '$goOperatingFlight1',
                '$goDeparture1',
                '$goArrival1',
                '$goDepartureAirport1',
                '$goArrivalAirport1',
                '$goDepTerminal1',
                '$goArrTerminal2',
                '$goDepartureLocation1',
                '$goArrivalLocation1',
                '$goDepartureTime1',
                '$goArrivalTime1',
                '$goFlightDuration1',
                '$goBookingCode1',
                '$goTransit1', 
                '$goTransit2', 

                '$backMarketingCareer1',
                '$backMarketingCareerName1', 
                '$backMarketingFlight1',
                '$backOperatingCareer1', 
                '$backOperatingFlight1', 
                '$backDeparture1',
                '$backArrival1',
                '$backDepartureAirport1', 
                '$backArrivalAirport1',
                '$backdepTerminal1',
                '$backArrTerminal1',      
                '$backDepartureLocation1', 
                '$backArrivalLocation1',
                '$backDepartureTime1',
                '$backArrivalTime1',
                '$backFlightDuration1',
                '$backBookingCode1',
                '$backTransit1',
                '$backTransit2',

                '$goMarketingCareer2', 
                '$goMarketingCareerName2', 
                '$goMarketingFlight2', 
                '$goOperatingCareer2', 
                '$goOperatingFlight2',
                '$goDeparture2',
                '$goArrival2',
                '$goDepartureAirport2',
                '$goArrivalAirport2',
                '$goDepTerminal2',
                '$goArrTerminal2',
                '$goDepartureLocation2',
                '$goArrivalLocation2',
                '$goDepartureTime2',
                '$goArrivalTime2',
                '$goFlightDuration2',
                '$goBookingCode2',

                '$backMarketingCareer2',
                '$backMarketingCareerName2', 
                '$backMarketingFlight2',
                '$backOperatingCareer2', 
                '$backOperatingFlight2', 
                '$backDeparture2',
                '$backArrival2',
                '$backDepartureAirport2', 
                '$backArrivalAirport2',
                '$backdepTerminal2',
                '$backArrTerminal2',      
                '$backDepartureLocation2', 
                '$backArrivalLocation2',
                '$backDepartureTime2',
                '$backArrivalTime2',
                '$backFlightDuration2',
                '$backBookingCode2',

                '$goMarketingCareer3', 
                '$goMarketingCareerName3', 
                '$goMarketingFlight3', 
                '$goOperatingCareer3', 
                '$goOperatingFlight3',
                '$goDeparture3',
                '$goArrival3',
                '$goDepartureAirport3',
                '$goArrivalAirport3',
                '$goDepTerminal3',
                '$goArrTerminal3',
                '$goDepartureLocation3',
                '$goArrivalLocation3',
                '$goDepartureTime3',
                '$goArrivalTime3',
                '$goFlightDuration3',
                '$goBookingCode3',

                '$backMarketingCareer3',
                '$backMarketingCareerName3', 
                '$backMarketingFlight3',
                '$backOperatingCareer3', 
                '$backOperatingFlight3', 
                '$backDeparture3',
                '$backArrival3',
                '$backDepartureAirport3', 
                '$backArrivalAirport3',
                '$backdepTerminal3',
                '$backArrTerminal3',      
                '$backDepartureLocation3', 
                '$backArrivalLocation3',
                '$backDepartureTime3',
                '$backArrivalTime3',
                '$backFlightDuration3',
                '$backBookingCode3',

                '$searchId', 
                '$resultId', 
                '$createdAt',
                '$uId'                     
                
            )";

            $query3 = mysqli_query($conn, $sql3);
            if($query3==true){
                $response['status'] = "success";
            $response['uid'] = "$uId";
                $response['message'] = "Data Saved";
            }else{
                $response['status'] = "error";
                $response['message'] = "Data Not Saved";
            }

        }elseif($segment==12){
            //go 1 details
            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = $_POST['segments']['go'][0]["departureAirport"];
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = $_POST['segments']['go'][0]["arrivalAirport"];
            $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];

            $goArrivalLocation1 = $_POST['segments']['go'][0]["arrivalLocation"];
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];

            //back 1 details
            $backTransit1 = $_POST['transit']['back']['transit1'];
            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = $_POST['segments']['back'][0]["departureAirport"];
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = $_POST['segments']['back'][0]["arrivalAirport"];
            $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            //back 2 details
            $backMarketingCareer2 = $_POST['segments']['back'][1]["marketingcareer"];
            $backMarketingCareerName2 = $_POST['segments']['back'][1]["marketingcareerName"];
            $backMarketingFlight2 = $_POST['segments']['back'][1]["marketingflight"];
            $backOperatingCareer2 = $_POST['segments']['back'][1]["operatingcareer"];
            $backOperatingFlight2 = $_POST['segments']['back'][1]["operatingflight"];
            $backDeparture2 = $_POST['segments']['back'][1]["departure"];
            $backDepartureAirport2 = $_POST['segments']['back'][1]["departureAirport"];
            $backDepartureLocation2 = $_POST['segments']['back'][1]["departureLocation"];
            $backDepartureTime2 = $_POST['segments']['back'][1]["departureTime"];
            $backArrival2 = $_POST['segments']['back'][1]["arrival"];
            $backArrivalAirport2 = $_POST['segments']['back'][1]["arrivalAirport"];
            $backdepTerminal2 = $_POST['segments']['back'][1]["departureTerminal"];
            $backArrTerminal2 = $_POST['segments']['back'][1]["arrivalTerminal"];
            $backArrivalLocation2 = $_POST['segments']['back'][1]["arrivalLocation"];
            $backArrivalTime2 = $_POST['segments']['back'][1]["arrivalTime"];
            $backFlightDuration2 = $_POST['segments']['back'][1]["flightduration"];
            $backBookingCode2 = $_POST['segments']['back'][1]["bookingcode"];

            $sql4 = "INSERT INTO `segment_return_way`( 
                `system`, 
                `segment`,
                `pnr`,                      
                `goMarketingCareer1`, 
                `goMarketingCareerName1`,
                `goMarketingFlight1`,
                `goOperatingCareer1`,
                `goOperatingFlight1`, 
                `goDeparture1`,
                `goArrival1`, 
                `goDepartureAirport1`,
                `goArrivalAirport1`,
                `goDepTerminal1`,
                `goArrTerminal1`,
                `goDepartureLocation1`,
                `goArrivalLocation1`,
                `goDepartureTime1`,
                `goArrivalTime1`,
                `goFlightDuration1`,  
                `goBookingCode1`,
                `backMarketingCareer1`, 
                `backMarketingCareerName1`,
                `backMarketingFlight1`,
                `backOperatingCareer1`,
                `backOperatingFlight1`, 
                `backDeparture1`,
                `backArrival1`, 
                `backDepartureAirport1`,
                `backArrivalAirport1`,
                `backdepTerminal1`,
                `backArrTerminal1`,
                `backDepartureLocation1`,
                `backArrivalLocation1`,
                `backDepartureTime1`,
                `backArrivalTime1`,
                `backFlightDuration1`,  
                `backBookingCode1`,
                `backTransit1`,                
                `backMarketingCareer2`, 
                `backMarketingCareerName2`,
                `backMarketingFlight2`,
                `backOperatingCareer2`,
                `backOperatingFlight2`, 
                `backDeparture2`,
                `backArrival2`, 
                `backDepartureAirport2`,
                `backArrivalAirport2`,
                `backdepTerminal2`,
                `backArrTerminal2`,
                `backDepartureLocation2`,
                `backArrivalLocation2`,
                `backDepartureTime2`,
                `backArrivalTime2`,
                `backFlightDuration2`,  
                `backBookingCode2`,
                `searchId`,
                `resultId`,                      
                `createdAt`,
                `uid`         
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$goMarketingCareer1', 
                '$goMarketingCareerName1', 
                '$goMarketingFlight1', 
                '$goOperatingCareer1', 
                '$goOperatingFlight1',
                '$goDeparture1',
                '$goArrival1',
                '$goDepartureAirport1',
                '$goArrivalAirport1',
                '$goDepTerminal1',
                '$goArrTerminal1',
                '$goDepartureLocation1',
                '$goArrivalLocation1',
                '$goDepartureTime1',
                '$goArrivalTime1',
                '$goFlightDuration1',
                '$goBookingCode1',   

                '$backMarketingCareer1',
                '$backMarketingCareerName1', 
                '$backMarketingFlight1',
                '$backOperatingCareer1', 
                '$backOperatingFlight1', 
                '$backDeparture1',
                '$backArrival1',
                '$backDepartureAirport1', 
                '$backArrivalAirport1',
                '$backdepTerminal1',
                '$backArrTerminal1',      
                '$backDepartureLocation1', 
                '$backArrivalLocation1',
                '$backDepartureTime1',
                '$backArrivalTime1',
                '$backFlightDuration1',
                '$backBookingCode1',
                '$backTransit1',           

                '$backMarketingCareer2',
                '$backMarketingCareerName2', 
                '$backMarketingFlight2',
                '$backOperatingCareer2', 
                '$backOperatingFlight2', 
                '$backDeparture2',
                '$backArrival2',
                '$backDepartureAirport2', 
                '$backArrivalAirport2',
                '$backdepTerminal2',
                '$backArrTerminal2',      
                '$backDepartureLocation2', 
                '$backArrivalLocation2',
                '$backDepartureTime2',
                '$backArrivalTime2',
                '$backFlightDuration2',
                '$backBookingCode2',

                '$searchId', 
                '$resultId', 
                '$createdAt',
                '$uId'                      
                
            )";

            $query4 = mysqli_query($conn, $sql4);
            if($query4 == true){
            $response['status'] = "success";
            $response['uid'] = "$uId";
            $response['message'] = "Data Saved";
        }else{
            $response['status'] = "error";
            $response['message'] = "Data Not Saved";
        }

        }elseif($segment==21){
            //go 1 details
            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = $_POST['segments']['go'][0]["departureAirport"];
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = $_POST['segments']['go'][0]["arrivalAirport"];
            $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];
            $goArrivalLocation1 = $_POST['segments']['go'][0]["arrivalLocation"];
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];
            $goTransit1 = $_POST['transit']['go']['transit1'];

            // go 2 details
            $goMarketingCareer2 = $_POST['segments']['go'][1]["marketingcareer"];
            $goMarketingCareerName2 = $_POST['segments']['go'][1]["marketingcareerName"];
            $goMarketingFlight2 = $_POST['segments']['go'][1]["marketingflight"];
            $goOperatingCareer2 = $_POST['segments']['go'][1]["operatingcareer"];
            $goOperatingFlight2 = $_POST['segments']['go'][1]["operatingflight"];
            $goDeparture2 = $_POST['segments']['go'][1]["departure"];
            $goDepartureAirport2 = $_POST['segments']['go'][1]["departureAirport"];
            $goDepartureLocation2 = $_POST['segments']['go'][1]["departureLocation"];
            $goDepartureTime2 = $_POST['segments']['go'][1]["departureTime"];
            $goArrival2 = $_POST['segments']['go'][1]["arrival"];
            $goArrivalAirport2 = $_POST['segments']['go'][1]["arrivalAirport"];
            $goDepTerminal2 = $_POST['segments']['go'][1]["departureTerminal"];
            $goArrTerminal2 = $_POST['segments']['go'][1]["arrivalTerminal"];
            $goArrivalLocation2 = $_POST['segments']['go'][1]["arrivalLocation"];
            $goArrivalTime2 = $_POST['segments']['go'][1]["arrivalTime"];
            $goFlightDuration2 = $_POST['segments']['go'][1]["flightduration"];
            $goBookingCode2 = $_POST['segments']['go'][1]["bookingcode"];
            

            //back 1 details
            $backTransit1 = $_POST['transit']['back']['transit1'];
            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = $_POST['segments']['back'][0]["departureAirport"];
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = $_POST['segments']['back'][0]["arrivalAirport"];
            $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            $sql5 = "INSERT INTO `segment_return_way`( 
                `system`, 
                `segment`,
                `pnr`,                      
                `goMarketingCareer1`, 
                `goMarketingCareerName1`,
                `goMarketingFlight1`,
                `goOperatingCareer1`,
                `goOperatingFlight1`, 
                `goDeparture1`,
                `goArrival1`, 
                `goDepartureAirport1`,
                `goArrivalAirport1`,
                `goDepTerminal1`,
                `goArrTerminal1`,
                `goDepartureLocation1`,
                `goArrivalLocation1`,
                `goDepartureTime1`,
                `goArrivalTime1`,
                `goFlightDuration1`,  
                `goBookingCode1`,            
                `goTransit1`,            

                `goMarketingCareer2`, 
                `goMarketingCareerName2`,
                `goMarketingFlight2`,
                `goOperatingCareer2`,
                `goOperatingFlight2`, 
                `goDeparture2`,
                `goArrival2`, 
                `goDepartureAirport2`,
                `goArrivalAirport2`,
                `goDepTerminal2`,
                `goArrTerminal2`,
                `goDepartureLocation2`,
                `goArrivalLocation2`,
                `goDepartureTime2`,
                `goArrivalTime2`,
                `goFlightDuration2`,  
                `goBookingCode2`,          
                
                `backMarketingCareer1`, 
                `backMarketingCareerName1`,
                `backMarketingFlight1`,
                `backOperatingCareer1`,
                `backOperatingFlight1`, 
                `backDeparture1`,
                `backArrival1`, 
                `backDepartureAirport1`,
                `backArrivalAirport1`,
                `backdepTerminal1`,
                `backArrTerminal1`,
                `backDepartureLocation1`,
                `backArrivalLocation1`,
                `backDepartureTime1`,
                `backArrivalTime1`,
                `backFlightDuration1`,  
                `backBookingCode1`,     

                `searchId`,
                `resultId`,                      
                `createdAt`,
                `uid`         
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$goMarketingCareer1', 
                '$goMarketingCareerName1', 
                '$goMarketingFlight1', 
                '$goOperatingCareer1', 
                '$goOperatingFlight1',
                '$goDeparture1',
                '$goArrival1',
                '$goDepartureAirport1',
                '$goArrivalAirport1',
                '$goDepTerminal1',
                '$goArrTerminal1',
                '$goDepartureLocation1',
                '$goArrivalLocation1',
                '$goDepartureTime1',
                '$goArrivalTime1',
                '$goFlightDuration1',
                '$goBookingCode1',            
                '$goTransit1',            

                '$goMarketingCareer2', 
                '$goMarketingCareerName2', 
                '$goMarketingFlight2', 
                '$goOperatingCareer2', 
                '$goOperatingFlight2',
                '$goDeparture2',
                '$goArrival2',
                '$goDepartureAirport2',
                '$goArrivalAirport2',
                '$goDepTerminal2',
                '$goArrTerminal2',
                '$goDepartureLocation2',
                '$goArrivalLocation2',
                '$goDepartureTime2',
                '$goArrivalTime2',
                '$goFlightDuration2',
                '$goBookingCode2',           

                '$backMarketingCareer1',
                '$backMarketingCareerName1', 
                '$backMarketingFlight1',
                '$backOperatingCareer1', 
                '$backOperatingFlight1', 
                '$backDeparture1',
                '$backArrival1',
                '$backDepartureAirport1', 
                '$backArrivalAirport1', 
                '$backdepTerminal1',
                '$backArrTerminal1',     
                '$backDepartureLocation1', 
                '$backArrivalLocation1',
                '$backDepartureTime1',
                '$backArrivalTime1',
                '$backFlightDuration1',
                '$backBookingCode1',              

                '$searchId', 
                '$resultId', 
                '$createdAt',
                '$uId'                      
                
            )";

            $query5 = mysqli_query($conn, $sql5);
            if($query5 == true){
                $response['status'] = "success";
            $response['uid'] = "$uId";           
                $response['message'] = "Data Saved";
        }else{
            $response['status'] = "error";
            $response['message'] = "Data Not Saved";
        }

        }

        echo json_encode($response);
        
    }

    
}