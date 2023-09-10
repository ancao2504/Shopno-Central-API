<?php

require '../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// use Firebase\JWT\JWT;
// use Firebase\JWT\KEY;

// require __DIR__ . '/../../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    $_POST = json_decode(file_get_contents('php://input'), true);
    
    $currentTime = date("Y-m-d H:i:s");
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($_SERVER['REMOTE_ADDR']) {
        include 'Browser.php';

        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = getBrowser();
        $browser = $ua['name'];
        $platform = $ua['platform'];

    } else {
        $ip = 'No IP';
    }

    $agentSql = mysqli_query($conn, "SELECT `email`, `status` FROM `agent` WHERE `email`='$email' AND `platform`='B2B'");
    $agentrow = mysqli_fetch_array($agentSql, MYSQLI_ASSOC);

    $staffSql = mysqli_query($conn, "SELECT `email`, `status` FROM `staffList` WHERE `email`='$email'");
    $staffrow = mysqli_fetch_array($staffSql, MYSQLI_ASSOC);
    // echo json_encode("a".$agentrow);
    // echo json_encode($staffrow); exit;

    if (empty($agentrow) && empty($staffrow)) {
        $response['status'] = "error";
        $response['message'] = "User does not exist";

    } else if (!empty($agentrow)) {
        $checkUserquery = "SELECT agentId, email, name, company, phone, status FROM agent WHERE email='$email' AND `password`='$password' AND `platform`='B2B'";
        $resultant = mysqli_query($conn, $checkUserquery);

        $SECRET_KEY = "qfrfdfgrterdfsgdferersdf9iewfjhfkjsdfifmkknhzxiwreiueridjfdmjihirywtbbxcgsdfsdnnmmklqqzznbnbewfijsfnjkijwhirweijrnmkmllkljojsdfskjqqweewdnfdfjdkfjkljsdfiuirqwhwfnbvqwtvvxfsguyrewijflkdjnvjqwerewrtrctuywernhnliuiywtwhbjcsdughcbbux mnzsncijdshdsfnknuhjsefinbcvijtqwrhxuiwnjcibmchdfgsjjchrdytwilsmdjjddpwwweweqmfhjehuiwezazcvdwewtwttlmkhgrwefaedw";
        $DOMAIN_NAME = "https://shopnotour.com/";

        if (mysqli_num_rows($resultant) > 0) {
            while ($row = $resultant->fetch_assoc()) {
                if ($row['status'] == 'active') {
                    $adminkey = "shopnotour.com375hijeh3497845hkwre98324iurweij2314ihjfidfihwehihi";
                    $secretKey = $SECRET_KEY;
                    $userName = $DOMAIN_NAME;
                    $issueAt = time();
                    $expireTime = $issueAt + 60;
                    $payload = [
                        'user' => $row,
                        'admin_secretKey' =>  $adminkey,
                        'issue_ time' => $issueAt,
                        'expire_time' => $expireTime,
                    ];

                    // $Token = JWT::encode($payload, $secretKey, 'HS256');
                    $agentId = $row['agentId'];
                    $agencyName = $row['company'];
                    $response['user'] = $row;
                    // $response['token'] = $Token;
                    $response['action'] = "complete";
                    $response['message'] = "success";
                    $conn->query("UPDATE `agent` SET `isActive`='yes',`loginIp`='$ip',`browser`='$browser' WHERE email='$email' AND `platform`='B2B'");
                    $conn->query("INSERT INTO `lastLogin`(`agentId`, `agencyName`, `StaffName`, `loginIp`, `success`,`browser`,`platform`, `craetedTime`)
               VALUES ('$agentId','$agencyName','No','$ip','yes','$browser','B2B','$currentTime')");
                } else if ($row['status'] == 'pending') {
                    $response['action'] = "pending";
                    $response['message'] = "Your agency registration process is pending.";
                } else if ($row['status'] == 'deactive') {
                    $response['action'] = "deactive";
                    $response['message'] = "Status Is Deactive";
                } else {
                    $response['action'] = "rejected";
                    $response['message'] = "Status Is Rejected";
                }
            }
        } else {
            $response['action'] = "incomplete";
            $response['message'] = "Wrong Password";
        }
    } else if (!empty($staffrow)) {
        $checkUserquery = "SELECT staffId, agentId, email, phone, name fROM staffList WHERE email='$email' AND `password`='$password'";
        $resultant = mysqli_query($conn, $checkUserquery);

        if (mysqli_num_rows($resultant) > 0) {
            while ($row = $resultant->fetch_assoc()) {
                $agentId = $row['agentId'];
                $checkAgent = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId' AND `platform`='B2B'"), MYSQLI_ASSOC);
                $agentSatus = $checkAgent['status'];

                if (isset($agentSatus)) {
                    if ($agentSatus == 'deactive') {
                        $response['action'] = "incomplete";
                        $response['message'] = "Agent Is Deactive";
                    } else if ($agentSatus == 'active') {
                        $agencyName = $checkAgent['company'];
                        $response['user'] = $row;
                        $response['action'] = "complete";
                        $response['message'] = "success";
                        $conn->query("INSERT INTO `lastLogin`(`agentId`, `agencyName`, `StaffName`, `loginIp`, `success`,`browser`,`platform`, `craetedTime`)
                VALUES ('$agentId','$agencyName','No','$ip','yes','$browser','B2B','$currentTime')");
                    } else {
                        $response['action'] = "incomplete";
                        $response['message'] = "Agent Is Deactive";
                    }
                } else {
                    $response['action'] = "incomplete";
                    $response['message'] = "Agent doesnt Exists";
                }
            }
        } else {
            $response['action'] = "incomplete";
            $response['message'] = "Wrong Password";
        }
    }

    echo json_encode($response);
}
