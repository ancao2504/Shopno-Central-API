<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if(array_key_exists("all", $_GET)){
            $sql = "SELECT * FROM agent WHERE platform = 'B2C'";
            $Data = array();
            if($result = $conn->query($sql)){
                while($row = $result->fetch_assoc()){
                    $UserId = $row["userId"];
                        /**Total Booking */ 
                    $bookingSql = "SELECT COUNT(*) FROM booking WHERE userId ='$UserId' AND status ='Ticketed'";
                    if($conn->query($bookingSql)){
                        $totalBooking = $bookingSql;
                    }else{
                        $totalBooking = 0;
                    }
                     /**Total Traveler */ 
                     $travelerSql = "SELECT COUNT(*) FROM passengers WHERE userId ='$UserId'";
                     if($conn->query($travelerSql)){
                         $totalTraveler = $travelerSql;
                     }else{
                         $totalTraveler = 0;
                     }

                    //   /**Total Search */ 
                    // $searchSql = "SELECT COUNT(*) FROM search_history WHERE userId ='$UserId'";
                    // if($conn->query($searchSql)){
                    //     $totalSearch = $searchSql;
                    // }else{
                    //     $totalSearch = 0;
                    // }
                    
                    /**Last Balance */ 
                    $BalanceSql =$conn->query("SELECT lastAmount FROM agent_ledger WHERE userId ='$UserId' LIMIT 1")->fetch_all(MYSQLI_ASSOC);
                    if(!empty($BalanceSql)){
                        $lastAmount = $BalanceSql;
                    }else{
                        $lastAmount = 0;
                    }

                    $response = $row;
                    // $response = $totalBooking;
                    // $response = $totalTraveler;
                   // $response = $totalSearch;
                    $response = $lastAmount;
                    array_push($Data, $response);
                }
                echo json_encode($Data);

            }else{
                $response=[];
                echo json_encode($response);
            }

        }else if(array_key_exists("status", $_GET)){
            $_POST = json_decode(file_get_contents("php://input"), true);
                $Status = $_POST['status'];
                $UserId = $_POST['userId'];
                $checker = $conn->query("SELECT userId FROM agent WHERE userId='$UserId' AND platform='B2C'")->fetch_all(MYSQLI_ASSOC);
                if(!empty($checker)){
                    $sql ="UPDATE agent SET status='$Status' WHERE userId='$UserId'AND platform='B2C'";
                    if($conn->query($sql)){
                        $response['status'] ="success";
                        $response['message'] ="User $Status";
                    }
                }else{
                    $response['status'] ="error";
                    $response['message'] ="User Not Found";
                    
                }
                echo json_encode($response);
        }
   
$conn->close();
?>