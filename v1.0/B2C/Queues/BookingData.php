<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if (array_key_exists("all", $_GET) && array_key_exists("agentId", $_GET)) {
    
    $agentId = $_GET["agentId"];
    $staffId = "";
    $sql = "SELECT * FROM `booking` WHERE agentId='$agentId' ORDER BY id DESC";
    $result = $conn->query($sql);
    $totaldata = $conn->query("SELECT * FROM `booking` where agentId='$agentId'")->num_rows;

    $return_arr = array();
    $Data = array();
    $count = 0; 
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;
            $staffId = $row['staffId'];
            $agentId = $row['agentId'];
            $pnr = $row['pnr'];
            $tripType = $row['tripType'];

            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);
            if (!empty($data)) {
                $companyname = $data['company'];
                $companyphone = $data['phone'];
            }

            $staffsql = mysqli_query($conn, "SELECT * FROM staffList WHERE staffId='$staffId' AND agentId ='$agentId'");
            $staffRow = mysqli_fetch_array($staffsql, MYSQLI_ASSOC);

            if (!empty($staffRow)) {
                $staffName = $staffRow['name'];
            } else {
                $staffName = "Agent";
            }
            if($tripType == 'oneway' || $tripType == '1'){
                $FlightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
                //print_r($FlightData);
                $FlightDate = $FlightData[0]['departureTime1'];
            }else{
                $FlightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
               // print_r($FlightData);
                $FlightDate = isset($FlightData[0]['goDepartureTime1']) ? $FlightData[0]['goDepartureTime1'] :"";
                
            }


            $response = $row;
            $response['companyname'] = "$companyname";
            $response['companyphone'] = "$companyphone";
            $response['bookedby'] = "$staffName";
            $response['flightdate'] =  "$FlightDate" ;
            $response['serial']="$count";

            array_push($Data, $response);
        }
    }

    $return_arr['total'] = $totaldata;
   
    $return_arr['data'] = $Data;
        

    echo json_encode($return_arr);
}else if (array_key_exists("agentId", $_GET) && array_key_exists("bookingId", $_GET)) {

    $agentId = $_GET["agentId"];
    $bookingId = $_GET["bookingId"];
    $sql = "SELECT * FROM `booking` where agentId='$agentId' AND bookingId='$bookingId'";
    $result = $conn->query($sql);

    $return_arr = array();
    if ($result->num_rows > 0) {     
        while ($row = $result->fetch_assoc()) {
            $staffId = $row['staffId'];
            $agentId = $row['agentId'];
            $pnr = $row['pnr'];
            $tripType = $row['tripType'];
            // print_r($pnr);
            // print_r($tripType);
            
            $staffsql = mysqli_query($conn, "SELECT * FROM staffList WHERE agentId='$agentId' AND  staffId='$staffId' ");
            

            $staffRow = mysqli_fetch_array($staffsql, MYSQLI_ASSOC);

            if (!empty($staffRow)) {
                $staffName = $staffRow['name'];
            } else {
                $staffName = "Agent";
            }

            $PassengerData = $conn->query("SELECT * FROM  `passengers` where bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            if ($tripType == 'oneway' || $tripType == 1) {
                $allFlightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
                //print_r($allFlightData);
                if (isset($allFlightData[0])) {
                    $flightData = $allFlightData[0];
                    $segment = $flightData['segment'];

                    if ($segment == 1) {
                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['marketingCareerName1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),

                        );

                    } else if ($segment == 2) {

                        //segment - 1;

                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['marketingCareerName1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['marketingCareerName2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),
                        );

                    } else if ($segment == 3) {

                        //segment - 1;

                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['marketingCareerName2'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['marketingCareerName2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        //segment -3
                        $departure3 = $flightData['departure3'];
                        $arrival3 = $flightData['arrival3'];
                        $departureTime3 = $flightData['departureTime3'];
                        $arrivalTime3 = $flightData['arrivalTime3'];
                        $marketingCareerName3 = $flightData['marketingCareerName3'];
                        $marketingCareer3 = $flightData['marketingCareer3'];
                        $marketingFlight3 = $flightData['marketingFlight3'];
                        $operatingCareer3 = $flightData['operatingCareer3'];
                        $flightDuration3 = $flightData['flightDuration3'];
                        $departureAirport3 = $flightData['departureAirport3'];
                        $arrivalAirport3 = $flightData['arrivalAirport3'];
                        $departureLocation3 = $flightData['departureLocation3'];
                        $arrivalLocation3 = $flightData['arrivalLocation3'];
                        $bookingcode3 = $flightData['bookingcode3'];
                        $departureTerminal3 = $flightData['departureTerminal3'];
                        $arrivalTerminal3 = $flightData['arrivalTerminal3'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),

                            "2" => array(
                                "departure" => "$departure3",
                                "arrival" => "$arrival3",
                                "departureTime" => "$departureTime3",
                                "arrivalTime" => "$arrivalTime3",
                                "marketingCareerName" => "$marketingCareerName3",
                                "marketingCareer" => "$marketingCareer3",
                                "marketingFlight" => "$marketingFlight3",
                                "operatingCareer" => "$operatingCareer3",
                                "flightDuration" => "$flightDuration3",
                                "departureAirport" => "$departureAirport3",
                                "arrivalAirport" => "$arrivalAirport3",
                                "departureLocation" => "$departureLocation3",
                                "arrivalLocation" => "$arrivalLocation3",
                                "bookingcode" => "$bookingcode3",
                                "departureTerminal" => "$departureTerminal3",
                                "arrivalTerminal" => "$arrivalTerminal3",

                            ),
                        );

                    } else if ($segment == 4) {

                        //segment - 1;
                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['marketingCareerName1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['marketingCareerName2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        //segment -3
                        $departure3 = $flightData['departure3'];
                        $arrival3 = $flightData['arrival3'];
                        $departureTime3 = $flightData['departureTime3'];
                        $arrivalTime3 = $flightData['arrivalTime3'];
                        $marketingCareerName3 = $flightData['marketingCareerName3'];
                        $marketingCareer3 = $flightData['marketingCareer3'];
                        $marketingFlight3 = $flightData['marketingFlight3'];
                        $operatingCareer3 = $flightData['operatingCareer3'];
                        $flightDuration3 = $flightData['flightDuration3'];
                        $departureAirport3 = $flightData['departureAirport3'];
                        $arrivalAirport3 = $flightData['arrivalAirport3'];
                        $departureLocation3 = $flightData['departureLocation3'];
                        $arrivalLocation3 = $flightData['arrivalLocation3'];
                        $bookingcode3 = $flightData['bookingcode3'];
                        $departureTerminal3 = $flightData['departureTerminal3'];
                        $arrivalTerminal3 = $flightData['arrivalTerminal3'];

                        //segment 4
                        $departure4 = $flightData['departure4'];
                        $arrival4 = $flightData['arrival4'];
                        $departureTime4 = $flightData['departureTime4'];
                        $arrivalTime4 = $flightData['arrivalTime4'];
                        $marketingCareerName4 = $flightData['marketingCareerName4'];
                        $marketingCareer4 = $flightData['marketingCareer4'];
                        $marketingFlight4 = $flightData['marketingFlight4'];
                        $operatingCareer4 = $flightData['operatingCareer4'];
                        $flightDuration4 = $flightData['flightDuration4'];
                        $departureAirport4 = $flightData['departureAirport4'];
                        $arrivalAirport4 = $flightData['arrivalAirport4'];
                        $departureLocation4 = $flightData['departureLocation4'];
                        $arrivalLocation4 = $flightData['arrivalLocation4'];
                        $bookingcode4 = $flightData['bookingcode4'];
                        $departureTerminal4 = $flightData['departureTerminal4'];
                        $arrivalTerminal4 = $flightData['arrivalTerminal4'];

                        $segmentsData = array(

                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),

                            "2" => array(
                                "departure" => "$departure3",
                                "arrival" => "$arrival3",
                                "departureTime" => "$departureTime3",
                                "arrivalTime" => "$arrivalTime3",
                                "marketingCareerName" => "$marketingCareerName3",
                                "marketingCareer" => "$marketingCareer3",
                                "marketingFlight" => "$marketingFlight3",
                                "operatingCareer" => "$operatingCareer3",
                                "flightDuration" => "$flightDuration3",
                                "departureAirport" => "$departureAirport3",
                                "arrivalAirport" => "$arrivalAirport3",
                                "departureLocation" => "$departureLocation3",
                                "arrivalLocation" => "$arrivalLocation3",
                                "bookingcode" => "$bookingcode3",
                                "departureTerminal" => "$departureTerminal3",
                                "arrivalTerminal" => "$arrivalTerminal3",

                            ),

                            "3" => array(
                                "departure" => "$departure4",
                                "arrival" => "$arrival4",
                                "departureTime" => "$departureTime4",
                                "arrivalTime" => "$arrivalTime4",
                                "marketingCareerName" => "$marketingCareerName4",
                                "marketingCareer" => "$marketingCareer4",
                                "marketingFlight" => "$marketingFlight4",
                                "operatingCareer" => "$operatingCareer4",
                                "flightDuration" => "$flightDuration4",
                                "departureAirport" => "$departureAirport4",
                                "arrivalAirport" => "$arrivalAirport4",
                                "departureLocation" => "$departureLocation4",
                                "arrivalLocation" => "$arrivalLocation4",
                                "bookingcode" => "$bookingcode4",
                                "departureTerminal" => "$departureTerminal4",
                                "arrivalTerminal" => "$arrivalTerminal4",

                            ),
                        );

                    }

                    $FlightData = array(
                        "system" => $system,
                        "segment_id" => $segment,
                        "segment" => $segmentsData,
                    );
                }

            }else if ($tripType == 'return' || $tripType == 2) {
                //echo $pnr;
                $allFlightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);

            //    print_r($allFlightData);

                if (isset($allFlightData[0])) {
                    $flightData = $allFlightData[0];
                    $segment = $flightData['segment'];

                    if ($segment == 1) {
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goMarketingCareerName1 = $flightData['goMarketingCareerName1'];
                        $goMarketingFlight1 = $flightData['goMarketingFlight1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goMarketingCareerName" => $goMarketingCareerName1,
                                    "goMarketingFlight" => $goMarketingFlight1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                )),

                            "back" => array(
                                "0" => array(
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1)),

                        );

                        $FlightData = array(
                            "System" => $system,
                            "Segment_id" => $segment,
                            "Segment_data" => $segmentsData,
                        );

                    } else if ($segment == 2) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goMarketingCareerName1 = $flightData['goMarketingCareerName1'];
                        $goMarketingFlight1 = $flightData['goMarketingFlight1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goMarketingCareerName2 = $flightData['goMarketingCareerName2'];
                        $goMarketingFlight2 = $flightData['goMarketingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goMarketingCareerName" => $goMarketingCareerName1,
                                    "goMarketingFlight" => $goMarketingFlight1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                ),
                                "1" => array(
                                    "goTransit" => $goTransit2,
                                    "goMarketingCareer" => $goMarketingCareer2,
                                    "goOperatingCareer" => $goOperatingCareer2,
                                    "goOperatingFlight" => $goOperatingFlight2,
                                    "goMarketingCareerName" => $goMarketingCareerName2,
                                    "goMarketingFlight" => $goMarketingFlight2,
                                    "goDeparture" => $goDeparture2,
                                    "goArrival" => $goArrival2,
                                    "goDepartureAirport" => $goDepartureAirport2,
                                    "goArrivalAirport" => $goArrivalAirport2,
                                    "goDepartureLocation" => $goDepartureLocation2,
                                    "goArrivalLocation" => $goArrivalLocation2,
                                    "goDepartureTime" => $goDepartureTime2,
                                    "goArrivalTime" => $goArrivalTime2,
                                    "goFlightDuration" => $goFlightDuration2,
                                    "goBookingCode" => $goBookingCode2,
                                    "goDepTerminal" => $goDepTerminal2,
                                    "goArrTerminal" => $goArrTerminal2,
                                )),

                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                                "1" => array(
                                    "backTransit" => $backTransit2,
                                    "backMarketingCareer" => $backMarketingCareer2,
                                    "backMarketingCareerName" => $backMarketingCareerName2,
                                    "backMarketingFlight" => $backMarketingFlight2,
                                    "backOperatingCareer" => $backOperatingCareer2,
                                    "backOperatingFlight" => $backOperatingFlight2,
                                    "backDeparture" => $backDeparture2,
                                    "backArrival" => $backArrival2,
                                    "backDepartureAirport" => $backDepartureAirport2,
                                    "backArrivalAirport" => $backArrivalAirport2,
                                    "backDepartureLocation" => $backDepartureLocation2,
                                    "backArrivalLocation" => $backArrivalLocation2,
                                    "backDepartureTime" => $backDepartureTime2,
                                    "backArrivalTime" => $backArrivalTime2,
                                    "backFlightDuration" => $backFlightDuration2,
                                    "backBookingCode" => $backBookingCode2,
                                    "backdepTerminal" => $backdepTerminal2,
                                    "backArrTerminal" => $backArrTerminal2),
                            ),
                        );

                        $FlightData = array(
                            "System" => $system,
                            "Segment_id" => $segment,
                            "Segment_data" => $segmentsData,
                        );

                    } else if ($segment == 12) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goMarketingCareerName" => $goMarketingCareerName1,
                                    "goMarketingFlight" => $goMarketingFlight1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                ),
                            ),
                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                                "1" => array(
                                    "backTransit" => $backTransit2,
                                    "backMarketingCareer" => $backMarketingCareer2,
                                    "backMarketingCareerName" => $backMarketingCareerName2,
                                    "backMarketingFlight" => $backMarketingFlight2,
                                    "backOperatingCareer" => $backOperatingCareer2,
                                    "backOperatingFlight" => $backOperatingFlight2,
                                    "backDeparture" => $backDeparture2,
                                    "backArrival" => $backArrival2,
                                    "backDepartureAirport" => $backDepartureAirport2,
                                    "backArrivalAirport" => $backArrivalAirport2,
                                    "backDepartureLocation" => $backDepartureLocation2,
                                    "backArrivalLocation" => $backArrivalLocation2,
                                    "backDepartureTime" => $backDepartureTime2,
                                    "backArrivalTime" => $backArrivalTime2,
                                    "backFlightDuration" => $backFlightDuration2,
                                    "backBookingCode" => $backBookingCode2,
                                    "backdepTerminal" => $backdepTerminal2,
                                    "backArrTerminal" => $backArrTerminal2),
                            ),
                        );
                        $FlightData = array(
                            "System" => $system,
                            "Segment_id" => $segment,
                            "Segment_data" => $segmentsData,
                        );

                    } else if ($segment == 21) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];

                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(

                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goMarketingFlight" => $goMarketingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1),

                                "1" => array(
                                    "goTransit" => $goTransit2,
                                    "goMarketingCareer" => $goMarketingCareer2,
                                    "goOperatingCareer" => $goOperatingCareer2,
                                    "goOperatingFlight" => $goOperatingFlight2,
                                    "goDeparture" => $goDeparture2,
                                    "goArrival" => $goArrival2,
                                    "goDepartureAirport" => $goDepartureAirport2,
                                    "goArrivalAirport" => $goArrivalAirport2,
                                    "goDepartureLocation" => $goDepartureLocation2,
                                    "goArrivalLocation" => $goArrivalLocation2,
                                    "goDepartureTime" => $goDepartureTime2,
                                    "goArrivalTime" => $goArrivalTime2,
                                    "goFlightDuration" => $goFlightDuration2,
                                    "goBookingCode" => $goBookingCode2,
                                    "goDepTerminal" => $goDepTerminal2,
                                    "goArrTerminal" => $goArrTerminal2),

                            ),

                            "back" => array(

                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                            ),
                        );

                        $FlightData = array(
                            "System" => $system,
                            "Segment_id" => $segment,
                            "Segment_data" => $segmentsData,
                        );

                    }
                    
                }else{
                    echo "data not found";
                }

            }

            $Notes = $conn->query("SELECT * FROM `notes` where reference='$bookingId'")->fetch_all(MYSQLI_ASSOC);
            $activitylog = $conn->query("SELECT * FROM  `activitylog` where ref='$bookingId'")->fetch_all(MYSQLI_ASSOC);
            $TicketInfo = $conn->query("SELECT DISTINCT  * FROM ticketed WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            $response = $row;
            $response['passenger'] = $PassengerData;
            $response['activity'] = $activitylog;
            $response['flightData'] = $FlightData;
            $response['notes'] = $Notes;
            $response['ticketData'] = $TicketInfo;

            array_push($return_arr, $response);
        }
        echo json_encode($return_arr);
    }else{
        $response['status'] = 'error';
        $response['message'] = "Booking Data Not Found";
        echo json_encode($response);
    }

    

}else if (array_key_exists("agentId", $_GET) && array_key_exists("bookingId", $_GET) && array_key_exists("subAgentId", $_GET)) {

    $agentId = $_GET["agentId"];
    $subAgentId = $_GET["subAgentId"];
    $bookingId = $_GET["bookingId"];
    $sql = "SELECT * FROM `booking` where agentId='$agentId' AND subAgentId='$agentId' AND bookingId ='$bookingId' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $staffId = $row['staffId'];
            $agentId = $row['agentId'];
            $pnr = $row['pnr'];
            $tripType = $row['tripType'];

            $staffsql = mysqli_query($conn, "SELECT * FROM subAgent WHERE agentId='$agentId' AND subAgentId='$subAgentId' ");
            $staffRow = mysqli_fetch_array($staffsql, MYSQLI_ASSOC);

            if (!empty($staffRow)) {
                $staffName = $staffRow['name'];
            } else {
                $staffName = "Agent";
            }

            $bookingId = $row['bookingId'];

            $PassengerData = $conn->query("SELECT * FROM `passengers` where bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            //flight Data
            $FlightData = array();
            if ($tripType == 'oneway') {
                $allFlightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
 
                if (isset($allFlightData[0])) {
                    $flightData = $allFlightData[0];
                    $segment = $flightData['segment'];

                    if ($segment == 1) {
                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['arrivalTime1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),

                        );

                    } else if ($segment == 2) {

                        //segment - 1;

                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['arrivalTime1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['arrivalTime2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),
                        );

                    } else if ($segment == 3) {

                        //segment - 1;

                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['arrivalTime1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['arrivalTime2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        //segment -3
                        $departure3 = $flightData['departure3'];
                        $arrival3 = $flightData['arrival3'];
                        $departureTime3 = $flightData['departureTime3'];
                        $arrivalTime3 = $flightData['arrivalTime3'];
                        $marketingCareerName3 = $flightData['arrivalTime3'];
                        $marketingCareer3 = $flightData['marketingCareer3'];
                        $marketingFlight3 = $flightData['marketingFlight3'];
                        $operatingCareer3 = $flightData['operatingCareer3'];
                        $flightDuration3 = $flightData['flightDuration3'];
                        $departureAirport3 = $flightData['departureAirport3'];
                        $arrivalAirport3 = $flightData['arrivalAirport3'];
                        $departureLocation3 = $flightData['departureLocation3'];
                        $arrivalLocation3 = $flightData['arrivalLocation3'];
                        $bookingcode3 = $flightData['bookingcode3'];
                        $departureTerminal3 = $flightData['departureTerminal3'];
                        $arrivalTerminal3 = $flightData['arrivalTerminal3'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),

                            "2" => array(
                                "departure" => "$departure3",
                                "arrival" => "$arrival3",
                                "departureTime" => "$departureTime3",
                                "arrivalTime" => "$arrivalTime3",
                                "marketingCareerName" => "$marketingCareerName3",
                                "marketingCareer" => "$marketingCareer3",
                                "marketingFlight" => "$marketingFlight3",
                                "operatingCareer" => "$operatingCareer3",
                                "flightDuration" => "$flightDuration3",
                                "departureAirport" => "$departureAirport3",
                                "arrivalAirport" => "$arrivalAirport3",
                                "departureLocation" => "$departureLocation3",
                                "arrivalLocation" => "$arrivalLocation3",
                                "bookingcode" => "$bookingcode3",
                                "departureTerminal" => "$departureTerminal3",
                                "arrivalTerminal" => "$arrivalTerminal3",

                            ),
                        );

                    } else if ($segment == 4) {

                        //segment - 1;
                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['arrivalTime1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['arrivalTime2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        //segment -3
                        $departure3 = $flightData['departure3'];
                        $arrival3 = $flightData['arrival3'];
                        $departureTime3 = $flightData['departureTime3'];
                        $arrivalTime3 = $flightData['arrivalTime3'];
                        $marketingCareerName3 = $flightData['arrivalTime3'];
                        $marketingCareer3 = $flightData['marketingCareer3'];
                        $marketingFlight3 = $flightData['marketingFlight3'];
                        $operatingCareer3 = $flightData['operatingCareer3'];
                        $flightDuration3 = $flightData['flightDuration3'];
                        $departureAirport3 = $flightData['departureAirport3'];
                        $arrivalAirport3 = $flightData['arrivalAirport3'];
                        $departureLocation3 = $flightData['departureLocation3'];
                        $arrivalLocation3 = $flightData['arrivalLocation3'];
                        $bookingcode3 = $flightData['bookingcode3'];
                        $departureTerminal3 = $flightData['departureTerminal3'];
                        $arrivalTerminal3 = $flightData['arrivalTerminal3'];

                        //segment 4
                        $departure4 = $flightData['departure4'];
                        $arrival4 = $flightData['arrival4'];
                        $departureTime4 = $flightData['departureTime4'];
                        $arrivalTime4 = $flightData['arrivalTime4'];
                        $marketingCareerName4 = $flightData['arrivalTime4'];
                        $marketingCareer4 = $flightData['marketingCareer4'];
                        $marketingFlight4 = $flightData['marketingFlight4'];
                        $operatingCareer4 = $flightData['operatingCareer4'];
                        $flightDuration4 = $flightData['flightDuration4'];
                        $departureAirport4 = $flightData['departureAirport4'];
                        $arrivalAirport4 = $flightData['arrivalAirport4'];
                        $departureLocation4 = $flightData['departureLocation4'];
                        $arrivalLocation4 = $flightData['arrivalLocation4'];
                        $bookingcode4 = $flightData['bookingcode4'];
                        $departureTerminal4 = $flightData['departureTerminal4'];
                        $arrivalTerminal4 = $flightData['arrivalTerminal4'];

                        $segmentsData = array(

                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),

                            "2" => array(
                                "departure" => "$departure3",
                                "arrival" => "$arrival3",
                                "departureTime" => "$departureTime3",
                                "arrivalTime" => "$arrivalTime3",
                                "marketingCareerName" => "$marketingCareerName3",
                                "marketingCareer" => "$marketingCareer3",
                                "marketingFlight" => "$marketingFlight3",
                                "operatingCareer" => "$operatingCareer3",
                                "flightDuration" => "$flightDuration3",
                                "departureAirport" => "$departureAirport3",
                                "arrivalAirport" => "$arrivalAirport3",
                                "departureLocation" => "$departureLocation3",
                                "arrivalLocation" => "$arrivalLocation3",
                                "bookingcode" => "$bookingcode3",
                                "departureTerminal" => "$departureTerminal3",
                                "arrivalTerminal" => "$arrivalTerminal3",

                            ),

                            "3" => array(
                                "departure" => "$departure4",
                                "arrival" => "$arrival4",
                                "departureTime" => "$departureTime4",
                                "arrivalTime" => "$arrivalTime4",
                                "marketingCareerName" => "$marketingCareerName4",
                                "marketingCareer" => "$marketingCareer4",
                                "marketingFlight" => "$marketingFlight4",
                                "operatingCareer" => "$operatingCareer4",
                                "flightDuration" => "$flightDuration4",
                                "departureAirport" => "$departureAirport4",
                                "arrivalAirport" => "$arrivalAirport4",
                                "departureLocation" => "$departureLocation4",
                                "arrivalLocation" => "$arrivalLocation4",
                                "bookingcode" => "$bookingcode4",
                                "departureTerminal" => "$departureTerminal4",
                                "arrivalTerminal" => "$arrivalTerminal4",

                            ),
                        );

                    }

                    $FlightData = array(
                        "system" => $system,
                        "segment_id" => $segment,
                        "segment" => $segmentsData,
                    );
                }

            } else if ($tripType == 'return') {
                $allFlightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);

                if (isset($allFlightData[0])) {
                    $flightData = $allFlightData[0];
                    $segment = $flightData['segment'];

                    if ($segment == 1) {
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                )),

                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1)),

                        );

                    } else if ($segment == 2) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                ),
                                "1" => array(
                                    "goTransit" => $goTransit2,
                                    "goMarketingCareer" => $goMarketingCareer2,
                                    "goOperatingCareer" => $goOperatingCareer2,
                                    "goOperatingFlight" => $goOperatingFlight2,
                                    "goDeparture" => $goDeparture2,
                                    "goArrival" => $goArrival2,
                                    "goDepartureAirport" => $goDepartureAirport2,
                                    "goArrivalAirport" => $goArrivalAirport2,
                                    "goDepartureLocation" => $goDepartureLocation2,
                                    "goArrivalLocation" => $goArrivalLocation2,
                                    "goDepartureTime" => $goDepartureTime2,
                                    "goArrivalTime" => $goArrivalTime2,
                                    "goFlightDuration" => $goFlightDuration2,
                                    "goBookingCode" => $goBookingCode2,
                                    "goDepTerminal" => $goDepTerminal2,
                                    "goArrTerminal" => $goArrTerminal2,
                                )),

                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                                "1" => array(
                                    "backTransit" => $backTransit2,
                                    "backMarketingCareer" => $backMarketingCareer2,
                                    "backMarketingCareerName" => $backMarketingCareerName2,
                                    "backMarketingFlight" => $backMarketingFlight2,
                                    "backOperatingCareer" => $backOperatingCareer2,
                                    "backOperatingFlight" => $backOperatingFlight2,
                                    "backDeparture" => $backDeparture2,
                                    "backArrival" => $backArrival2,
                                    "backDepartureAirport" => $backDepartureAirport2,
                                    "backArrivalAirport" => $backArrivalAirport2,
                                    "backDepartureLocation" => $backDepartureLocation2,
                                    "backArrivalLocation" => $backArrivalLocation2,
                                    "backDepartureTime" => $backDepartureTime2,
                                    "backArrivalTime" => $backArrivalTime2,
                                    "backFlightDuration" => $backFlightDuration2,
                                    "backBookingCode" => $backBookingCode2,
                                    "backdepTerminal" => $backdepTerminal2,
                                    "backArrTerminal" => $backArrTerminal2),
                            ),
                        );

                    } else if ($segment == 12) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                ),
                            ),
                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                                "1" => array(
                                    "backTransit" => $backTransit2,
                                    "backMarketingCareer" => $backMarketingCareer2,
                                    "backMarketingCareerName" => $backMarketingCareerName2,
                                    "backMarketingFlight" => $backMarketingFlight2,
                                    "backOperatingCareer" => $backOperatingCareer2,
                                    "backOperatingFlight" => $backOperatingFlight2,
                                    "backDeparture" => $backDeparture2,
                                    "backArrival" => $backArrival2,
                                    "backDepartureAirport" => $backDepartureAirport2,
                                    "backArrivalAirport" => $backArrivalAirport2,
                                    "backDepartureLocation" => $backDepartureLocation2,
                                    "backArrivalLocation" => $backArrivalLocation2,
                                    "backDepartureTime" => $backDepartureTime2,
                                    "backArrivalTime" => $backArrivalTime2,
                                    "backFlightDuration" => $backFlightDuration2,
                                    "backBookingCode" => $backBookingCode2,
                                    "backdepTerminal" => $backdepTerminal2,
                                    "backArrTerminal" => $backArrTerminal2),
                            ),
                        );

                    } else if ($segment == 21) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];

                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(

                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1),

                                "1" => array(
                                    "goTransit" => $goTransit2,
                                    "goMarketingCareer" => $goMarketingCareer2,
                                    "goOperatingCareer" => $goOperatingCareer2,
                                    "goOperatingFlight" => $goOperatingFlight2,
                                    "goDeparture" => $goDeparture2,
                                    "goArrival" => $goArrival2,
                                    "goDepartureAirport" => $goDepartureAirport2,
                                    "goArrivalAirport" => $goArrivalAirport2,
                                    "goDepartureLocation" => $goDepartureLocation2,
                                    "goArrivalLocation" => $goArrivalLocation2,
                                    "goDepartureTime" => $goDepartureTime2,
                                    "goArrivalTime" => $goArrivalTime2,
                                    "goFlightDuration" => $goFlightDuration2,
                                    "goBookingCode" => $goBookingCode2,
                                    "goDepTerminal" => $goDepTerminal2,
                                    "goArrTerminal" => $goArrTerminal2),

                            ),

                            "back" => array(

                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                            ),
                        );

                    }

                    $FlightData = array(
                        "System" => $system,
                        "Segment_id" => $segment,
                        "Segment_data" => $segmentsData,
                    );

                }

            }

            $activitylog = $conn->query("SELECT * FROM  `activitylog` where ref='$bookingId'")->fetch_all(MYSQLI_ASSOC);
            $TicketInfo = $conn->query("SELECT DISTINCT  * FROM ticketed WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            $response = $row;
            $response['passenger'] = $PassengerData;
            $response['activity'] = $activitylog;
            $response['flightData'] = $FlightData;
            $response['ticketData'] = $TicketInfo;

            array_push($return_arr, $response);
        }
    }

    echo json_encode($return_arr);

}else if (array_key_exists("agentId", $_GET) && array_key_exists("subAgentId", $_GET)) {

    $agentId = $_GET["agentId"];
    $subAgentId = $_GET["subAgentId"];
    $bookingId = $_GET["bookingId"];
    $sql = "SELECT * FROM `booking` where agentId='$agentId' AND subAgentId='$agentId' ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $staffId = $row['staffId'];
            $agentId = $row['agentId'];
            $pnr = $row['pnr'];
            $tripType = $row['tripType'];

            $staffsql = mysqli_query($conn, "SELECT * FROM subAgent WHERE agentId='$agentId' AND subAgentId='$subAgentId' ");
            $staffRow = mysqli_fetch_array($staffsql, MYSQLI_ASSOC);

            if (!empty($staffRow)) {
                $staffName = $staffRow['name'];
            } else {
                $staffName = "Agent";
            }

            $bookingId = $row['bookingId'];

            $PassengerData = $conn->query("SELECT * FROM `passengers` where bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            //flight Data
            $FlightData = array();
            if ($tripType == 'oneway') {
                $allFlightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);

                if (isset($allFlightData[0])) {
                    $flightData = $allFlightData[0];
                    $segment = $flightData['segment'];

                    if ($segment == 1) {
                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['arrivalTime1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),

                        );

                    } else if ($segment == 2) {

                        //segment - 1;

                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['arrivalTime1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['arrivalTime2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),
                        );

                    } else if ($segment == 3) {

                        //segment - 1;

                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['arrivalTime1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['arrivalTime2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        //segment -3
                        $departure3 = $flightData['departure3'];
                        $arrival3 = $flightData['arrival3'];
                        $departureTime3 = $flightData['departureTime3'];
                        $arrivalTime3 = $flightData['arrivalTime3'];
                        $marketingCareerName3 = $flightData['arrivalTime3'];
                        $marketingCareer3 = $flightData['marketingCareer3'];
                        $marketingFlight3 = $flightData['marketingFlight3'];
                        $operatingCareer3 = $flightData['operatingCareer3'];
                        $flightDuration3 = $flightData['flightDuration3'];
                        $departureAirport3 = $flightData['departureAirport3'];
                        $arrivalAirport3 = $flightData['arrivalAirport3'];
                        $departureLocation3 = $flightData['departureLocation3'];
                        $arrivalLocation3 = $flightData['arrivalLocation3'];
                        $bookingcode3 = $flightData['bookingcode3'];
                        $departureTerminal3 = $flightData['departureTerminal3'];
                        $arrivalTerminal3 = $flightData['arrivalTerminal3'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),

                            "2" => array(
                                "departure" => "$departure3",
                                "arrival" => "$arrival3",
                                "departureTime" => "$departureTime3",
                                "arrivalTime" => "$arrivalTime3",
                                "marketingCareerName" => "$marketingCareerName3",
                                "marketingCareer" => "$marketingCareer3",
                                "marketingFlight" => "$marketingFlight3",
                                "operatingCareer" => "$operatingCareer3",
                                "flightDuration" => "$flightDuration3",
                                "departureAirport" => "$departureAirport3",
                                "arrivalAirport" => "$arrivalAirport3",
                                "departureLocation" => "$departureLocation3",
                                "arrivalLocation" => "$arrivalLocation3",
                                "bookingcode" => "$bookingcode3",
                                "departureTerminal" => "$departureTerminal3",
                                "arrivalTerminal" => "$arrivalTerminal3",

                            ),
                        );

                    } else if ($segment == 4) {

                        //segment - 1;
                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['arrivalTime1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['arrivalTime2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        //segment -3
                        $departure3 = $flightData['departure3'];
                        $arrival3 = $flightData['arrival3'];
                        $departureTime3 = $flightData['departureTime3'];
                        $arrivalTime3 = $flightData['arrivalTime3'];
                        $marketingCareerName3 = $flightData['arrivalTime3'];
                        $marketingCareer3 = $flightData['marketingCareer3'];
                        $marketingFlight3 = $flightData['marketingFlight3'];
                        $operatingCareer3 = $flightData['operatingCareer3'];
                        $flightDuration3 = $flightData['flightDuration3'];
                        $departureAirport3 = $flightData['departureAirport3'];
                        $arrivalAirport3 = $flightData['arrivalAirport3'];
                        $departureLocation3 = $flightData['departureLocation3'];
                        $arrivalLocation3 = $flightData['arrivalLocation3'];
                        $bookingcode3 = $flightData['bookingcode3'];
                        $departureTerminal3 = $flightData['departureTerminal3'];
                        $arrivalTerminal3 = $flightData['arrivalTerminal3'];

                        //segment 4
                        $departure4 = $flightData['departure4'];
                        $arrival4 = $flightData['arrival4'];
                        $departureTime4 = $flightData['departureTime4'];
                        $arrivalTime4 = $flightData['arrivalTime4'];
                        $marketingCareerName4 = $flightData['arrivalTime4'];
                        $marketingCareer4 = $flightData['marketingCareer4'];
                        $marketingFlight4 = $flightData['marketingFlight4'];
                        $operatingCareer4 = $flightData['operatingCareer4'];
                        $flightDuration4 = $flightData['flightDuration4'];
                        $departureAirport4 = $flightData['departureAirport4'];
                        $arrivalAirport4 = $flightData['arrivalAirport4'];
                        $departureLocation4 = $flightData['departureLocation4'];
                        $arrivalLocation4 = $flightData['arrivalLocation4'];
                        $bookingcode4 = $flightData['bookingcode4'];
                        $departureTerminal4 = $flightData['departureTerminal4'];
                        $arrivalTerminal4 = $flightData['arrivalTerminal4'];

                        $segmentsData = array(

                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),

                            "2" => array(
                                "departure" => "$departure3",
                                "arrival" => "$arrival3",
                                "departureTime" => "$departureTime3",
                                "arrivalTime" => "$arrivalTime3",
                                "marketingCareerName" => "$marketingCareerName3",
                                "marketingCareer" => "$marketingCareer3",
                                "marketingFlight" => "$marketingFlight3",
                                "operatingCareer" => "$operatingCareer3",
                                "flightDuration" => "$flightDuration3",
                                "departureAirport" => "$departureAirport3",
                                "arrivalAirport" => "$arrivalAirport3",
                                "departureLocation" => "$departureLocation3",
                                "arrivalLocation" => "$arrivalLocation3",
                                "bookingcode" => "$bookingcode3",
                                "departureTerminal" => "$departureTerminal3",
                                "arrivalTerminal" => "$arrivalTerminal3",

                            ),

                            "3" => array(
                                "departure" => "$departure4",
                                "arrival" => "$arrival4",
                                "departureTime" => "$departureTime4",
                                "arrivalTime" => "$arrivalTime4",
                                "marketingCareerName" => "$marketingCareerName4",
                                "marketingCareer" => "$marketingCareer4",
                                "marketingFlight" => "$marketingFlight4",
                                "operatingCareer" => "$operatingCareer4",
                                "flightDuration" => "$flightDuration4",
                                "departureAirport" => "$departureAirport4",
                                "arrivalAirport" => "$arrivalAirport4",
                                "departureLocation" => "$departureLocation4",
                                "arrivalLocation" => "$arrivalLocation4",
                                "bookingcode" => "$bookingcode4",
                                "departureTerminal" => "$departureTerminal4",
                                "arrivalTerminal" => "$arrivalTerminal4",

                            ),
                        );

                    }

                    $FlightData = array(
                        "system" => $system,
                        "segment_id" => $segment,
                        "segment" => $segmentsData,
                    );
                }

            } else if ($tripType == 'return') {
                $allFlightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);

                if (isset($allFlightData[0])) {
                    $flightData = $allFlightData[0];
                    $segment = $flightData['segment'];

                    if ($segment == 1) {
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                )),

                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1)),

                        );

                    } else if ($segment == 2) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                ),
                                "1" => array(
                                    "goTransit" => $goTransit2,
                                    "goMarketingCareer" => $goMarketingCareer2,
                                    "goOperatingCareer" => $goOperatingCareer2,
                                    "goOperatingFlight" => $goOperatingFlight2,
                                    "goDeparture" => $goDeparture2,
                                    "goArrival" => $goArrival2,
                                    "goDepartureAirport" => $goDepartureAirport2,
                                    "goArrivalAirport" => $goArrivalAirport2,
                                    "goDepartureLocation" => $goDepartureLocation2,
                                    "goArrivalLocation" => $goArrivalLocation2,
                                    "goDepartureTime" => $goDepartureTime2,
                                    "goArrivalTime" => $goArrivalTime2,
                                    "goFlightDuration" => $goFlightDuration2,
                                    "goBookingCode" => $goBookingCode2,
                                    "goDepTerminal" => $goDepTerminal2,
                                    "goArrTerminal" => $goArrTerminal2,
                                )),

                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                                "1" => array(
                                    "backTransit" => $backTransit2,
                                    "backMarketingCareer" => $backMarketingCareer2,
                                    "backMarketingCareerName" => $backMarketingCareerName2,
                                    "backMarketingFlight" => $backMarketingFlight2,
                                    "backOperatingCareer" => $backOperatingCareer2,
                                    "backOperatingFlight" => $backOperatingFlight2,
                                    "backDeparture" => $backDeparture2,
                                    "backArrival" => $backArrival2,
                                    "backDepartureAirport" => $backDepartureAirport2,
                                    "backArrivalAirport" => $backArrivalAirport2,
                                    "backDepartureLocation" => $backDepartureLocation2,
                                    "backArrivalLocation" => $backArrivalLocation2,
                                    "backDepartureTime" => $backDepartureTime2,
                                    "backArrivalTime" => $backArrivalTime2,
                                    "backFlightDuration" => $backFlightDuration2,
                                    "backBookingCode" => $backBookingCode2,
                                    "backdepTerminal" => $backdepTerminal2,
                                    "backArrTerminal" => $backArrTerminal2),
                            ),
                        );

                    } else if ($segment == 12) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                ),
                            ),
                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                                "1" => array(
                                    "backTransit" => $backTransit2,
                                    "backMarketingCareer" => $backMarketingCareer2,
                                    "backMarketingCareerName" => $backMarketingCareerName2,
                                    "backMarketingFlight" => $backMarketingFlight2,
                                    "backOperatingCareer" => $backOperatingCareer2,
                                    "backOperatingFlight" => $backOperatingFlight2,
                                    "backDeparture" => $backDeparture2,
                                    "backArrival" => $backArrival2,
                                    "backDepartureAirport" => $backDepartureAirport2,
                                    "backArrivalAirport" => $backArrivalAirport2,
                                    "backDepartureLocation" => $backDepartureLocation2,
                                    "backArrivalLocation" => $backArrivalLocation2,
                                    "backDepartureTime" => $backDepartureTime2,
                                    "backArrivalTime" => $backArrivalTime2,
                                    "backFlightDuration" => $backFlightDuration2,
                                    "backBookingCode" => $backBookingCode2,
                                    "backdepTerminal" => $backdepTerminal2,
                                    "backArrTerminal" => $backArrTerminal2),
                            ),
                        );

                    } else if ($segment == 21) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];

                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(

                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1),

                                "1" => array(
                                    "goTransit" => $goTransit2,
                                    "goMarketingCareer" => $goMarketingCareer2,
                                    "goOperatingCareer" => $goOperatingCareer2,
                                    "goOperatingFlight" => $goOperatingFlight2,
                                    "goDeparture" => $goDeparture2,
                                    "goArrival" => $goArrival2,
                                    "goDepartureAirport" => $goDepartureAirport2,
                                    "goArrivalAirport" => $goArrivalAirport2,
                                    "goDepartureLocation" => $goDepartureLocation2,
                                    "goArrivalLocation" => $goArrivalLocation2,
                                    "goDepartureTime" => $goDepartureTime2,
                                    "goArrivalTime" => $goArrivalTime2,
                                    "goFlightDuration" => $goFlightDuration2,
                                    "goBookingCode" => $goBookingCode2,
                                    "goDepTerminal" => $goDepTerminal2,
                                    "goArrTerminal" => $goArrTerminal2),

                            ),

                            "back" => array(

                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                            ),
                        );

                    }

                    $FlightData = array(
                        "System" => $system,
                        "Segment_id" => $segment,
                        "Segment_data" => $segmentsData,
                    );

                }
            }

            $activitylog = $conn->query("SELECT * FROM  `activitylog` where ref='$bookingId'")->fetch_all(MYSQLI_ASSOC);
            $TicketInfo = $conn->query("SELECT DISTINCT  * FROM ticketed WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            $response = $row;
            $response['passenger'] = $PassengerData;
            $response['activity'] = $activitylog;
            $response['flightData'] = $FlightData;
            $response['ticketData'] = $TicketInfo;

            array_push($return_arr, $response);
        }
    }

    echo json_encode($return_arr);

}else if (array_key_exists("agentId", $_GET) && array_key_exists("page", $_GET)) {
    $page = $_GET['page'];
    $agentId = $_GET['agentId'];
    $result_per_page = 20;
    $page_first_result = ($page - 1) * $result_per_page;

    $staffId = "";
    $sql = "SELECT * FROM `booking` where agentId='$agentId' ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
    $result = $conn->query($sql);
    $totaldata = $conn->query("SELECT * FROM `booking` where agentId='$agentId'")->num_rows;

    $return_arr = array();
    $Data = array();
    $count = 0;
    if ($result->num_rows > 0) { 
        while ($row = $result->fetch_assoc()) {
            $count++;
            $staffId = $row['staffId'];
            $agentId = $row['agentId'];
            $pnr = $row['pnr'];
            $tripType = $row['tripType'];

            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);
            if (!empty($data)) {
                $companyname = $data['company'];
                $companyphone = $data['phone'];
            }

            $staffsql = mysqli_query($conn, "SELECT * FROM staffList WHERE staffId='$staffId' AND agentId ='$agentId'");
            $staffRow = mysqli_fetch_array($staffsql, MYSQLI_ASSOC);

            if (!empty($staffRow)) {
                $staffName = $staffRow['name'];
            } else {
                $staffName = "Agent";
            }

            if($tripType == 'oneway' || $tripType == '1'){
                $FlightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
                $FlightDate = $FlightData[0]['departureTime1'];
            }else{
                $FlightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
                // $FlightDate = $FlightData[0]['goDepartureTime1'];
                $FlightDate = isset($FlightData[0]['goDepartureTime1']) ? $FlightData[0]['goDepartureTime1'] :" ";
            }


            $response = $row;
            $response['companyname'] = "$companyname";
            $response['companyphone'] = "$companyphone";
            $response['bookedby'] = "$staffName";
             $response['flightdate'] = "$FlightDate";
            $response['serial']="$count";

            array_push($Data, $response);
        }
    }

    $return_arr['total'] = $totaldata;
    $return_arr['data_per_page'] = $result_per_page;
    $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
    $return_arr['data'] = $Data;
        

    echo json_encode($return_arr);
}else if (array_key_exists("agentId", $_GET) &&  array_key_exists("status", $_GET) && array_key_exists("pages", $_GET)) {
    
    $page = $_GET['pages'];
    $agentId = $_GET['agentId'];
    $status = $_GET['status'];
    $result_per_page = 20;
    $page_first_result = ($page - 1) * $result_per_page;

    $staffId = "";
    $sql = "SELECT * FROM `booking` where status='$status' AND agentId='$agentId' ORDER BY id DESC LIMIT $page_first_result,$result_per_page";
    $result = $conn->query($sql);
    $totaldata = $conn->query("SELECT * FROM `booking` where agentId='$agentId'")->num_rows;

    $return_arr = array();
    $Data = array();
    $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count++;
            $staffId = $row['staffId'];
            $agentId = $row['agentId'];

            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);
            if (!empty($data)) {
                $companyname = $data['company'];
                $companyphone = $data['phone'];
            }

            $staffsql = mysqli_query($conn, "SELECT * FROM staffList WHERE staffId='$staffId' AND agentId ='$agentId'");
            $staffRow = mysqli_fetch_array($staffsql, MYSQLI_ASSOC);

            if (!empty($staffRow)) {
                $staffName = $staffRow['name'];
            } else {
                $staffName = "Agent";
            }


            $response = $row;
            $response['companyname'] = "$companyname";
            $response['companyphone'] = "$companyphone";
            $response['bookedby'] = "$staffName";
            $response['serial']="$count";

            array_push($Data, $response);
        }
    }

    $return_arr['total'] = $totaldata;
    $return_arr['data_per_page'] = $result_per_page;
    $return_arr['number_of_page'] = ceil(($totaldata) / $result_per_page);
    $return_arr['data'] = $Data;
        

    echo json_encode($return_arr);
}else if (array_key_exists("userId", $_GET) && array_key_exists("bookingid", $_GET)) {

    $bookingId = $_GET["bookingid"];
    $userId = $_GET["userId"];
    $sql = "SELECT * FROM `booking` where userId='$userId' AND bookingId='$bookingId'";
    $result = $conn->query($sql);

    $return_arr = array();
    if ($result->num_rows > 0) {     
        while ($row = $result->fetch_assoc()) { 
            $staffId = $row['staffId'];
            $agentId = $row['agentId'];
            $pnr = $row['pnr'];
            $tripType = $row['tripType'];
            
            $staffsql = mysqli_query($conn, "SELECT * FROM staffList WHERE staffId='$staffId'");
            

            $staffRow = mysqli_fetch_array($staffsql, MYSQLI_ASSOC);

            if (!empty($staffRow)) {
                $staffName = $staffRow['name'];
            } else {
                $staffName = "Agent";
            }

            $PassengerData = $conn->query("SELECT * FROM  `passengers` where bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            if ($tripType == 'oneway' || $tripType == 1) {
                $allFlightData = $conn->query("SELECT * FROM segment_one_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
                //print_r($allFlightData);
                if (isset($allFlightData[0])) {
                    $flightData = $allFlightData[0];
                    $segment = $flightData['segment'];

                    if ($segment == 1) {
                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['marketingCareerName1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),

                        );

                    } else if ($segment == 2) {

                        //segment - 1;

                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['marketingCareerName1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['marketingCareerName2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),
                        );

                    } else if ($segment == 3) {

                        //segment - 1;

                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['marketingCareerName2'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['marketingCareerName2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        //segment -3
                        $departure3 = $flightData['departure3'];
                        $arrival3 = $flightData['arrival3'];
                        $departureTime3 = $flightData['departureTime3'];
                        $arrivalTime3 = $flightData['arrivalTime3'];
                        $marketingCareerName3 = $flightData['marketingCareerName3'];
                        $marketingCareer3 = $flightData['marketingCareer3'];
                        $marketingFlight3 = $flightData['marketingFlight3'];
                        $operatingCareer3 = $flightData['operatingCareer3'];
                        $flightDuration3 = $flightData['flightDuration3'];
                        $departureAirport3 = $flightData['departureAirport3'];
                        $arrivalAirport3 = $flightData['arrivalAirport3'];
                        $departureLocation3 = $flightData['departureLocation3'];
                        $arrivalLocation3 = $flightData['arrivalLocation3'];
                        $bookingcode3 = $flightData['bookingcode3'];
                        $departureTerminal3 = $flightData['departureTerminal3'];
                        $arrivalTerminal3 = $flightData['arrivalTerminal3'];

                        $segmentsData = array(
                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),

                            "2" => array(
                                "departure" => "$departure3",
                                "arrival" => "$arrival3",
                                "departureTime" => "$departureTime3",
                                "arrivalTime" => "$arrivalTime3",
                                "marketingCareerName" => "$marketingCareerName3",
                                "marketingCareer" => "$marketingCareer3",
                                "marketingFlight" => "$marketingFlight3",
                                "operatingCareer" => "$operatingCareer3",
                                "flightDuration" => "$flightDuration3",
                                "departureAirport" => "$departureAirport3",
                                "arrivalAirport" => "$arrivalAirport3",
                                "departureLocation" => "$departureLocation3",
                                "arrivalLocation" => "$arrivalLocation3",
                                "bookingcode" => "$bookingcode3",
                                "departureTerminal" => "$departureTerminal3",
                                "arrivalTerminal" => "$arrivalTerminal3",

                            ),
                        );

                    } else if ($segment == 4) {

                        //segment - 1;
                        $system = $flightData['system'];
                        $departure1 = $flightData['departure1'];
                        $arrival1 = $flightData['arrival1'];
                        $departureTime1 = $flightData['departureTime1'];
                        $arrivalTime1 = $flightData['arrivalTime1'];
                        $marketingCareerName1 = $flightData['marketingCareerName1'];
                        $marketingCareer1 = $flightData['marketingCareer1'];
                        $marketingFlight1 = $flightData['marketingFlight1'];
                        $operatingCareer1 = $flightData['operatingCareer1'];
                        $flightDuration1 = $flightData['flightDuration1'];
                        $departureAirport1 = $flightData['departureAirport1'];
                        $arrivalAirport1 = $flightData['arrivalAirport1'];
                        $departureLocation1 = $flightData['departureLocation1'];
                        $arrivalLocation1 = $flightData['arrivalLocation1'];
                        $bookingcode1 = $flightData['bookingcode1'];
                        $departureTerminal1 = $flightData['departureTerminal1'];
                        $arrivalTerminal1 = $flightData['arrivalTerminal1'];

                        //segment 2

                        $departure2 = $flightData['departure2'];
                        $arrival2 = $flightData['arrival2'];
                        $departureTime2 = $flightData['departureTime2'];
                        $arrivalTime2 = $flightData['arrivalTime2'];
                        $marketingCareerName2 = $flightData['marketingCareerName2'];
                        $marketingCareer2 = $flightData['marketingCareer2'];
                        $marketingFlight2 = $flightData['marketingFlight2'];
                        $operatingCareer2 = $flightData['operatingCareer2'];
                        $flightDuration2 = $flightData['flightDuration2'];
                        $departureAirport2 = $flightData['departureAirport2'];
                        $arrivalAirport2 = $flightData['arrivalAirport2'];
                        $departureLocation2 = $flightData['departureLocation2'];
                        $arrivalLocation2 = $flightData['arrivalLocation2'];
                        $bookingcode2 = $flightData['bookingcode2'];
                        $departureTerminal2 = $flightData['departureTerminal2'];
                        $arrivalTerminal2 = $flightData['arrivalTerminal2'];

                        //segment -3
                        $departure3 = $flightData['departure3'];
                        $arrival3 = $flightData['arrival3'];
                        $departureTime3 = $flightData['departureTime3'];
                        $arrivalTime3 = $flightData['arrivalTime3'];
                        $marketingCareerName3 = $flightData['marketingCareerName3'];
                        $marketingCareer3 = $flightData['marketingCareer3'];
                        $marketingFlight3 = $flightData['marketingFlight3'];
                        $operatingCareer3 = $flightData['operatingCareer3'];
                        $flightDuration3 = $flightData['flightDuration3'];
                        $departureAirport3 = $flightData['departureAirport3'];
                        $arrivalAirport3 = $flightData['arrivalAirport3'];
                        $departureLocation3 = $flightData['departureLocation3'];
                        $arrivalLocation3 = $flightData['arrivalLocation3'];
                        $bookingcode3 = $flightData['bookingcode3'];
                        $departureTerminal3 = $flightData['departureTerminal3'];
                        $arrivalTerminal3 = $flightData['arrivalTerminal3'];

                        //segment 4
                        $departure4 = $flightData['departure4'];
                        $arrival4 = $flightData['arrival4'];
                        $departureTime4 = $flightData['departureTime4'];
                        $arrivalTime4 = $flightData['arrivalTime4'];
                        $marketingCareerName4 = $flightData['marketingCareerName4'];
                        $marketingCareer4 = $flightData['marketingCareer4'];
                        $marketingFlight4 = $flightData['marketingFlight4'];
                        $operatingCareer4 = $flightData['operatingCareer4'];
                        $flightDuration4 = $flightData['flightDuration4'];
                        $departureAirport4 = $flightData['departureAirport4'];
                        $arrivalAirport4 = $flightData['arrivalAirport4'];
                        $departureLocation4 = $flightData['departureLocation4'];
                        $arrivalLocation4 = $flightData['arrivalLocation4'];
                        $bookingcode4 = $flightData['bookingcode4'];
                        $departureTerminal4 = $flightData['departureTerminal4'];
                        $arrivalTerminal4 = $flightData['arrivalTerminal4'];

                        $segmentsData = array(

                            "0" => array(
                                "departure" => "$departure1",
                                "arrival" => "$arrival1",
                                "departureTime" => "$departureTime1",
                                "arrivalTime" => "$arrivalTime1",
                                "marketingCareerName" => "$marketingCareerName1",
                                "marketingCareer" => "$marketingCareer1",
                                "marketingFlight" => "$marketingFlight1",
                                "operatingCareer" => "$operatingCareer1",
                                "flightDuration" => "$flightDuration1",
                                "departureAirport" => "$departureAirport1",
                                "arrivalAirport" => "$arrivalAirport1",
                                "departureLocation" => "$departureLocation1",
                                "arrivalLocation" => "$arrivalLocation1",
                                "bookingcode" => "$bookingcode1",
                                "departureTerminal" => "$departureTerminal1",
                                "arrivalTerminal" => "$arrivalTerminal1",

                            ),
                            "1" => array(
                                "departure" => "$departure2",
                                "arrival" => "$arrival2",
                                "departureTime" => "$departureTime2",
                                "arrivalTime" => "$arrivalTime2",
                                "marketingCareerName" => "$marketingCareerName2",
                                "marketingCareer" => "$marketingCareer2",
                                "marketingFlight" => "$marketingFlight2",
                                "operatingCareer" => "$operatingCareer2",
                                "flightDuration" => "$flightDuration2",
                                "departureAirport" => "$departureAirport2",
                                "arrivalAirport" => "$arrivalAirport2",
                                "departureLocation" => "$departureLocation2",
                                "arrivalLocation" => "$arrivalLocation2",
                                "bookingcode" => "$bookingcode2",
                                "departureTerminal" => "$departureTerminal2",
                                "arrivalTerminal" => "$arrivalTerminal2",

                            ),

                            "2" => array(
                                "departure" => "$departure3",
                                "arrival" => "$arrival3",
                                "departureTime" => "$departureTime3",
                                "arrivalTime" => "$arrivalTime3",
                                "marketingCareerName" => "$marketingCareerName3",
                                "marketingCareer" => "$marketingCareer3",
                                "marketingFlight" => "$marketingFlight3",
                                "operatingCareer" => "$operatingCareer3",
                                "flightDuration" => "$flightDuration3",
                                "departureAirport" => "$departureAirport3",
                                "arrivalAirport" => "$arrivalAirport3",
                                "departureLocation" => "$departureLocation3",
                                "arrivalLocation" => "$arrivalLocation3",
                                "bookingcode" => "$bookingcode3",
                                "departureTerminal" => "$departureTerminal3",
                                "arrivalTerminal" => "$arrivalTerminal3",

                            ),

                            "3" => array(
                                "departure" => "$departure4",
                                "arrival" => "$arrival4",
                                "departureTime" => "$departureTime4",
                                "arrivalTime" => "$arrivalTime4",
                                "marketingCareerName" => "$marketingCareerName4",
                                "marketingCareer" => "$marketingCareer4",
                                "marketingFlight" => "$marketingFlight4",
                                "operatingCareer" => "$operatingCareer4",
                                "flightDuration" => "$flightDuration4",
                                "departureAirport" => "$departureAirport4",
                                "arrivalAirport" => "$arrivalAirport4",
                                "departureLocation" => "$departureLocation4",
                                "arrivalLocation" => "$arrivalLocation4",
                                "bookingcode" => "$bookingcode4",
                                "departureTerminal" => "$departureTerminal4",
                                "arrivalTerminal" => "$arrivalTerminal4",

                            ),
                        );

                    }

                    $FlightData = array(
                        "system" => $system,
                        "segment_id" => $segment,
                        "segment" => $segmentsData,
                    );
                }

            }else if ($tripType == 'return' || $tripType == 2) {
                $allFlightData = $conn->query("SELECT * FROM segment_return_way where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);

                if (isset($allFlightData[0])) {
                    $flightData = $allFlightData[0];
                    $segment = $flightData['segment'];

                    if ($segment == 1) {
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goMarketingCareerName1 = $flightData['goMarketingCareerName1'];
                        $goMarketingFlight1 = $flightData['goMarketingFlight1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goMarketingCareerName" => $goMarketingCareerName1,
                                    "goMarketingFlight" => $goMarketingFlight1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                )),

                            "back" => array(
                                "0" => array(
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1)),

                        );

                    } else if ($segment == 2) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goMarketingCareerName1 = $flightData['goMarketingCareerName1'];
                        $goMarketingFlight1 = $flightData['goMarketingFlight1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goMarketingCareerName2 = $flightData['goMarketingCareerName2'];
                        $goMarketingFlight2 = $flightData['goMarketingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport2'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goMarketingCareerName" => $goMarketingCareerName1,
                                    "goMarketingFlight" => $goMarketingFlight1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                ),
                                "1" => array(
                                    "goTransit" => $goTransit2,
                                    "goMarketingCareer" => $goMarketingCareer2,
                                    "goOperatingCareer" => $goOperatingCareer2,
                                    "goOperatingFlight" => $goOperatingFlight2,
                                    "goMarketingCareerName" => $goMarketingCareerName2,
                                    "goMarketingFlight" => $goMarketingFlight2,
                                    "goDeparture" => $goDeparture2,
                                    "goArrival" => $goArrival2,
                                    "goDepartureAirport" => $goDepartureAirport2,
                                    "goArrivalAirport" => $goArrivalAirport2,
                                    "goDepartureLocation" => $goDepartureLocation2,
                                    "goArrivalLocation" => $goArrivalLocation2,
                                    "goDepartureTime" => $goDepartureTime2,
                                    "goArrivalTime" => $goArrivalTime2,
                                    "goFlightDuration" => $goFlightDuration2,
                                    "goBookingCode" => $goBookingCode2,
                                    "goDepTerminal" => $goDepTerminal2,
                                    "goArrTerminal" => $goArrTerminal2,
                                )),

                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                                "1" => array(
                                    "backTransit" => $backTransit2,
                                    "backMarketingCareer" => $backMarketingCareer2,
                                    "backMarketingCareerName" => $backMarketingCareerName2,
                                    "backMarketingFlight" => $backMarketingFlight2,
                                    "backOperatingCareer" => $backOperatingCareer2,
                                    "backOperatingFlight" => $backOperatingFlight2,
                                    "backDeparture" => $backDeparture2,
                                    "backArrival" => $backArrival2,
                                    "backDepartureAirport" => $backDepartureAirport2,
                                    "backArrivalAirport" => $backArrivalAirport2,
                                    "backDepartureLocation" => $backDepartureLocation2,
                                    "backArrivalLocation" => $backArrivalLocation2,
                                    "backDepartureTime" => $backDepartureTime2,
                                    "backArrivalTime" => $backArrivalTime2,
                                    "backFlightDuration" => $backFlightDuration2,
                                    "backBookingCode" => $backBookingCode2,
                                    "backdepTerminal" => $backdepTerminal2,
                                    "backArrTerminal" => $backArrTerminal2),
                            ),
                        );

                    } else if ($segment == 12) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];
                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(
                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goMarketingCareerName" => $goMarketingCareerName1,
                                    "goMarketingFlight" => $goMarketingFlight1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1,
                                ),
                            ),
                            "back" => array(
                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                                "1" => array(
                                    "backTransit" => $backTransit2,
                                    "backMarketingCareer" => $backMarketingCareer2,
                                    "backMarketingCareerName" => $backMarketingCareerName2,
                                    "backMarketingFlight" => $backMarketingFlight2,
                                    "backOperatingCareer" => $backOperatingCareer2,
                                    "backOperatingFlight" => $backOperatingFlight2,
                                    "backDeparture" => $backDeparture2,
                                    "backArrival" => $backArrival2,
                                    "backDepartureAirport" => $backDepartureAirport2,
                                    "backArrivalAirport" => $backArrivalAirport2,
                                    "backDepartureLocation" => $backDepartureLocation2,
                                    "backArrivalLocation" => $backArrivalLocation2,
                                    "backDepartureTime" => $backDepartureTime2,
                                    "backArrivalTime" => $backArrivalTime2,
                                    "backFlightDuration" => $backFlightDuration2,
                                    "backBookingCode" => $backBookingCode2,
                                    "backdepTerminal" => $backdepTerminal2,
                                    "backArrTerminal" => $backArrTerminal2),
                            ),
                        );

                    } else if ($segment == 21) {

                        //segment - 1
                        $system = $flightData['system'];
                        $goTransit1 = $flightData['goTransit1'];
                        $backTransit1 = $flightData['backTransit1'];
                        $goMarketingCareer1 = $flightData['goMarketingCareer1'];
                        $goOperatingCareer1 = $flightData['goOperatingCareer1'];
                        $goOperatingFlight1 = $flightData['goOperatingFlight1'];
                        $goDeparture1 = $flightData['goDeparture1'];
                        $goArrival1 = $flightData['goArrival1'];
                        $goDepartureAirport1 = $flightData['goDepartureAirport1'];
                        $goArrivalAirport1 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation1 = $flightData['goDepartureLocation1'];
                        $goArrivalLocation1 = $flightData['goArrivalLocation1'];
                        $goDepartureTime1 = $flightData['goDepartureTime1'];
                        $goArrivalTime1 = $flightData['goArrivalTime1'];
                        $goFlightDuration1 = $flightData['goFlightDuration1'];
                        $goBookingCode1 = $flightData['goBookingCode1'];
                        $goDepTerminal1 = $flightData['goDepTerminal1'];
                        $goArrTerminal1 = $flightData['goArrTerminal1'];

                        $backMarketingCareer1 = $flightData['backMarketingCareer1'];
                        $backMarketingCareerName1 = $flightData['backMarketingCareerName1'];
                        $backMarketingFlight1 = $flightData['backMarketingFlight1'];
                        $backOperatingCareer1 = $flightData['backOperatingCareer1'];
                        $backOperatingFlight1 = $flightData['backOperatingFlight1'];
                        $backDeparture1 = $flightData['backDeparture1'];
                        $backArrival1 = $flightData['backArrival1'];
                        $backDepartureAirport1 = $flightData['backDepartureAirport1'];
                        $backArrivalAirport1 = $flightData['backArrivalAirport1'];
                        $backDepartureLocation1 = $flightData['backDepartureLocation1'];
                        $backArrivalLocation1 = $flightData['backArrivalLocation1'];
                        $backDepartureTime1 = $flightData['backDepartureTime1'];
                        $backArrivalTime1 = $flightData['backArrivalTime1'];
                        $backFlightDuration1 = $flightData['backFlightDuration1'];
                        $backBookingCode1 = $flightData['backBookingCode1'];
                        $backdepTerminal1 = $flightData['backdepTerminal1'];
                        $backArrTerminal1 = $flightData['backArrTerminal1'];

                        // segment 2
                        $goTransit2 = $flightData['goTransit2'];
                        $goMarketingCareer2 = $flightData['goMarketingCareer2'];
                        $goOperatingCareer2 = $flightData['goOperatingCareer2'];
                        $goOperatingFlight2 = $flightData['goOperatingFlight2'];
                        $goDeparture2 = $flightData['goDeparture2'];
                        $goArrival2 = $flightData['goArrival2'];
                        $goDepartureAirport2 = $flightData['goDepartureAirport2'];
                        $goArrivalAirport2 = $flightData['goArrivalAirport1'];
                        $goDepartureLocation2 = $flightData['goDepartureLocation2'];
                        $goArrivalLocation2 = $flightData['goArrivalLocation2'];
                        $goDepartureTime2 = $flightData['goDepartureTime2'];
                        $goArrivalTime2 = $flightData['goArrivalTime2'];
                        $goFlightDuration2 = $flightData['goFlightDuration2'];
                        $goBookingCode2 = $flightData['goBookingCode2'];
                        $goDepTerminal2 = $flightData['goDepTerminal2'];
                        $goArrTerminal2 = $flightData['goArrTerminal2'];
                        $backTransit2 = $flightData['backTransit2'];
                        $backMarketingCareer2 = $flightData['backMarketingCareer2'];
                        $backMarketingCareerName2 = $flightData['backMarketingCareerName2'];
                        $backMarketingFlight2 = $flightData['backMarketingFlight2'];
                        $backOperatingCareer2 = $flightData['backOperatingCareer2'];
                        $backOperatingFlight2 = $flightData['backOperatingFlight2'];
                        $backDeparture2 = $flightData['backDeparture2'];
                        $backArrival2 = $flightData['backArrival2'];
                        $backDepartureAirport2 = $flightData['backDepartureAirport2'];
                        $backArrivalAirport2 = $flightData['backArrivalAirport2'];
                        $backDepartureLocation2 = $flightData['backDepartureLocation2'];
                        $backArrivalLocation2 = $flightData['backArrivalLocation2'];
                        $backDepartureTime2 = $flightData['backDepartureTime2'];
                        $backArrivalTime2 = $flightData['backArrivalTime2'];
                        $backFlightDuration2 = $flightData['backFlightDuration2'];
                        $backBookingCode2 = $flightData['backBookingCode2'];
                        $backdepTerminal2 = $flightData['backdepTerminal2'];
                        $backArrTerminal2 = $flightData['backArrTerminal2'];

                        $segmentsData = array(

                            "go" => array(
                                "0" => array(
                                    "goTransit" => $goTransit1,
                                    "goMarketingCareer" => $goMarketingCareer1,
                                    "goOperatingCareer" => $goOperatingCareer1,
                                    "goOperatingFlight" => $goOperatingFlight1,
                                    "goMarketingFlight" => $goMarketingFlight1,
                                    "goDeparture" => $goDeparture1,
                                    "goArrival" => $goArrival1,
                                    "goDepartureAirport" => $goDepartureAirport1,
                                    "goArrivalAirport" => $goArrivalAirport1,
                                    "goDepartureLocation" => $goDepartureLocation1,
                                    "goArrivalLocation" => $goArrivalLocation1,
                                    "goDepartureTime" => $goDepartureTime1,
                                    "goArrivalTime" => $goArrivalTime1,
                                    "goFlightDuration" => $goFlightDuration1,
                                    "goBookingCode" => $goBookingCode1,
                                    "goDepTerminal" => $goDepTerminal1,
                                    "goArrTerminal" => $goArrTerminal1),

                                "1" => array(
                                    "goTransit" => $goTransit2,
                                    "goMarketingCareer" => $goMarketingCareer2,
                                    "goOperatingCareer" => $goOperatingCareer2,
                                    "goOperatingFlight" => $goOperatingFlight2,
                                    "goDeparture" => $goDeparture2,
                                    "goArrival" => $goArrival2,
                                    "goDepartureAirport" => $goDepartureAirport2,
                                    "goArrivalAirport" => $goArrivalAirport2,
                                    "goDepartureLocation" => $goDepartureLocation2,
                                    "goArrivalLocation" => $goArrivalLocation2,
                                    "goDepartureTime" => $goDepartureTime2,
                                    "goArrivalTime" => $goArrivalTime2,
                                    "goFlightDuration" => $goFlightDuration2,
                                    "goBookingCode" => $goBookingCode2,
                                    "goDepTerminal" => $goDepTerminal2,
                                    "goArrTerminal" => $goArrTerminal2),

                            ),

                            "back" => array(

                                "0" => array(
                                    "backTransit" => $backTransit1,
                                    "backMarketingCareer" => $backMarketingCareer1,
                                    "backMarketingCareerName" => $backMarketingCareerName1,
                                    "backMarketingFlight" => $backMarketingFlight1,
                                    "backOperatingCareer" => $backOperatingCareer1,
                                    "backOperatingFlight" => $backOperatingFlight1,
                                    "backDeparture" => $backDeparture1,
                                    "backArrival" => $backArrival1,
                                    "backDepartureAirport" => $backDepartureAirport1,
                                    "backArrivalAirport" => $backArrivalAirport1,
                                    "backDepartureLocation" => $backDepartureLocation1,
                                    "backArrivalLocation" => $backArrivalLocation1,
                                    "backDepartureTime" => $backDepartureTime1,
                                    "backArrivalTime" => $backArrivalTime1,
                                    "backFlightDuration" => $backFlightDuration1,
                                    "backBookingCode" => $backBookingCode1,
                                    "backdepTerminal" => $backdepTerminal1,
                                    "backArrTerminal" => $backArrTerminal1),

                            ),
                        );

                    }

                    $FlightData = array(
                        "System" => $system,
                        "Segment_id" => $segment,
                        "Segment_data" => $segmentsData,
                    );

                }

            }

            // $Notes = $conn->query("SELECT * FROM `notes` where reference='$bookingId'")->fetch_all(MYSQLI_ASSOC);
            // $activitylog = $conn->query("SELECT * FROM  `activitylog` where ref='$bookingId'")->fetch_all(MYSQLI_ASSOC);
            $TicketInfo = $conn->query("SELECT DISTINCT  * FROM ticketed WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            $response = $row;
            $response['passenger'] = $PassengerData;
           // $response['activity'] = $activitylog;
            $response['flightData'] = $FlightData;
           // $response['notes'] = $Notes;
            $response['ticketData'] = $TicketInfo;

            array_push($return_arr, $response);
        }
    }else{
        $response['status'] = 'error';
        $response['message'] = "Booking Data Not Found";
        echo json_encode($response);
    }

    echo json_encode($return_arr);

}