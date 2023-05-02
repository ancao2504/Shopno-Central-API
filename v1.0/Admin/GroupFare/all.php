<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  if(array_key_exists('all', $_GET)){
  
    $sql = "SELECT * FROM groupfare ORDER BY id DESC";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $groupId = $row['groupId'];
            $vendor = $row['vendor'];
            $invoice = $row['invoice'];
            $career = $row['career'];
            $total = $row['total'];
            $BasePrice = $row['BasePrice'];
            $Taxes = $row['Taxes'];
            $price = $row['price'];
            $seat = $row['seat'];
            $bags = $row['bags'];
            $journeyTime = $row['journeyTime'];
            $segments = $row['segment'];

            if ($segments == 1) {

                $segmentsData = array("0" => array(
                    "vendor" => $vendor,
                    "invoice" => $invoice,
                    "career" => $career,
                    "total" => $total,
                    "basePrice" => $BasePrice,
                    "taxes" => $Taxes,
                    "price" => $price,
                    "bags" => $bags,
                    "seat" => $seat,
                    "journeyTime" => $journeyTime,
                    "departure" => $row['departure1'],
                    "depTime" => $row['depTime1'],
                    "arrival" => $row['arrival1'],
                    "arrTime" => $row['arrTime1'],
                    "flightNum" => $row['flightNum1'],
                    "transit" => $row['transit1'],

                ),

                );

                $basic = array(
                  "Segments" => $segments,
                  "groupId" => $groupId,
                  "data" => $segmentsData
                );
                array_push($return_arr, $basic);

            }
            if ($segments == 2) {
                $segmentsData = array(
                    "0" => array(
                   
                        "vendor" => $vendor,
                        "invoice" => $invoice,
                        "career" => $career,
                        "total" => $total,
                        "basePrice" => $BasePrice,
                        "taxes" => $Taxes,
                        "price" => $price,
                        "bags" => $bags,
                        "seat" => $seat,
                        "journeyTime" => $journeyTime,
                        "departure" => $row['departure1'],
                        "depTime" => $row['depTime1'],
                        "arrival" => $row['arrival1'],
                        "arrTime" => $row['arrTime1'],
                        "flightNum" => $row['flightNum1'],
                        "transit" => $row['transit1'],

                    ),
                    "1" => array(
                        "vendor" => $vendor,
                        "invoice" => $invoice,
                        "career" => $career,
                        "total" => $total,
                        "basePrice" => $BasePrice,
                        "taxes" => $Taxes,
                        "price" => $price,
                        "bags" => $bags,
                        "journeyTime" => $journeyTime,
                        "departure" => $row['departure2'],
                        "depTime" => $row['depTime2'],
                        "arrival" => $row['arrival2'],
                        "arrTime" => $row['arrTime2'],
                        "flightNum" => $row['flightNum2'],
                        "transit" => $row['transit2'],
                    ),
                );
                $basic = array(
                  "Segments" => $segments,
                  "groupId" => $groupId,
                  "bags" => $bags,
                  "data" => $segmentsData
                );
                array_push($return_arr, $basic);
            }
           
        }
    }

    echo json_encode($return_arr);
}