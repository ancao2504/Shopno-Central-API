<?php


// create both cURL resources

$ch1 = curl_init();
$ch2 = curl_init();
$ch3 = curl_init();
$ch4 = curl_init();
$ch5 = curl_init();
$ch6 = curl_init();
$ch7 = curl_init();
$ch8 = curl_init();
$ch9 = curl_init();
$ch10 = curl_init();

// set URL and other appropriate options
curl_setopt($ch1, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=JFK&departuredate=2022-09-21&adult=1&child=0&infant=0");
curl_setopt($ch2, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-22&adult=1&child=0&infant=0");
curl_setopt($ch3, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-23&adult=1&child=0&infant=0");
curl_setopt($ch4, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-24&adult=1&child=0&infant=0");
curl_setopt($ch5, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-25&adult=1&child=0&infant=0");
curl_setopt($ch6, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-26&adult=1&child=0&infant=0");
curl_setopt($ch7, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-27&adult=1&child=0&infant=0");
curl_setopt($ch8, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-28&adult=1&child=0&infant=0");
curl_setopt($ch9, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-29&adult=1&child=0&infant=0");
curl_setopt($ch10, CURLOPT_URL, "https://api.flyfarint.com/v.1.0.0/AirSearch/oneway.php?tripType=oneway&journeyfrom=DAC&journeyto=DXB&departuredate=2022-09-30&adult=1&child=0&infant=0");
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch5, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch6, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch7, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch8, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch9, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch10, CURLOPT_RETURNTRANSFER, true);

//create the multiple cURL handle
$mh = curl_multi_init();

//add the two handles
curl_multi_add_handle($mh,$ch1);
curl_multi_add_handle($mh,$ch2);
curl_multi_add_handle($mh,$ch3);
curl_multi_add_handle($mh,$ch4);
curl_multi_add_handle($mh,$ch5);
curl_multi_add_handle($mh,$ch6);
curl_multi_add_handle($mh,$ch7);
curl_multi_add_handle($mh,$ch8);
curl_multi_add_handle($mh,$ch9);
curl_multi_add_handle($mh,$ch10);


//execute the multi handle
do {
    curl_multi_exec($mh, $active);
} while ($active);

//close the handles
curl_multi_remove_handle($mh, $ch1);
curl_multi_remove_handle($mh, $ch2);
curl_multi_remove_handle($mh, $ch3);
curl_multi_remove_handle($mh, $ch4);
curl_multi_remove_handle($mh, $ch5);
curl_multi_remove_handle($mh, $ch6);
curl_multi_remove_handle($mh, $ch7);
curl_multi_remove_handle($mh, $ch8);
curl_multi_remove_handle($mh, $ch9);
curl_multi_remove_handle($mh, $ch10);
curl_multi_close($mh);

//Result
$resA = json_decode(curl_multi_getcontent($ch1));
$resB = json_decode(curl_multi_getcontent($ch2));
$resC = json_decode(curl_multi_getcontent($ch3));
$resD = json_decode(curl_multi_getcontent($ch4));
$resE = json_decode(curl_multi_getcontent($ch5));
$resF = json_decode(curl_multi_getcontent($ch6));
$resG = json_decode(curl_multi_getcontent($ch7));
$resH = json_decode(curl_multi_getcontent($ch8));
$resI = json_decode(curl_multi_getcontent($ch9));
$resJ = json_decode(curl_multi_getcontent($ch10));

print_r($resA[0]->price); echo "<br/>";
print_r($resB[0]->price); echo "<br/>";
print_r($resC[0]->price); echo "<br/>";
print_r($resD[0]->price); echo "<br/>";
print_r($resE[0]->price); echo "<br/>";
print_r($resF[0]->price); echo "<br/>";
print_r($resG[0]->price); echo "<br/>";
print_r($resH[0]->price); echo "<br/>";
print_r($resI[0]->price); echo "<br/>";
print_r($resJ[0]->price); echo "<br/>";

?>