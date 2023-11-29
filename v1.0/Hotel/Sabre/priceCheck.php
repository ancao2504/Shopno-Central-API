<?php

include './utils.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$allResponse = [];
$FlightType;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $rateKey = $_POST['key'];
    $pcc = '27YK';
    // $url = 'https://api.cert.platform.sabre.com/v3.0.0/hotel/pricecheck';
    $url = 'https://api.platform.sabre.com/v3.0.0/hotel/pricecheck';
    // $accessToken = getToken();
    $accessToken = getProdToken();
    $requestBody = sabrePriceRQ($rateKey);

    $result = priceCheck($requestBody, $accessToken, $url);

    if (isset($result)) {

        echo $result;
        // echo $accessToken;
        // echo $requestBody;

    } else {
        $response = [];
        $response['status'] = 'error';
        $response['message'] = 'Invalid Request';

        echo json_encode($response);
    }
} else {
    $response = [];
    $response['status'] = 'error';
    $response['message'] = 'Invalid Request';

    echo json_encode($response);
}

function sabrePriceRQ($rateKey)
{
    $requestBody =
        '{
        "HotelPriceCheckRQ": {
          "RateInfoRef": {
            "RateKey": "' .
        $rateKey .
        '"
          }
        }
      }';
    return $requestBody;
}

function priceCheck($requestBody, $accessToken, $url)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $requestBody,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Conversation-ID: 2021.01.DevStudio',
            'Authorization: Bearer ' . $accessToken,
        ],
    ]);

    $response = curl_exec($curl);

    // TODO:Check the HTTP response code
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($httpCode != 200) {
        $response = [];
        $response['status'] = 'error';
        $response['code'] = $httpCode;
        $response['message'] = 'Invalid Request';

        echo json_encode($response);
    } else {
        // TODO:Output the API response
        //TODO: Making Modify

        $responseData = json_decode($response, true);

        // TODO: Modify Result
        if (isset($responseData)) {
            return json_encode($responseData);
        } else {
            return json_encode($responseData);
        }
    }

    curl_close($curl);
}

function convertKeysToCamelCase($array)
{
    $result = [];

    foreach ($array as $item) {
        $camelCaseItem = [];

        foreach ($item as $key => $value) {
            // Convert the key to camelCase
            $camelCaseKey = lcfirst(
                str_replace(' ', '', ucwords(str_replace('_', ' ', $key)))
            );

            $camelCaseItem[$camelCaseKey] = $value;
        }

        $result[] = $camelCaseItem;
    }

    return $result;
}

function flattenObject($data, $prefix = '')
{
    $result = new stdClass();

    foreach ($data as $key => $value) {
        if (is_object($value) || is_array($value)) {
            // If the value is an object or an array, recursively flatten it
            $nestedData = flattenObject($value, $prefix . camelCase($key));
            foreach ($nestedData as $nestedKey => $nestedValue) {
                $result->{$nestedKey} = $nestedValue;
            }
        } else {
            // If the value is a scalar, add it to the result object with camelCase key
            $result->{camelCase($prefix . $key)} = $value;
        }
    }

    return $result;
}

function camelCase($str)
{
    // Remove underscores and convert to camel case
    $str = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
    return $str;
}
