<?php
require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$response = array();

if (array_key_exists("all", $_GET)) {
    $query = "SELECT * FROM `hotel_booking` ORDER BY id DESC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $return_arr = array();
        while ($row = $result->fetch_assoc()) {
            $agentId = $row['agentId'];

            $agentQuery = $conn->query("SELECT * FROM agent WHERE agentId='$agentId'");
            $agentData = $agentQuery->fetch_assoc();

            if ($agentData) {
                $companyName = $agentData['company'];
                $companyPhone = $agentData['phone'];
                $bookedBy = $agentData['name'];
            }

            $passengerQuery = $conn->query("SELECT * FROM hotel_passengers WHERE agentId='$agentId'");
            $passengerInfo = $passengerQuery->fetch_assoc();

            $paymentInfoQuery = $conn->query("SELECT * FROM hotel_payment_info WHERE agentId='$agentId'");
            $paymentInfo = $paymentInfoQuery->fetch_assoc();

            $response = $row;
            $response['companyName'] = $companyName ?? "";
            $response['companyPhone'] = $companyPhone ?? "";
            $response['bookedBy'] = $bookedBy ?? "";
            $response['passengerInfo'] = [$passengerInfo] ?? [];
            $response['paymentInfo'] = $paymentInfo ?? [];

            $return_arr[] = $response;
        }

        echo json_encode($return_arr);
    } else {
        $response['status'] = 'error';
        $response['message'] = "Booking Data Not Found";
        echo json_encode($response);
    }
}elseif (array_key_exists("all", $_GET)&& array_key_exists("agentId", $_GET)) {
    $agentId = $_GET['grantId'];
    $query = "SELECT * FROM `hotel_booking` WHERE agentId = $agentId ORDER BY id DESC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $return_arr = array();
        while ($row = $result->fetch_assoc()) {
            $agentId = $row['agentId'];

            $agentQuery = $conn->query("SELECT * FROM agent WHERE agentId='$agentId'");
            $agentData = $agentQuery->fetch_assoc();

            if ($agentData) {
                $companyName = $agentData['company'];
                $companyPhone = $agentData['phone'];
                $bookedBy = $agentData['name'];
            }

            $passengerQuery = $conn->query("SELECT * FROM hotel_passengers WHERE agentId='$agentId'");
            $passengerInfo = $passengerQuery->fetch_assoc();

            $paymentInfoQuery = $conn->query("SELECT * FROM hotel_payment_info WHERE agentId='$agentId'");
            $paymentInfo = $paymentInfoQuery->fetch_assoc();

            $response = $row;
            $response['companyName'] = $companyName ?? "";
            $response['companyPhone'] = $companyPhone ?? "";
            $response['bookedBy'] = $bookedBy ?? "";
            $response['passengerInfo'] = [$passengerInfo] ?? [];
            $response['paymentInfo'] = $paymentInfo ?? [];

            $return_arr[] = $response;
        }

        echo json_encode($return_arr);
    } else {
        $response['status'] = 'error';
        $response['message'] = "Booking Data Not Found";
        echo json_encode($response);
    }
} elseif (array_key_exists("bookingId", $_GET)) {
    $bookingId = $_GET["bookingId"];
    $sql = "SELECT * FROM `hotel_booking` where bookingId='$bookingId'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $return_arr = array();
        while ($row = $result->fetch_assoc()) {
            $agentId = $row['agentId'];

            $agentQuery = $conn->query("SELECT * FROM agent WHERE agentId='$agentId'");
            $agentData = $agentQuery->fetch_assoc();

            if ($agentData) {
                $companyName = $agentData['company'];
                $companyPhone = $agentData['phone'];
                $bookedBy = $agentData['name'];
            }

            $passengerQuery = $conn->query("SELECT * FROM hotel_passengers WHERE agentId='$agentId'");
            $passengerInfo = $passengerQuery->fetch_assoc();

            $paymentInfoQuery = $conn->query("SELECT * FROM hotel_payment_info WHERE agentId='$agentId'");
            $paymentInfo = $paymentInfoQuery->fetch_assoc();

            $response = $row;
            $response['companyName'] = $companyName ?? "";
            $response['companyPhone'] = $companyPhone ?? "";
            $response['bookedBy'] = $bookedBy ?? "";
            $response['passengerInfo'] = [$passengerInfo] ?? [];
            $response['paymentInfo'] = $paymentInfo?? [];

            $return_arr[] = $response;
        }

        echo json_encode($return_arr);
    } else {
        $response['status'] = 'error';
        $response['message'] = "Booking Data Not Found";
        echo json_encode($response);
    }
}elseif (array_key_exists("all", $_GET)&& array_key_exists("agentId", $_GET) && array_key_exists("bookingId", $_GET) ) {
    $agentId = $_GET['grantId'];
    $query = "SELECT * FROM `hotel_booking` WHERE agentId = $agentId AND bookingId = $bookingId ORDER BY id DESC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $return_arr = array();
        while ($row = $result->fetch_assoc()) {
            $agentId = $row['agentId'];

            $agentQuery = $conn->query("SELECT * FROM agent WHERE agentId='$agentId'");
            $agentData = $agentQuery->fetch_assoc();

            if ($agentData) {
                $companyName = $agentData['company'];
                $companyPhone = $agentData['phone'];
                $bookedBy = $agentData['name'];
            }

            $passengerQuery = $conn->query("SELECT * FROM hotel_passengers WHERE agentId='$agentId'");
            $passengerInfo = $passengerQuery->fetch_assoc();

            $paymentInfoQuery = $conn->query("SELECT * FROM hotel_payment_info WHERE agentId='$agentId'");
            $paymentInfo = $paymentInfoQuery->fetch_assoc();

            $response = $row;
            $response['companyName'] = $companyName ?? "";
            $response['companyPhone'] = $companyPhone ?? "";
            $response['bookedBy'] = $bookedBy ?? "";
            $response['passengerInfo'] = [$passengerInfo] ?? [];
            $response['paymentInfo'] = $paymentInfo ?? [];

            $return_arr[] = $response;
        }

        echo json_encode($return_arr);
    } else {
        $response['status'] = 'error';
        $response['message'] = "Booking Data Not Found";
        echo json_encode($response);
    }
} else {
    $response['status'] = "error";
    $response['message'] = "Invalid Request";
    echo json_encode($response);
}
?>
