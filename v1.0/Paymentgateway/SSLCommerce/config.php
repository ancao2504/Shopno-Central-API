<?php

if (!defined('PROJECT_PATH')) {

    // define('PROJECT_PATH', 'https://shopno.api.flyfarint.com/v1.0/Paymentgateway/SSLCommerce'); // replace this value with your project path
    define('PROJECT_PATH', 'http://localhost/shopno-tours-travels/v1.0/Paymentgateway/SSLCommerce'); // replace this value with your project path
}

if (!defined('IS_SANDBOX')) {
    define('IS_SANDBOX', true); // 'true' for sandbox, 'false' for live
}


//production

// if (!defined('STORE_ID')) {
//     define('STORE_ID', 'shopnotour0live'); // your store id. For sandbox, register at https://developer.sslcommerz.com/registration/
// }

// sandbox
if (!defined('STORE_ID')) {
    define('STORE_ID', 'sixse63fd6f58d5390'); // your store id. For sandbox, register at https://developer.sslcommerz.com/registration/
}


//production
// if (!defined('STORE_PASSWORD')) {
//     define('STORE_PASSWORD', '64DCC29186EDF14916'); // your store password.
// }

//sandbox
if (!defined('STORE_PASSWORD')) {
    define('STORE_PASSWORD', 'sixse63fd6f58d5390@ssl'); // your store password.
}

return [
    'success_url' => 'success.php',
    // your success url
    'failed_url' => 'fail.php',
    // your fail url
    'cancel_url' => 'cancel.php',
    //your cancel url
    'ipn_url' => 'ipn.php',
    // your ipn url


    'projectPath' => PROJECT_PATH,
    'apiDomain' => IS_SANDBOX ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com',
    'apiCredentials' => [
        'store_id' => STORE_ID,
        'store_password' => STORE_PASSWORD,
    ],
    'apiUrl' => [
        'make_payment' => "/gwprocess/v4/api.php",
        'order_validate' => "/validator/api/validationserverAPI.php",
    ],
    'connect_from_localhost' => false,
    'verify_hash' => true,
];