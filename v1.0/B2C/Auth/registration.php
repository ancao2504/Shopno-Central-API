<?php
include "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $Name = $_POST['name'];
    $Email = $_POST['email'];
    $Phone = $_POST['phone'];
    $Password = $_POST['password'];

    $createdAt = date("Y-m-d H:i:s");

    $sql = "SELECT MAX(userId) AS maxUserId FROM agent";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $maxUserId = $row["maxUserId"];

        if ($maxUserId) {
            $number = (int) preg_replace('/[^0-9]/', '', $maxUserId) + 1;
            $userId = "STU$number";
        } else {
            $userId = "STU1000";
        }
    } else {
        $userId = "STU1000";
    }

    $checkUser = "SELECT email, phone FROM agent WHERE (email='$Email' OR phone ='$Phone') AND platform = 'B2C'";
$result = mysqli_query($conn, $checkUser);


if (mysqli_num_rows($result) > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['email'] == $Email) {
            $response['status'] = "error";
            $response['message'] = "Email Already Exists";
        } else if ($row['phone'] == $Phone) {
            $response['status'] = "error";
            $response['message'] = "Phone Number Registered to Another User";
        }
    }
} else if (mysqli_num_rows($result) <= 0) {
        $sql = "INSERT INTO `agent`(
                `userId`,
                `name`,
                `email`,
                `password`,
                `phone`,
                `platform`,
                `status`,
                `joinAt`
            )
            VALUES(
                '$userId',
                '$Name',
                '$Email',
                '$Password',
                '$Phone',
                'B2C',
                'active',
                '$createdAt'
            )";

        if ($conn->query($sql) === true) {
            $response['userId'] = $userId;
            $response['name'] = $Name;
            $response['status'] = "success";
            $response['message'] = "Registration Successful";

        } else {
            $response['status'] = "error";
            $response['message'] = "Registration Failed";
        }
    }

    echo json_encode($response);

}
