<?php
    include '../.env';
     use Firebase\JWT\JWT;
     require_once('../vendor/autoload.php');
            $secret_Key = 'Bcpemb10Ae';
            $issuedAt = time();
            $expire = $issuedAt + 100;
            $payload = [
                
                    "sub" =>"test",
                    "name"=> "habibTest",
                    "password"=> "habibtest6474563",
                    'issue_time' => $issuedAt,
                    'exp_time' => $expire
            ];
            $Token = JWT::encode($payload, $secret_Key, 'HS512');
            //echo $Token;
            $header = apache_response_headers();
            if($header['Authorization']){
               $header = $header['Authorization'];
               $decode = JWT::decode($header, new Key($secret_Key, 'HS512'));
        
            }
            
    
?>