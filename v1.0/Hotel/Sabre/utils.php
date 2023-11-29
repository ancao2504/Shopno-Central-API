<?php

include '../../../config.php';



function getProdToken()
{
    $SABRE_ID = $_ENV['SABRE_ID'];
    $SABRE_PASSWORD = $_ENV['SABRE_PASSWORD'];
    $SABRE_AUTH_ENDPOINT = $_ENV['SABRE_AUTH_ENDPOINT'];
    $SABRE_PCC = $_ENV['SABRE_PCC'];

    $Client_ID_RAW = "V1:$SABRE_ID:$SABRE_PCC:AA";

    //Encoded Basic Auth
    $client_id = base64_encode($Client_ID_RAW);
    $client_secret = base64_encode($SABRE_PASSWORD);
    $token = base64_encode($client_id . ":" . $client_secret);
    $data = 'grant_type=client_credentials';
    $headers = array(
        'Authorization: Basic ' . $token,
        'Accept: /',
        'Content-Type: application/x-www-form-urlencoded'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$SABRE_AUTH_ENDPOINT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $AuthResponse = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($AuthResponse, 1);

    if (isset($result['access_token'])) {
        $sabreToken = $result['access_token'];
        return $sabreToken;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Token Creation Failed';
        echo json_encode($response);
        exit();
    }
}
function getCertToken()
{
    $SABRE_ID = $_ENV['SABRE_ID'];
    $SABRE_PASSWORD = $_ENV['SABRE_PASSWORD_CERT'];
    $SABRE_AUTH_ENDPOINT = $_ENV['SABRE_AUTH_ENDPOINT_CERT'];
    $SABRE_PCC = $_ENV['SABRE_PCC'];

    $Client_ID_RAW = "V1:$SABRE_ID:$SABRE_PCC:AA";

    //Encoded Basic Auth
    $client_id = base64_encode($Client_ID_RAW);
    $client_secret = base64_encode($SABRE_PASSWORD);
    $token = base64_encode($client_id . ":" . $client_secret);
    $data = 'grant_type=client_credentials';
    $headers = array(
        'Authorization: Basic ' . $token,
        'Accept: /',
        'Content-Type: application/x-www-form-urlencoded'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$SABRE_AUTH_ENDPOINT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $AuthResponse = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($AuthResponse, 1);

    if (isset($result['access_token'])) {
        $sabreToken = $result['access_token'];
        return $sabreToken;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Token Creation Failed';
        echo json_encode($response);
        exit();
    }
}
