<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {
    // $sql = "SELECT * FROM agent WHERE platform = 'B2C'";
    // // echo json_encode($conn->query($sql)->fetch_all(MYSQLI_ASSOC));
    // $Data = array();
    // if ($result = $conn->query($sql)) {
        
    //     while ($row = $result->fetch_assoc()) {
    //         $UserId = $row["userId"];
            
    // //         /**Total Booking */
    // //         $bookingSql = "SELECT COUNT(*) FROM booking WHERE userId ='$UserId' AND status ='Ticketed'";
    // //         if ($conn->query($bookingSql)) {
    // //             $totalBooking = $bookingSql;
    // //         } else {
    // //             $totalBooking = 0;
    // //         }
    // //         /**Total Traveler */
    // //         $travelerSql = "SELECT COUNT(*) FROM passengers WHERE userId ='$UserId'";
    // //         if ($conn->query($travelerSql)) {
    // //             $totalTraveler = $travelerSql;
    // //         } else {
    // //             $totalTraveler = 0;
    // //         }

    // //         //   /**Total Search */ 
    // //         // $searchSql = "SELECT COUNT(*) FROM search_history WHERE userId ='$UserId'";
    // //         // if($conn->query($searchSql)){
    // //         //     $totalSearch = $searchSql;
    // //         // }else{
    // //         //     $totalSearch = 0;
    // //         // }

    // //         /**Last Balance */
    //         $BalanceSql = $conn->query("SELECT lastAmount FROM agent_ledger WHERE userId ='$UserId' LIMIT 1")->fetch_assoc();
    //         if (!empty($BalanceSql)) {
    //             $lastAmount = $BalanceSql["lastAmount"];
    //         } else {
    //             $lastAmount = 0;
    //         }
    //         $Data["lastAmount"] = $lastAmount;
    // //         $response = $row;
    // //         // $response = $totalBooking;
    // //         // $response = $totalTraveler;
    // //         // $response = $totalSearch;
    //         // $response = $lastAmount;
    // //         array_push($Data, $response);
    //     }
    //     // echo json_encode($Data);
    // } else {
    //     $response = [];
    //     echo json_encode($response);
    // }

    //////////////////////////////////Query Explanation///////////////////////////////////////////////
    /*
    1. COALESCE to handle cases where there may not be a corresponding ledger entry (defaulting to 0).
    2. LEFT JOIN operation is used to join between the agent table and the agent_ledger table, 
    allowing you to combine data from both tables priotizing left table which is agent Table.
    3. subquery within the LEFT JOIN condition. It ensures that you are joining with the latest 
    ledger entry for each user by finding the maximum id from the agent_ledger table for the same user
    */
    ////////////////////////////////////////////////////////////////////////////////////////////////////
    
    $respopnse=$conn->query(
    "SELECT A.id, A.userId,A.name, A.email, A.phone, COALESCE(AL.lastAmount, 0) AS lastAmount
    FROM agent AS A
    LEFT JOIN agent_ledger AS AL
    ON A.userId = AL.userId
    AND AL.id = (
            SELECT MAX(id)
            FROM agent_ledger AS subAL
            WHERE subAL.userId = A.userId
    )
    WHERE A.platform = 'B2C'
    ORDER BY A.userId DESC
    "
    )->fetch_all(MYSQLI_ASSOC);

    echo json_encode($respopnse);
    
} else if (array_key_exists("status", $_GET)) {
    $_POST = json_decode(file_get_contents("php://input"), true);
    $Status = $_POST['status'];
    $UserId = $_POST['userId'];
    $checker = $conn->query("SELECT userId FROM agent WHERE userId='$UserId' AND platform='B2C'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($checker)) {
        $sql = "UPDATE agent SET status='$Status' WHERE userId='$UserId'AND platform='B2C'";
        if ($conn->query($sql)) {
            $response['status'] = "success";
            $response['message'] = "User $Status";
        }
    } else {
        $response['status'] = "error";
        $response['message'] = "User Not Found";
    }
    echo json_encode($response);
}

$conn->close();
