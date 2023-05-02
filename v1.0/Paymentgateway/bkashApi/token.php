<?php

    $credentials_json = '{
        "base_url" : "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized",
        "username" : "sandboxTokenizedUser02",
        "password" : "sandboxTokenizedUser02@12345",
        "app_key" : "4f6o0cjiki2rfm34kfdadl1eqq",
        "app_secret" : "2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b"
        }';
    
    $credentials_arr = json_decode($credentials_json,true);
    global $credentials_arr;

    $post_token = array(
        'app_key' => $credentials_arr['app_key'],
        'app_secret' => $credentials_arr['app_secret']
    );
    $url = curl_init($credentials_arr['base_url']."/checkout/token/grant");
    $post_token = json_encode($post_token);
    $header = array(
        'Content-Type:application/json',
        "password:". $credentials_arr['password'],
        "username:". $credentials_arr['username']
    );
    
    curl_setopt($url, CURLOPT_HTTPHEADER, $header);
    curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
    curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
    $result_data = curl_exec($url);
    curl_close($url);
        
    $response = json_decode($result_data, true);

    $_SESSION["token"] = $response['id_token'];

    return $response['id_token'];

?>