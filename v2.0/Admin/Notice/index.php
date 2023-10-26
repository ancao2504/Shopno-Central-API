<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../../authorization.php';
if (authorization($conn) == true){  

    if(array_key_exists("add", $_GET)) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            //$_POST = json_decode(file_get_contents('php://input'), true);

            $actionAt = date('Y-m-d H:i:s');
            $Title = str_replace("'", "''",$_POST['title']);
            $OverView = str_replace("'", "''",$_POST['overview']);
            
            
            $sql = "SELECT * FROM notice ORDER BY id DESC LIMIT 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["refer"]);
                    $number = (int) $outputString + 1;
                    $noticeId = "FFN$number";
                }
            } else {
                $noticeId = "FFN1000";

            }

            $fileName = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : "";
            $tempPath = isset($_FILES['file']['tmp_name'])? $_FILES['file']['tmp_name']:"";
            $fileSize = isset($_FILES['file']['size']) ? $_FILES['file']['size']:"";

            if (!empty($fileName)){
                $upload_path = "../../../cdn.flyfarint.com/Notice/$noticeId/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);

                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension

                // valid image extensions
                $valid_extensions = array('png', 'PNG', 'jpg', 'JPG');

                $renameFile = "notice.$fileExt";

                // allow valid image file formats
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        $arrtest = getimagesize($upload_path . $renameFile);

                        if ($fileSize < 5000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }
                    }
                }
            }

            // if no error caused, continue ....
            if (!isset($errorMSG)) {
                $fileUrl = isset($renameFile)? $renameFile: "";
            }

            $sql = "INSERT INTO `notice`(
                `refer`,
                `image`,
                `title`,
                `description`,
                `status`,
                `actionAt`
                )
            VALUES(
                '$noticeId',
                '$fileUrl',
                '$Title',
                '$OverView',
                'true',
                '$actionAt'
            )";

            if ($conn->query($sql) == true) {
                $response['status'] = 'success';
                $response['message'] = 'Notice Added successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Notice Added failed';
            }

            echo json_encode($response);

        }
    } else if (array_key_exists("edit", $_GET)) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //$_POST = json_decode(file_get_contents('php://input'), true);

            $actionAt = date('Y-m-d H:i:s');
            $id = $_POST['id'];
            $noticeId = $_POST['noticeId'];
            $Title = isset($_POST['title'])? str_replace("'", "''",$_POST['title']):"";
            $OverView = isset($_POST['overview']) ? str_replace("'", "''",$_POST['overview']):"";

            $fileName = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : "";
            $tempPath = isset($_FILES['file']['tmp_name'])? $_FILES['file']['tmp_name']:"";
            $fileSize = isset($_FILES['file']['size']) ? $_FILES['file']['size']:"";

            if (!empty($fileName)) {
                $upload_path = "../../../cdn.flyfarint.com/Notice/$noticeId/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension

                // valid image extensions
                $valid_extensions = array('png', 'PNG', 'jpg', 'JPG');

                $renameFile = "notice.$fileExt";

                // allow valid image file formats
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        $arrtest = getimagesize($upload_path . $renameFile);

                        if ($fileSize < 5000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }
                    } else {
                        // check file size '5MB'
                        if ($fileSize < 5000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }
                    }
                }
            }

            // if no error caused, continue ....
            if (!isset($errorMSG)) {
                $fileUrl = isset($renameFile) ? $renameFile: "";
            }else{
                $fileUrl ="";
            }

            $sql = "UPDATE notice SET
                image = '$fileUrl', title='$Title', description='$OverView',
                `actionAt` = '$actionAt' WHERE id = '$id'";

            if ($conn->query($sql) == true) {
                $response['status'] = 'success';
                $response['message'] = 'Notice update successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Notice update failed';
            }
        
            echo json_encode($response);
        }
    } else if (array_key_exists('delete', $_GET)) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $_POST = json_decode(file_get_contents('php://input'), true);
            $id = $_POST['id'];

            $sql = "SELECT * FROM `notice` WHERE id = '$id'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $sql = "DELETE FROM notice WHERE id='$id'";
                $result = $conn->query($sql);
                if ($result == true) {
                    $response['status'] = 'success';
                    $response['message'] = 'Notice delete successfully';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Notice delete failed';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'data not found';
            }
            echo json_encode($response);
        }
    }

}else{
  authorization($conn);
}