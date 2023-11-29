<?php

include '../../config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE');
header('Access-Control-Max-Age: 3600');
header(
    'Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
);

$SABRE_ID = $_ENV['SABRE_ID'];

$All = [];
$FlightType;

$control = mysqli_query($conn, 'SELECT * FROM control where id=1');
$controlrow = mysqli_fetch_array($control, MYSQLI_ASSOC);

if (!empty($controlrow)) {
    $Sabre = $controlrow['sabre'];
}

$Airportsql = 'SELECT name, cityName,countryCode FROM airports WHERE';

if (
    array_key_exists('journeyfrom', $_GET) &&
    array_key_exists('journeyto', $_GET) &&
    array_key_exists('departuredate', $_GET) &&
    array_key_exists('adult', $_GET) &&
    array_key_exists('child', $_GET) &&
    array_key_exists('infant', $_GET)
) {
    $From = $_GET['journeyfrom'];
    $To = $_GET['journeyto'];
    $Date = $_GET['departuredate'];
    $ActualDate = $Date . 'T00:00:00';
    $adult = $_GET['adult'];
    $child = $_GET['child'];
    $infants = $_GET['infant'];

    // Trip Type
    $fromsql = mysqli_query(
        $conn,
        "SELECT name, cityName, countryCode FROM airports WHERE code='$From' "
    );
    $fromrow = mysqli_fetch_array($fromsql, MYSQLI_ASSOC);

    if (!empty($fromrow)) {
        $fromCountry = $fromrow['countryCode'];
    }

    $tosql = mysqli_query(
        $conn,
        "SELECT name, cityName, countryCode FROM airports WHERE code='$To' "
    );
    $torow = mysqli_fetch_array($tosql, MYSQLI_ASSOC);

    if (!empty($torow)) {
        $toCountry = $torow['countryCode'];
    }

    if ($fromCountry == 'BD' && $toCountry == 'BD') {
        $TripType = 'Inbound';
    } else {
        $TripType = 'Outbound';
    }

    $ComissionType = '';
    if ($fromCountry == 'BD' && $toCountry == 'BD') {
        $ComissionType = 'domestic';
    } elseif ($fromCountry != 'BD' && $toCountry != 'BD') {
        $ComissionType = 'sotto';
    } elseif ($fromCountry != 'BD' && $toCountry == 'BD') {
        $ComissionType = 'sotti';
    } elseif ($fromCountry == 'BD' && $toCountry != 'BD') {
        $ComissionType = 'sitti';
    }

    $SeatReq = $adult + $child;

    if ($adult > 0 && $child > 0 && $infants > 0) {
        $SabreRequest =
            '{
				"Code": "ADT",
				"Quantity": ' .
            $adult .
            '
			},
			{
				"Code": "C09",
				"Quantity": ' .
            $child .
            '
			},
			{
				"Code": "INF",
				"Quantity": ' .
            $infants .
            '
			}';
    } elseif ($adult > 0 && $child > 0) {
        $SabreRequest =
            '{
					"Code": "ADT",
					"Quantity": ' .
            $adult .
            '
				},
				{
					"Code": "C09",
					"Quantity": ' .
            $child .
            '
				}';
    } elseif ($adult > 0 && $infants > 0) {
        $SabreRequest =
            '{
				"Code": "ADT",
				"Quantity": ' .
            $adult .
            '
				},
				{
					"Code": "INF",
					"Quantity": ' .
            $infants .
            '
				}';
    } else {
        $SabreRequest =
            '{
					"Code": "ADT",
					"Quantity": ' .
            $adult .
            '
				}';
    }

    $requestedBody = `{
        "GetHotelListRQ": {
          "POS": {
            "Source": {
              "PseudoCityCode": "TM61"
            }
          },
          "CorporateNumber": "DK44391RC",
          "HotelRefs": {
            "HotelRef": [
              {
                "HotelCode": "100072188",
                "CodeContext": "GLOBAL"
              }
            ]
          },
          "HotelPref": {
            "HotelName": "Inn",
            "BrandCodes": {
              "BrandCode": [
                "10008",
                "10009"
              ]
            },
            "ChainCodes": {
              "ChainCode": [
                "MC",
                "YX"
              ]
            },
            "AmenityCodes": {
              "Inclusive": false,
              "AmenityCode": [
                15,
                16
              ]
            },
            "SecurityFeatureCodes": {
              "Inclusive": false,
              "SecurityFeatureCode": [
                15
              ]
            },
            "PropertyTypeCodes": {
              "Inclusive": false,
              "PropertyTypeCode": [
                15,
                16
              ]
            },
            "PropertyQualityCodes": {
              "Inclusive": false,
              "PropertyQualityCode": [
                15,
                16
              ]
            },
            "SabreRating": {
              "Min": "1.5",
              "Max": "4.5"
            }
          },
          "HotelInfoRef": {
            "Amenities": true,
            "LocationInfo": true,
            "PropertyTypeInfo": true,
            "PropertyQualityInfo": true,
            "SecurityFeatures": true
          }
        }
      }`;

    $client_id = base64_encode('V1:593072:14KK:AA');
    //$client_secret = base64_encode("280ff537"); //cert
    $client_secret = base64_encode('f270395'); //prod

    $token = base64_encode($client_id . ':' . $client_secret);

    $data = 'grant_type=client_credentials';

    $headers = [
        'Authorization: Basic ' . $token,
        'Accept: /',
        'Content-Type: application/x-www-form-urlencoded',
    ];

    $ch = curl_init();
    curl_setopt(
        $ch,
        CURLOPT_URL,
        'https://api.platform.sabre.com/v2/auth/token'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    $resf = json_decode($res, 1);
    $access_token = $resf['access_token'];

    $curl = curl_init();

    if (isset($access_token)) {
        curl_setopt_array($curl, [
            //CURLOPT_URL => 'https://api-crt.cert.havail.sabre.com/v4/offers/shop',
            CURLOPT_URL => 'https://api.platform.sabre.com/v4/offers/shop',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $requestedBody,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Conversation-ID: 2021.01.DevStudio',
                'Authorization: Bearer ' . $access_token,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);
    }
}
function FareRulesPolicy(
    $comissionvalue,
    $FareCurrency,
    $Ait,
    $BaseFare,
    $Taxes
) {
    $TotalPrice =
        $BaseFare * (1 - (int) $comissionvalue / 100) +
        $Taxes +
        ($BaseFare + $Taxes) * $Ait;

    $AgentPrice = CurrencyConversation($TotalPrice, $FareCurrency);

    return $AgentPrice;
}

function CurrencyConversation($TotalPrice, $FareCurrency)
{
    include '../../config.php';

    $data = $conn->query(
        "SELECT * FROM `fxconversion_rate` where currencyname='$FareCurrency' "
    );
    $PaymentRate = 0;
    $Sellingrate = 0;
    if ($data->num_rows > 0) {
        while ($row = $data->fetch_assoc()) {
            $PaymentRate = $row['paymentrate'];
            $Sellingrate = $row['sellingrate'];

            return floor(($TotalPrice / $PaymentRate) * $Sellingrate);
        }
    } else {
        return $TotalPrice;
    }
}

$conn->close();
?>
