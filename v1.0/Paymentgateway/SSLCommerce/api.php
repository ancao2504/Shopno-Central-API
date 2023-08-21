
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


use SslCommerz\SslCommerzNotification;

if (array_key_exists("amount", $_GET) && array_key_exists("agentId", $_GET)) {
    require_once(__DIR__ . "/lib/SslCommerzNotification.php");

    include("db_connection.php");
    include("OrderTransaction.php");

    
    $agentId = $_GET["agentId"];
    $_SESSION['agentId'] = $agentId;
    $_SESSION['amount'] = $_GET["amount"];


    
    $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'"), MYSQLI_ASSOC);

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
    $query = new OrderTransaction();
    $sql = $query->saveTransactionQuery($post_data);

    if ($conn->query($sql) === TRUE) {

        # Call the Payment Gateway Library
        $sslcomz = new SslCommerzNotification();
        $sslcomz->makePayment($post_data, 'hosted');
    } else {
        $response['status'] = 'success';
        $response['message'] = 'Someting went Wrong';

        echo json_encode($response);
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid Request';

    echo json_encode($response);
}