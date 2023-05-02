<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$TARGETBRANCH = 'P7182044';
	$CREDENTIALS = 'Universal API/uAPI5270664478-0c51bde6:2Td*m/F3M5'; 
	$message = <<<EOM
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
	<soapenv:Header/>
	<soapenv:Body>
	<AirFareDisplayReq xmlns="http://www.travelport.com/schema/air_v51_0"
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
		TraceId="FFI-KayesFahim" TargetBranch="P7182044" Origin="DAC" Destination="DXB" ProviderCode="1G">
		<BillingPointOfSaleInfo OriginApplication="UAPI" xmlns="http://www.travelport.com/schema/common_v51_0" />
		<AirFareDisplayModifiers xmlns="http://www.travelport.com/schema/air_v51_0" />
	</AirFareDisplayReq>	
</soapenv:Body>
</soapenv:Envelope>
EOM;

//print($message);



$auth = base64_encode("$CREDENTIALS");
$soap_do = curl_init("https://apac.universal-api.pp.travelport.com/B2BGateway/connect/uAPI/AirService");
$header = array(
"Content-Type: text/xml;charset=UTF-8",
"Accept: gzip,deflate",
"Cache-Control: no-cache",
"Pragma: no-cache",
"SOAPAction: \"\"",
"Authorization: Basic $auth",
"Content-length: ".strlen($message),
);


curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($soap_do, CURLOPT_POST, true );
curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message);
curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
$return = curl_exec($soap_do);
curl_close($soap_do);


$xml = $return;
$xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3', $xml);
$xml = simplexml_load_string($xml);
$json = json_encode($xml);

$responseArray = json_decode($json, true); // true to have an array, false for an object

$AirFareRules = $responseArray['SOAPBody']['airAirFareDisplayRsp']['airFareDisplay'];

foreach($AirFareRules as $airRules){
	
	
}





?>