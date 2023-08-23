<?php
include "../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $agentId = $_POST['agentId'];
    $markupAmount = $_POST['markup'];
    $markupType = $_POST['markuptype'];

    $agentRowChecker = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
    if (mysqli_num_rows($agentRowChecker) > 0) {
        $result = mysqli_query($conn, "UPDATE agent SET markup= '$markupAmount', markuptype='$markupType' WHERE agentId='$agentId' ");
        if ($result === true) {
            $response['status'] = 'success';
            $response['message'] = 'Markup Update Successfully';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Markup Update Failed';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Agent Not Found';
    }
    echo json_encode($response);

}
