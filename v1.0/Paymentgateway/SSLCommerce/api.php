
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require "../../config.php";
require_once(__DIR__ . "/lib/SslCommerzNotification.php");
include "OrderTransaction.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


use SslCommerz\SslCommerzNotification;

if (array_key_exists("amount", $_GET) && array_key_exists("agentId", $_GET)) {

    $query = new OrderTransaction();

    $agentId = $_GET["agentId"];
    $_SESSION['agentId'] = $agentId;
    $_SESSION['amount'] = $_GET["amount"];

    $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'"), MYSQLI_ASSOC);

    if ($agentdata["phone"] == "") {
        echo json_encode(array("status" => "error", "message" => "Please update your phone number"));
        exit;
    }

    if (!empty($agentdata)) {
        $company = $agentdata["company"];
        $name = $agentdata["name"];
        $email = $agentdata["email"];
        $phone = $agentdata["phone"];
        $address = $agentdata["companyadd"];
    }


    # Organize the submitted/inputted data
    $post_data = array();

    $post_data['total_amount'] = $_GET['amount'];
    $post_data['currency'] = "BDT";
    $post_data['tran_id'] = "$agentId-" . uniqid();
    $post_data['agentId'] = $agentId;

    # CUSTOMER INFORMATION
    $post_data['cus_name'] = isset($name) ? $name : "John Doe";
    $post_data['cus_email'] = isset($email) ? $email : "john.doe@email.com";
    $post_data['cus_add1'] = "Dhaka";
    $post_data['cus_add2'] = "Dhaka";
    $post_data['cus_city'] = "Dhaka";
    $post_data['cus_state'] = "Dhaka";
    $post_data['cus_postcode'] = "1000";
    $post_data['cus_country'] = "Bangladesh";
    $post_data['cus_phone'] = isset($phone) ? $phone : "01711111111";
    $post_data['cus_fax'] = "01711111111";

    # SHIPMENT INFORMATION
    $post_data["shipping_method"] = "YES";
    $post_data['ship_name'] = "Store Test";
    $post_data['ship_add1'] = "Dhaka";
    $post_data['ship_add2'] = "Dhaka";
    $post_data['ship_city'] = "Dhaka";
    $post_data['ship_state'] = "Dhaka";
    $post_data['ship_postcode'] = "1000";
    $post_data['ship_phone'] = "";
    $post_data['ship_country'] = "Bangladesh";

    $post_data['emi_option'] = "1";
    $post_data["product_category"] = "Electronic";
    $post_data["product_profile"] = "general";
    $post_data["product_name"] = "Computer";
    $post_data["num_of_item"] = "1";



    # First, save the input data into local database table `orders`
    $sql = $query->saveTransactionQuery($post_data, "B2B");

    if ($conn->query($sql) === TRUE) {

        # Call the Payment Gateway Library
        $sslcomz = new SslCommerzNotification();
        $sslcomz->makePayment($post_data, 'hosted');
    } else {
        $response['status'] = 'success';
        $response['message'] = 'Someting went Wrong';

        echo json_encode($response);
    }
} else if (array_key_exists("amount", $_GET) && array_key_exists("userId", $_GET) && array_key_exists("app", $_GET)) {

    $userId = $_GET["userId"];
    $_SESSION['userId'] = $userId;
    $_SESSION['amount'] = $_GET["amount"];



    $userdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE userId='$userId'"), MYSQLI_ASSOC);

    if ($userdata["phone"] == "") {
        echo json_encode(array("status" => "error", "message" => "Please update your phone number"));
        exit;
    }

    if (!empty($userdata)) {
        $name = $userdata["name"];
        $email = $userdata["email"];
        $phone = $userdata["phone"];
        $address = $userdata["companyadd"];
    } else {
        echo json_encode(array("status" => "error", "message" => "No user data found"));
        exit;
    }

    # Organize the submitted/inputted data
    $post_data = array();

    $post_data['total_amount'] = $_GET['amount'];
    $post_data['currency'] = "BDT";
    $post_data['tran_id'] = "$userId-" . uniqid();
    $post_data['userId'] = $userId;

    # CUSTOMER INFORMATION
    $post_data['cus_name'] = isset($name) ? $name : "John Doe";
    $post_data['cus_email'] = isset($email) ? $email : "john.doe@email.com";
    $post_data['cus_add1'] = "Dhaka";
    $post_data['cus_add2'] = "Dhaka";
    $post_data['cus_city'] = "Dhaka";
    $post_data['cus_state'] = "Dhaka";
    $post_data['cus_postcode'] = "1000";
    $post_data['cus_country'] = "Bangladesh";
    $post_data['cus_phone'] = isset($phone) ? $phone : "01711111111";
    $post_data['cus_fax'] = "01711111111";

    # SHIPMENT INFORMATION
    $post_data["shipping_method"] = "YES";
    $post_data['ship_name'] = "Store Test";
    $post_data['ship_add1'] = "Dhaka";
    $post_data['ship_add2'] = "Dhaka";
    $post_data['ship_city'] = "Dhaka";
    $post_data['ship_state'] = "Dhaka";
    $post_data['ship_postcode'] = "1000";
    $post_data['ship_phone'] = "";
    $post_data['ship_country'] = "Bangladesh";

    $post_data['emi_option'] = "1";
    $post_data["product_category"] = "Electronic";
    $post_data["product_profile"] = "general";
    $post_data["product_name"] = "Computer";
    $post_data["num_of_item"] = "1";

    // echo json_encode($post_data);
    // exit;

    # First, save the input data into local database table `orders`
    $query = new OrderTransaction();

    $sql = $query->saveTransactionQuery($post_data, "B2CApp");

    if ($conn->query($sql) === TRUE) {
        # Call the Payment Gateway Library
        // echo json_encode($post_data);
        $sslcomz = new SslCommerzNotification();
        echo json_encode($sslcomz->makePayment($post_data, 'hosted'));
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Someting went Wrong';

        echo json_encode($response);
    }
} else if (array_key_exists("amount", $_GET) && array_key_exists("userId", $_GET) && !array_key_exists("app", $_GET)) {




    $userId = $_GET["userId"];
    $_SESSION['userId'] = $userId;
    $_SESSION['amount'] = $_GET["amount"];



    $userdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE userId='$userId'"), MYSQLI_ASSOC);
    // echo json_encode($userdata);
    // exit;
    if ($userdata["phone"] == "") {
        echo json_encode(array("status" => "error", "message" => "Please update your phone number"));
        exit;
    }
    if (!empty($userdata)) {
        $name = $userdata["name"];
        $email = $userdata["email"];
        $phone = $userdata["phone"];
        $address = $userdata["companyadd"];
    }


    # Organize the submitted/inputted data
    $post_data = array();

    $post_data['total_amount'] = $_GET['amount'];
    $post_data['currency'] = "BDT";
    $post_data['tran_id'] = "$userId-" . uniqid();
    $post_data['userId'] = $userId;

    # CUSTOMER INFORMATION
    $post_data['cus_name'] = isset($name) ? $name : "John Doe";
    $post_data['cus_email'] = isset($email) ? $email : "john.doe@email.com";
    $post_data['cus_add1'] = "Dhaka";
    $post_data['cus_add2'] = "Dhaka";
    $post_data['cus_city'] = "Dhaka";
    $post_data['cus_state'] = "Dhaka";
    $post_data['cus_postcode'] = "1000";
    $post_data['cus_country'] = "Bangladesh";
    $post_data['cus_phone'] = isset($phone) ? $phone : "01711111111";
    $post_data['cus_fax'] = "01711111111";

    # SHIPMENT INFORMATION
    $post_data["shipping_method"] = "YES";
    $post_data['ship_name'] = "Store Test";
    $post_data['ship_add1'] = "Dhaka";
    $post_data['ship_add2'] = "Dhaka";
    $post_data['ship_city'] = "Dhaka";
    $post_data['ship_state'] = "Dhaka";
    $post_data['ship_postcode'] = "1000";
    $post_data['ship_phone'] = "";
    $post_data['ship_country'] = "Bangladesh";

    $post_data['emi_option'] = "1";
    $post_data["product_category"] = "Electronic";
    $post_data["product_profile"] = "general";
    $post_data["product_name"] = "Computer";
    $post_data["num_of_item"] = "1";

    // echo json_encode($post_data);
    // exit;
    $query = new OrderTransaction();
    # First, save the input data into local database table `orders`
    $sql = $query->saveTransactionQuery($post_data, "B2C");
    if ($conn->query($sql) === TRUE) {
        # Call the Payment Gateway Library
        $sslcomz = new SslCommerzNotification();
        // echo json_encode($post_data);
        echo json_encode($sslcomz->makePayment($post_data, 'hosted'));
    } else {
        $response['status'] = 'success';
        $response['message'] = 'Someting went Wrong';

        echo json_encode($response);
    }
} else if (array_key_exists("appredirect", $_GET) && array_key_exists("success", $_GET)) {

    echo json_encode(
        array(
            "status" => "success",
            "message" => "Deposit Successfully Done"
        )
    );
    exit;
} else if (array_key_exists("appredirect", $_GET) && array_key_exists("failed", $_GET)) {

    echo json_encode(
        array(
            "status" => "error",
            "message" => "Deposit Failed"
        )
    );
    exit;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid Request';

    echo json_encode($response);
}
