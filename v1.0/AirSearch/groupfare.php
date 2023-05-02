<?php
include "../config.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


  $sql = "SELECT * FROM `groupfare` ORDER BY price ASC";
  $result = $conn->query($sql);

  $AllItenary = array();
  $Airportsql =  "SELECT name, cityName,countryCode FROM airports WHERE";

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){

        $Career = $row['career'];
        $BasePrice = $row['BasePrice'];
        $Taxes = $row['Taxes'];
        $Price = $row['price'];
        $Seat = $row['seat'];
        $Bags = $row['bags'];      
        $segments = $row['segment'];

        $carriersql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$Career' ");
        $carrierrow = mysqli_fetch_array($carriersql,MYSQLI_ASSOC);

        if(!empty($carrierrow)){
            $markettingCarrierName = $carrierrow['name'];                                            
        }

        if ($segments == 1){

            $DepartureFrom = $row['departure1'];
            $ArrivalTo =$row['arrival1'];

            // Departure Country
            $sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
            $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

            if(!empty($row1)){
                $dAirport = $row1['name'];
                $dCity = $row1['cityName'];
                $dCountry = $row1['countryCode'];           
            }

            // Departure Country
            $sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
            $row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

            if(!empty($row2)){
                $aAirport = $row2['name'];
                $aCity = $row2['cityName'];
                $aCountry = $row2['countryCode'];       
            }

            $ElapsedTime = $row['journeyTime'];
            $TravelTime = floor($ElapsedTime / 60)."H ".($ElapsedTime - ((floor($ElapsedTime / 60)) * 60))."Min";

            $segment = array("0" =>
                array("marketingcareer"=> $row['career'],
                        "marketingcareerName"=> $markettingCarrierName,
                        "marketingflight"=> $row['flightNum1'],
                        "operatingcareer"=> $row['career'],
                        "operatingflight"=> $row['flightNum1'],
                        "departure"=> $row['departure1'],
                        "departureAirport"=> $aAirport,
                        "departureLocation"=> "$dCity , $dCountry",                    
                        "departureTime" => $row['depTime1'],
                        "arrival"=> $row['arrival1'],                   
                        "arrivalTime" => $row['arrTime1'],
                        "arrivalAirport"=> $aAirport,
                        "arrivalLocation"=> "$aCity , $aCountry",
                        "flightduration"=> "$TravelTime",
                        "bookingcode"=> "",
                        "seat"=> "$Seat")                                           

            );
        
            $transitDetails = array("transit1" => "0");

            $basic = array("segment"=> "1",
                            "career"=> $Career,
                            "careerName" => $markettingCarrierName,
                            "BasePrice" => $BasePrice,
                            "Taxes" => $Taxes,
                            "price" => $Price,
                            "clientPrice"=> "",
                            "departure"=> $row['departure1'],                   
                            "departureTime" => $row['depTime1'],
                            "departureDate" => $row['depTime1'],
                            "arrival"=> $row['arrival1'],                   
                            "arrivalTime" => $row['arrTime1'],
                            "arrivalDate" => $row['arrTime1'],
                            "flightduration"=> "$TravelTime",
                            "bags" => "$Bags",
                            "seat" => "$Seat",
                            "class" => "Economy",											 
                            "segments" => $segment,
                            "transit" => $transitDetails
                        
                );

            array_push($AllItenary, $basic);
           
        }else if($segments == 2){
            
            $DepartureFrom = $row['departure1'];
            $ArrivalTo =$row['arrival1'];

            // Departure Country
            $dsql = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
            $drow = mysqli_fetch_array($dsql,MYSQLI_ASSOC);

            if(!empty($drow)){
                $dAirport = $drow['name'];
                $dCity = $drow['cityName'];
                $dCountry = $drow['countryCode'];           
            }

            // Departure Country
            $asql = mysqli_query($conn,"$Airportsql code='$ArrivalTo' ");
            $arow = mysqli_fetch_array($asql,MYSQLI_ASSOC);

            if(!empty($arow)){
                $aAirport = $arow['name'];
                $aCity = $arow['cityName'];
                $aCountry = $arow['countryCode'];         
            }

            $ElapsedTime = $row['journeyTime'];
            $TravelTime = floor($ElapsedTime / 60)."H ".($ElapsedTime - ((floor($ElapsedTime / 60)) * 60))."Min";

            $DepartureFrom1 = $row['departure2'];
            $ArrivalTo1 =$row['arrival2'];

            // Departure Country
            $dsql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
            $drow1 = mysqli_fetch_array($dsql1,MYSQLI_ASSOC);

            if(!empty($drow1)){
                $dAirport1 = $drow1['name'];
                $dCity1 = $drow1['cityName'];
                $dCountry1 = $drow1['countryCode'];           
            }

            // Departure Country
            $asql1 = mysqli_query($conn,"$Airportsql code='$ArrivalTo1' ");
            $arow1 = mysqli_fetch_array($asql1,MYSQLI_ASSOC);

            if(!empty($arow1)){
                $aAirport1 = $arow1['name'];
                $aCity1 = $arow1['cityName'];
                $aCountry1 = $arow1['countryCode'];         
            }

            $ElapsedTime1 = $row['journeyTime'];
            $TravelTime1 = floor($ElapsedTime1 / 60)."H ".($ElapsedTime1 - ((floor($ElapsedTime1 / 60)) * 60))."Min";


            $segment = array("0" =>
                            array("marketingcareer"=> $row['career'],
                                    "marketingcareerName"=> $markettingCarrierName,
                                    "marketingflight"=> $row['flightNum1'],
                                    "operatingcareer"=> $row['career'],
                                    "operatingflight"=> $row['flightNum1'],
                                    "departure"=> $row['departure1'],
                                    "departureAirport"=> $aAirport,
                                    "departureLocation"=> "$dCity , $dCountry",                    
                                    "departureTime" => $row['depTime1'],
                                    "arrival"=> $row['arrival1'],                   
                                    "arrivalTime" => $row['arrTime1'],
                                    "arrivalAirport"=> $aAirport,
                                    "arrivalLocation"=> "$aCity , $aCountry",
                                    "flightduration"=> "$TravelTime",
                                    "bookingcode"=> "",
                                    "seat"=> "$Seat"),
                            "1" =>
                            array("marketingcareer"=> $row['career'],
                                    "marketingcareerName"=> $markettingCarrierName,
                                    "marketingflight"=> $row['flightNum2'],
                                    "operatingcareer"=> $row['career'],
                                    "operatingflight"=> $row['flightNum2'],
                                    "departure"=> $row['departure2'],
                                    "departureAirport"=> $aAirport1,
                                    "departureLocation"=> "$dCity1 , $dCountry1",                    
                                    "departureTime" => $row['depTime2'],
                                    "arrival"=> $row['arrival2'],                   
                                    "arrivalTime" => $row['arrTime2'],
                                    "arrivalAirport"=> $aAirport1,
                                    "arrivalLocation"=> "$aCity1 , $aCountry1",
                                    "flightduration"=> "$TravelTime1",
                                    "bookingcode"=> "",
                                    "seat"=> "$Seat")
                                                                               

                            );
        
            $transitDetails = array("transit1" => "0");

            $basic = array("segment"=> "2",
                            "career"=> $Career,
                            "careerName" => $markettingCarrierName,
                            "BasePrice" => $BasePrice,
                            "Taxes" => $Taxes,
                            "price" => $Price,
                            "clientPrice"=> "",
                            "departure"=> $row['departure1'],                   
                            "departureTime" => $row['depTime1'],
                            "departureDate" => $row['depTime1'],
                            "arrival"=> $row['arrival2'],                   
                            "arrivalTime" => $row['arrTime2'],
                            "arrivalDate" => $row['arrTime2'],
                            "flightduration"=> "$TravelTime",
                            "bags" => "$Bags",
                            "seat" => "$Seat",
                            "class" => "Economy",											 
                            "segments" => $segment,
                            "transit" => $transitDetails
                        
                );

            array_push($AllItenary, $basic);
            
        }else if($segments == 3){
            
        }

    }
    
  }

  echo json_encode($AllItenary);

$conn->close();
?>