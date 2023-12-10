<?php

// TODO:INCLUDE NECESSARY FILES
include '../../config.php';
include './utils.php';

// TODO:SET HEADERS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// TODO:CHECK IF 'BOOKING ID' EXISTS IN THE GET REQUEST
if (isset($_GET['bookingId'])) {
    $bookingId = $_GET['bookingId'];
    $accessToken = getProdToken();
    $query = "SELECT pnr FROM hotel_booking WHERE bookingId = '$bookingId'";
    $bookingResult = mysqli_query($conn, $query);

    if ($bookingResult) {
        $bookingData = mysqli_fetch_assoc($bookingResult);
        $pnr = $bookingData['pnr']??"";

        // TODO:INITIALIZE CURL
        $curl = curl_init();

        // TODO:SET CURL OPTIONS
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.platform.sabre.com/v1/trip/orders/getBooking',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                "confirmationId" => $pnr,
                "retrieveBooking" => true,
                "cancelAll" => true,
                "errorHandlingPolicy" => "ALLOW_PARTIAL_CANCEL"
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Conversation-ID: 2021.01.DevStudio',
                "Authorization: Bearer $accessToken"
            ),
        ));

        // todo:EXECUTE cURL request
        $response = curl_exec($curl);

        // TODO:CLOSE CURL RESOURCE
        curl_close($curl);

        if ($response === false) {
            // todo:HANDLE cURL error
            $errMessage = array(
                'status' => 'error',
                'message' => 'cURL error: ' . curl_error($curl)
            );
            echo json_encode($errMessage);
        } else {
            // TODO:OUTPUT THE RESPONSE
            echo $response;
        }
    } else {
        // todo:HANDLE database error
        $errMessage = array(
            'status' => 'error',
            'message' => 'Invalid BookingID or Database Error'
        );
        echo json_encode($errMessage);
    }
} else {
    // TODO:HANDLE MISSING 'BOOKING ID' ERROR
    $errMessage = array(
        'status' => 'error',
        'message' => 'Missing BookingID in request'
    );
    echo json_encode($errMessage);
}
?>
