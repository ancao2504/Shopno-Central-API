<?php
include "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){ 
    if (array_key_exists('add', $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = json_decode(file_get_contents("php://input"), true);
            $type = $_POST['type'];
            $careerName = $_POST['careername'];
            $depCountry = $_POST['depcountry'];
            $arrCountry = $_POST['arrcountry'];
            $system = $_POST['system'];
            $defaultPercentage = $_POST['defaultpercentage'];
            $percentage = $_POST['percentage'];
            $amount = isset($_POST['amount']) ? $_POST['amount'] : "";
            $yrCommission = isset($_POST['yrcommission']) ? $_POST['yrcommission'] : "";
            $yqCommission = isset($_POST['yqcommission']) ? $_POST['yqcommission'] : "";
            $aitCommission = isset($_POST['aitcommission']) ? $_POST['aitcommission'] : "";
            $conversationPolicy = $_POST['conversationpolicy'];
            $createAt = date("Y-m-d H:i:s");

            $sql = "INSERT INTO `commission_manage`(`commissionType`, `careerName`,`depCountry`, `arrCountry`,`system`, `defaultPercentage`, `percentage`, `amount`, `yrCommission`, `yqCommission`,`aitPercentage`,`conversationPolicy`, `created_at`)VALUES('$type', '$careerName', '$depCountry', '$arrCountry', '$system', '$defaultPercentage', '$percentage', '$amount', '$yrCommission','$yqCommission', '$aitCommission', '$conversationPolicy', '$createAt')";

            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Commission Manage Added Successfully";
            } else {
                $response['status'] = "error";
                $response['message'] = "Query Failed";
            }
            echo json_encode($response);
        }

    }else if(array_key_exists('edit', $_GET)){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = json_decode(file_get_contents("php://input"), true);
            $id = $_POST['id'];
            $type = $_POST['type'];
            $careerName = $_POST['careername'];
            $depCountry = $_POST['depcountry'];
            $arrCountry = $_POST['arrcountry'];
            $system = $_POST['system'];
            $defaultPercentage = $_POST['defaultpercentage'];
            $percentage = $_POST['percentage'];
            $amount = isset($_POST['amount']) ? $_POST['amount'] : "";
            $yrCommission = isset($_POST['yrcommission']) ? $_POST['yrcommission'] : "";
            $yqCommission = isset($_POST['yqcommission']) ? $_POST['yqcommission'] : "";
            $aitCommission = isset($_POST['aitcommission']) ? $_POST['aitcommission'] : "";
            $conversationPolicy = $_POST['conversationpolicy'];
            $updatedAt = date("Y-m-d H:i:s");

            $checker = $conn->query("SELECT id FROM commission_manage WHERE id = '$id'")->fetch_all(MYSQLI_ASSOC);
            if(!empty($checker)){
                $sql = "UPDATE `commission_manage` SET `commissionType`='$type', `careerName`='$careerName',`depCountry`='$depCountry', `arrCountry`='$arrCountry', `system`='$system', `defaultPercentage`='$defaultPercentage', `percentage`='$percentage', `amount`='$amount', `yrCommission`='$yrCommission', `yqCommission`='$yqCommission',`aitPercentage`='$aitCommission',`conversationPolicy`='$conversationPolicy', `updated_at`=' $updatedAt' WHERE id = '$id'";

                if ($conn->query($sql) === true) {
                    $response['status'] = "success"; 
                    $response['message'] = "Commission Manage Update Successfully";
                } else {
                    $response['status'] = "error"; 
                    $response['message'] = "Query Failed";
                }
            }else{
                $response['status'] = "error";
                $response['message'] = "Id Not Found";
            }

            echo json_encode($response);
        }
    }else if(array_key_exists('delete', $_GET)){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = json_decode(file_get_contents("php://input"), true);
            $id = $_POST['id'];
            $checker = $conn->query("SELECT id FROM commission_manage WHERE id = '$id'")->fetch_all(MYSQLI_ASSOC);

            if(!empty($checker)){
                $sql = "DELETE FROM commission_manage WHERE id='$id'";
                if($conn->query($sql) == true){
                    $response['status'] = "success";
                    $response['message'] = "Commission Delete Successfully";
                }else{
                    $response['status'] = "error";
                    $response['message'] = "Query Failed";
                }
            }else{
                $response['status'] = "error";
                $response['message'] = "Id Not Found";
            }
            echo json_encode($response);
        }
    }else if(array_key_exists("all", $_GET)){
        $data = $conn->query("SELECT * FROM commission_manage")->fetch_all(MYSQLI_ASSOC);
        if(!empty($data)){
            echo json_encode($data);
        }else{
            $response['status'] = "error";
            $response['message'] = "Data Not Found";
            echo json_encode($response);
        }
    }
}else{
  authorization($conn);
}