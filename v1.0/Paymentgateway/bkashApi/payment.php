<?php

    include 'token.php';
         

    $post_token = array(
        'mode' => '0011',
        'amount' => $_GET['amount'] ? $_GET['amount'] : 1,
        'payerReference' => " ",
        'callbackURL' => "http://localhost/Flight-Central-Api/v.1.0.0/Paymentgateway/bkashApi/callback.php", // Your callback URL
        'currency' => 'BDT',
        'intent' => 'sale',
        'merchantInvoiceNumber' => 'Inv'.rand()
    );

    $url = curl_init($credentials_arr['base_url']."/checkout/create");
    $post_token = json_encode($post_token);
    $header = array(
        'Content-Type:application/json',
        'Authorization:'. $_SESSION["token"],
        'X-APP-Key:'. $credentials_arr['app_key']
    );

    curl_setopt($url, CURLOPT_HTTPHEADER, $header);
    curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
    curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
    $result_data = curl_exec($url);
    curl_close($url);

    $response = json_decode($result_data, true);
    print_r($response);

    //header("Location: ".$response['bkashURL']); 
    exit;   
  
    
    ?>