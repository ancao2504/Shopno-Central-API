<?php

include_once('../config.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$_POST = json_decode(file_get_contents('php://input'), true);

    $adult = $_POST['adultCount'];
    $child = $_POST['childCount'];
    $infants =  $_POST['infantCount'];
    $agentId = $_POST['agentId'];
    $BookingId = $_POST['bookingId'];
    $createdTimer = date('Y-m-d H:i:s');


    if($adult > 0 && $child> 0 && $infants> 0){

    for($x = 0 ; $x < $adult; $x++){

        $paxId ="";
        $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                $number= (int)$outputString + 1;
                $paxId = "FFP$number"; 								
            }
        } else {
                $paxId ="FFP1000";
        }

        ${'afName'.$x} = strtoupper($_POST['adult'][$x]["afName"]);
        ${'alName'.$x} = strtoupper($_POST['adult'][$x]["alName"]);
        ${'agender'.$x} = $_POST['adult'][$x]["agender"];
        ${'adob'.$x} = $_POST['adult'][$x]["adob"];
        ${'apassNo'.$x} = strtoupper($_POST['adult'][$x]["apassNo"]);
        ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
        ${'apassNation'.$x} = strtoupper($_POST['adult'][$x]["apassNation"]);

        ${'sql'.$x} = "INSERT INTO `passengers`(
                        `paxId`,
                       `agentId`,
                        `bookingId`,
                        `fName`,
                        `lName`,
                        `dob`,
                        `gender`,
                        `type`,
                        `passNation`,
                        `passNo`,
                        `passEx`,
                         `created`
                    )
                VALUES('$paxId','$agentId','$BookingId','${'afName'.$x}','${'alName'.$x}','${'adob'.$x}','${'agender'.$x}','ADT','${'apassNation'.$x}',
                    '${'apassNo'.$x}','${'apassEx'.$x}','$createdTimer')";

        if ($conn->query(${'sql'.$x}) === TRUE) {
            $response['status']="success";
            $response['message']="Traveler Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Traveler Added Failed";
        }
              
    }


    for($x = 0 ; $x < $child; $x++){

        $paxId ="";
        $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                $number= (int)$outputString + 1;
                $paxId = "FFP$number"; 								
            }
        } else {
                $paxId ="FFP1000";
        }

        ${'cfName'.$x} = strtoupper($_POST['child'][$x]["cfName"]);
        ${'clName'.$x} = strtoupper($_POST['child'][$x]["clName"]);
        ${'cgender'.$x} = $_POST['child'][$x]["cgender"];
        ${'cdob'.$x} = $_POST['child'][$x]["cdob"];
        ${'cpassNo'.$x} = strtoupper($_POST['child'][$x]["cpassNo"]);
        ${'cpassEx'.$x} = $_POST['child'][$x]["cpassEx"];
        ${'cpassNation'.$x} = strtoupper($_POST['child'][$x]["cpassNation"]);

        ${'sql'.$x} = "INSERT INTO `passengers`(
                        `paxId`,`agentId`,`bookingId`,`fName`,`lName`, `dob`,`gender`,`type`,`passNation`,`passNo`,`passEx`,`created`)
                VALUES('$paxId','$agentId','$BookingId','${'cfName'.$x}','${'clName'.$x}','${'cdob'.$x}','${'cgender'.$x}','CNN','${'cpassNation'.$x}',
                    '${'cpassNo'.$x}','${'cpassEx'.$x}','$createdTimer')";

        if ($conn->query(${'sql'.$x}) === TRUE) {
            $response['status']="success";
            $response['message']="Traveler Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Traveler Added Failed";
        }


        
    }


    for($x = 0 ; $x < $infants; $x++){

        $paxId ="";
        $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                $number= (int)$outputString + 1;
                $paxId = "FFP$number"; 								
            }
        } else {
                $paxId ="FFP1000";
        }

        ${'ifName'.$x} = strtoupper($_POST['infant'][$x]["ifName"]);
        ${'ilName'.$x} = strtoupper($_POST['infant'][$x]["ilName"]);
        ${'igender'.$x} = $_POST['infant'][$x]["igender"];
        ${'idob'.$x} = $_POST['infant'][$x]["idob"];
        ${'ipassNo'.$x} = strtoupper($_POST['infant'][$x]["ipassNo"]);
        ${'ipassEx'.$x} = $_POST['infant'][$x]["ipassEx"];
        ${'ipassNation'.$x} = strtoupper($_POST['infant'][$x]["ipassNation"]);

        ${'sql'.$x} = "INSERT INTO `passengers`(
                        `paxId`,`agentId`,`bookingId`,`fName`,`lName`,`dob`,`gender`,`type`,`passNation`,`passNo`,`passEx`,`created`)
                VALUES('$paxId','$agentId','$BookingId','${'ifName'.$x}','${'ilName'.$x}','${'idob'.$x}','${'igender'.$x}','INF','${'ipassNation'.$x}',
                    '${'ipassNo'.$x}','${'ipassEx'.$x}','$createdTimer')";

        if ($conn->query(${'sql'.$x}) === TRUE) {
            $response['status']="success";
            $response['message']="Traveler Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Traveler Added Failed";
        }




                
    }
    echo json_encode($response);



        
    }else if($adult > 0 && $child > 0){


    for($x = 0 ; $x < $adult; $x++){
        $paxId ="";
        $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                $number= (int)$outputString + 1;
                $paxId = "FFP$number"; 								
            }
        } else {
                $paxId ="FFP1000";
        }

        ${'afName'.$x} = strtoupper($_POST['adult'][$x]["afName"]);
        ${'alName'.$x} = strtoupper($_POST['adult'][$x]["alName"]);
        ${'agender'.$x} = $_POST['adult'][$x]["agender"];
        ${'adob'.$x} = $_POST['adult'][$x]["adob"];
        ${'apassNo'.$x} = strtoupper($_POST['adult'][$x]["apassNo"]);
        ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
        ${'apassNation'.$x} = strtoupper($_POST['adult'][$x]["apassNation"]);

        ${'sql'.$x} = "INSERT INTO `passengers`(
            `paxId`,
                        `agentId`,
                        `bookingId`,
                        `fName`,
                        `lName`,
                        `dob`,
                        `gender`,
                        `type`,
                        `passNation`,
                        `passNo`,
                        `passEx`,
                         `created`
                        
                    )
               VALUES('$paxId','$agentId','$BookingId','${'afName'.$x}','${'alName'.$x}','${'adob'.$x}','${'agender'.$x}','ADT','${'apassNation'.$x}',
                    '${'apassNo'.$x}','${'apassEx'.$x}','$createdTimer')";

        if ($conn->query(${'sql'.$x}) === TRUE) {
            $response['status']="success";
            $response['message']="Traveler Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Traveler Added Failed";
        }


      
                
    }

    
    for($x = 0 ; $x < $child; $x++){

        $paxId ="";
        $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                $number= (int)$outputString + 1;
                $paxId = "FFP$number"; 								
            }
        } else {
                $paxId ="FFP1000";
        }

        ${'cfName'.$x} = strtoupper($_POST['child'][$x]["cfName"]);
        ${'clName'.$x} = strtoupper($_POST['child'][$x]["clName"]);
        ${'cgender'.$x} = $_POST['child'][$x]["cgender"];
        ${'cdob'.$x} = $_POST['child'][$x]["cdob"];
        ${'cpassNo'.$x} = strtoupper($_POST['child'][$x]["cpassNo"]);
        ${'cpassEx'.$x} = $_POST['child'][$x]["cpassEx"];
        ${'cpassNation'.$x} = strtoupper($_POST['child'][$x]["cpassNation"]);

        ${'sql'.$x} = "INSERT INTO `passengers`(
                        `paxId`,
                        `agentId`,
                        `bookingId`,
                        `fName`,
                        `lName`,
                        `dob`,
                        `gender`,
                        `type`,
                        `passNation`,
                        `passNo`,
                        `passEx`,
                        `created`
                    )
                VALUES('$paxId','$agentId','$BookingId','${'cfName'.$x}','${'clName'.$x}','${'cdob'.$x}','${'cgender'.$x}','CNN','${'cpassNation'.$x}',
                    '${'cpassNo'.$x}','${'cpassEx'.$x}','$createdTimer')";

        if ($conn->query(${'sql'.$x}) === TRUE) {
            $response['status']="success";
            $response['message']="Traveler Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Traveler Added Failed";
        }


        
    }
    echo json_encode($response);

    }else if($adult > 0 && $infants > 0){


    for($x = 0 ; $x < $adult; $x++){
        $paxId ="";
        $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                $number= (int)$outputString + 1;
                $paxId = "FFP$number"; 								
            }
        } else {
                $paxId ="FFP1000";
        }

        ${'afName'.$x} = strtoupper($_POST['adult'][$x]["afName"]);
        ${'alName'.$x} = strtoupper($_POST['adult'][$x]["alName"]);
        ${'agender'.$x} = $_POST['adult'][$x]["agender"];
        ${'adob'.$x} = $_POST['adult'][$x]["adob"];
        ${'apassNo'.$x} = strtoupper($_POST['adult'][$x]["apassNo"]);
        ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
        ${'apassNation'.$x} = strtoupper($_POST['adult'][$x]["apassNation"]);

        ${'sql'.$x} = "INSERT INTO `passengers`(
                        `paxId`,
                        `agentId`,
                        `bookingId`,
                        `fName`,
                        `lName`,
                        `dob`,
                        `gender`,
                        `type`,
                        `passNation`,
                        `passNo`,
                        `passEx`,
                         `created`
                    )
                VALUES('$paxId','$agentId','$BookingId','${'afName'.$x}','${'alName'.$x}','${'adob'.$x}','${'agender'.$x}','ADT','${'apassNation'.$x}',
                    '${'apassNo'.$x}','${'apassEx'.$x}','$createdTimer')";

        if ($conn->query(${'sql'.$x}) === TRUE) {
            $response['status']="success";
            $response['message']="Traveler Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Traveler Added Failed";
        }


                
    }

    
    for($x = 0 ; $x < $infants; $x++){

        $paxId ="";
        $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                $number= (int)$outputString + 1;
                $paxId = "FFP$number"; 								
            }
        } else {
                $paxId ="FFP1000";
        }

        ${'ifName'.$x} = strtoupper($_POST['infant'][$x]["ifName"]);
        ${'ilName'.$x} = strtoupper($_POST['infant'][$x]["ilName"]);
        ${'igender'.$x} = $_POST['infant'][$x]["igender"];
        ${'idob'.$x} =  $_POST['infant'][$x]["idob"];
        ${'ipassNo'.$x} = strtoupper($_POST['infant'][$x]["ipassNo"]);
        ${'ipassEx'.$x} = $_POST['infant'][$x]["ipassEx"];
        ${'ipassNation'.$x} = strtoupper($_POST['infant'][$x]["ipassNation"]);

        ${'sql'.$x} = "INSERT INTO `passengers`(
                        `paxId`,
                        `agentId`,
                        `bookingId`,
                        `fName`,
                        `lName`,
                        `dob`,
                        `gender`,
                        `type`,
                        `passNation`,
                        `passNo`,
                        `passEx`,
                         `created`
                    )
                VALUES('$paxId','$agentId','$BookingId','${'ifName'.$x}','${'ilName'.$x}','${'idob'.$x}','${'igender'.$x}','INF','${'ipassNation'.$x}',
                    '${'ipassNo'.$x}','${'ipassEx'.$x}','$createdTimer')";

        if ($conn->query(${'sql'.$x}) === TRUE) {
            $response['status']="success";
            $response['message']="Traveler Added Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Traveler Added Failed";
        }


                
    }

    echo json_encode($response);

        
    }else if($adult > 0){
        for($x = 0 ; $x < $adult; $x++){
        $paxId ="";
        $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]); 
                $number= (int)$outputString + 1;
                $paxId = "FFP$number"; 								
            }
        } else {
                $paxId ="FFP1000";
        }

            ${'afName'.$x} = strtoupper($_POST['adult'][$x]["afName"]);
            ${'alName'.$x} = strtoupper($_POST['adult'][$x]["alName"]);
            ${'agender'.$x} = $_POST['adult'][$x]["agender"];
            ${'adob'.$x} = $_POST['adult'][$x]["adob"];
            ${'apassNo'.$x} = strtoupper($_POST['adult'][$x]["apassNo"]);
            ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation'.$x} = strtoupper($_POST['adult'][$x]["apassNation"]);

            ${'sql'.$x} = "INSERT INTO `passengers`(
                        `paxId`,
                        `agentId`,
                        `bookingId`,
                        `fName`,
                        `lName`,
                        `dob`,
                        `gender`,
                        `type`,
                        `passNation`,
                        `passNo`,
                        `passEx`,
                        `created`
                    )
                VALUES('$paxId','$agentId','$BookingId','${'afName'.$x}','${'alName'.$x}','${'adob'.$x}','${'agender'.$x}','ADT','${'apassNation'.$x}',
                    '${'apassNo'.$x}','${'apassEx'.$x}','$createdTimer')";

                if ($conn->query(${'sql'.$x}) === TRUE) {
                    $response['status']="success";
                    $response['message']="Traveler Added Successfully";          
                } else {
                    $response['status']="error";
                    $response['message']="Traveler Added Failed";
                }
                    
        }
        
        echo json_encode($response);
        
    }

    

?>