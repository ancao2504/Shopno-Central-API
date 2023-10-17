<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../../authorization.php';
if (authorization($conn) == true){  
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
    }else if(array_key_exists('search', $_GET)){
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $_POST = json_decode(file_get_contents('php://input'), true);
            $departure = $_POST['departure'];
                $arrival = $_POST['arrival'];
                $depTime = $_POST['depTime'];

                
            
                    $checker1 = mysqli_query($conn, "SELECT * FROM groupfare WHERE departure1 LIKE '$departure%' AND arrival1 LIKE '$arrival%' AND depTime1 LIKE '$depTime%' ORDER BY id DESC ")->fetch_all(MYSQLI_ASSOC);
                    $checker2 = mysqli_query($conn, "SELECT * FROM groupfare WHERE departure2 LIKE '$departure%' AND arrival2 LIKE '$arrival%' AND depTime2 LIKE '$depTime%' ORDER BY id DESC ")->fetch_all(MYSQLI_ASSOC);
                    if(!empty($checker1)){
                        $response['data'] = $checker1;
                    
                    }else if(!empty($checker2)){
                        $response['data'] = $checker2;
        
                    }else{
                    $response["status"] = "error";
                    $response["message"] = "Data Not Found";
                    
                    }
                echo json_encode($response);
            
        }
        

    }
}else{
  authorization($conn);
}