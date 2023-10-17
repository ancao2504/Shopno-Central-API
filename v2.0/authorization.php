<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

require 'vendor/autoload.php';
require 'config.php';

function authorization($conn)
{
    $headers = getallheaders();
    if (!array_key_exists('Authorization', $headers)) {
        echo json_encode(["error" => "Authorization header is missing"]);
        exit;
    } else {
        if (substr($headers['Authorization'], 0, 7) !== 'Bearer ') {
            echo json_encode(["error" => "Bearer keyword is missing"]);
            exit;
        } else {
            try {
                $token = trim(substr($headers['Authorization'], 6));
                $key = 'qwerttyuiopasdfdfghjklzxccvbvbnmfldnhfldnhfldnhhfmn';
                $decoded = json_decode(json_encode(JWT::decode($token, new Key($key, 'HS256'))), true);
                return $decoded;
            } catch (UnexpectedValueException $e) {
                error_log("Invalid JWT: " . $e->getMessage());
                header('HTTP/1.0 400 Bad Request');
                echo json_encode(array('message' => 'Invalid JWT format'));
                exit;
            } catch (SignatureInvalidException $e) {
                error_log("JWT Signature Verification Failed: " . $e->getMessage());
                header('HTTP/1.0 401 Unauthorized');
                echo json_encode(array('message' => 'Invalid token or signature'));
                exit;
            }


        }

    }
}

?>