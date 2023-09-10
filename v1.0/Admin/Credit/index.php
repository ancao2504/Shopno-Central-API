<?php

require_once '../../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("balance", $_GET) && array_key_exists("agentId", $_GET)) {
    $agentId = $_GET["agentId"];

    // Sub Agent Checker
    $agentChecker = $conn->query("SELECT 'agentId', 'status' FROM agent WHERE agentId='$agentId' AND status='active'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($agentChecker)) {
        $todayDate = date("Y-m-d");

        $agentData = $conn->query("SELECT b.*, l.lastAmount
                FROM booking b
                JOIN (
                    SELECT lastAmount
                    FROM agent_ledger
                    WHERE agentId = '$agentId'
                    ORDER BY id DESC
                    LIMIT 1
                ) l
                ON b.agentId = '$agentId' AND b.status = 'Hold'")->fetch_all(MYSQLI_ASSOC);

        if (!empty($agentData)) {
            echo json_encode($agentData);
        } else {
            echo json_encode([]);
        }

    } else {
        $response['status'] = "error";
        $response['message'] = "Agent not found";
        echo json_encode($response);
        exit();
    }

} else if (array_key_exists("creditadd", $_GET)) {

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_POST = json_decode(file_get_contents("php://input"), true);
        $agentId = $_POST['agentId'];
        $remainingBooking = $_POST['remaining_booking'];
        $lastFlightDate = $_POST['last_flight_date'];
        $currentBalance = $_POST['current_balance'];
        $creditAmount = $_POST['credit_amount'];
        $reason = $_POST['reason'];
        $createdAt = date("Y-m-d H:i:s");

        // Agent Checker
        $agentChecker = $conn->query("SELECT 'agentId', 'status' FROM agent WHERE agentId='$agentId' AND status='active'")->fetch_all(MYSQLI_ASSOC);

        if (!empty($agentChecker)) {
                    saveData($agentId, $creditAmount, $currentBalance, $createdAt, $reason, $remainingBooking, $lastFlightDate, $conn);
        } else {
            $response['status'] = 'error';
            $response['message'] = "Agent not found";
            echo json_encode($response);
            exit();
        }

        // echo json_encode($response);
    }

} else if (array_key_exists('all', $_GET)) {

    $data = $conn->query("SELECT * FROM credit WHERE platform='B2B'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
        echo json_encode($data);
    } else {
        $response['status'] = 'error';
        $response['message'] = "Data not found";
        echo json_encode($response);
    }
} else if (array_key_exists('agentId', $_GET)) {
    $agentId = $_GET['agentId'];

    $data = $conn->query("SELECT * FROM credit WHERE agentId='$agentId' AND platform='B2B'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
        echo json_encode($data);
    } else {
        $response['status'] = 'error';
        $response['message'] = "Data not found";
        echo json_encode($response);
    }
}
// Method for Insert or Update request

function saveData($agentId, $creditAmount, $currentBalance, $createdAt, $reason, $remainingBooking, $lastFlightDate, $conn)
{
    $creditAmountSql = $conn->query("SELECT credit FROM `agent` WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);

    if (!empty($creditAmountSql[0]['credit'])) {
        $NewCreditAmount = $creditAmountSql[0]['credit'] + $creditAmount;
    } else {
        $NewCreditAmount = $creditAmount;
    }

    
    // Credit Id Generated
    $data = $conn->query("SELECT id, creditId FROM credit ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);

    if (!empty($data[0]['creditId'])) {
        $id = $data[0]['id'];
        $getCreditId = preg_replace("/[^0-9]/", '', $data[0]['creditId']);
        $creditId = "STL".$id;
    } else {
        $creditId = "STL1000";
    }

    $conn->query("UPDATE agent SET credit='$NewCreditAmount' WHERE agentId='$agentId'");

    $conn->query("INSERT INTO `agent_ledger`(`agentId`,`platform`,`loan`, `lastAmount`,`details`, `transactionId`,`reference`,`createdAt`)
                      VALUES ('$agentId','B2B','$creditAmount','$currentBalance','$creditAmount TK Credit By Shopno Tours & Travels','$creditId','','$createdAt')");

    $sql = "INSERT INTO credit (agentId, creditId, platform,remaining_booking, last_flight_date,current_balance, credit_amount, reason, createdAt) VALUES ('$agentId','$creditId','B2B','$remainingBooking','$lastFlightDate','$currentBalance','$creditAmount','$reason', '$createdAt')";
    if ($conn->query($sql)) {
        $response['status'] = 'success';
        $response['message'] = "Credit added successfully";
    } else {
        $response['status'] = 'error';
        $response['message'] = "Credit query failed";
    }

    echo json_encode($response);

}
