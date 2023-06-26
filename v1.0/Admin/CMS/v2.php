<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $_POST = json_decode(file_get_contents('php://input'), true);
    if(array_key_exists("option", $_GET)){
        $Option = $_GET["option"];
        $Data= isset( $_POST["data"]) ? str_replace("'", "''", $_POST["data"]): "";
        // echo $Data;

        if($Data !== ""){
            updateData($Option, $Data, $conn);
        }else{
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];
            imageUpdate($fileName, $tempPath, $fileSize, $Option, $conn);
        }

        
    }
}

function updateData($Option, $Data, $conn){
        $sql = "UPDATE cms SET $Option = '$Data'";
        if ($conn->query($sql) === true) {
            $response['status'] = "success";
            $response['message'] = "$Option Update Successfully";
        } else {
            $response['status'] = "Error";
            $response['message'] = "Update Failed";
        }
        echo json_encode($response);
    
}
function imageUpdate($fileName, $tempPath, $fileSize, $Option, $conn){
    if (empty($fileName)) {
        $errorMSG = json_encode(array("message" => "please select file", "status" => false));
        echo $errorMSG;
    } else {
        $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
        $valid_extensions = array('png', 'PNG', 'ico', 'JPG', 'jpg', 'jpeg','webp', 'WEBP');
        if($Option == 'favicon1'){
            $extension = 'ico';
        }else {
            $extension = "webp";
        }
        $renameFile = "$Option.$extension";
        if (in_array($fileExt, $valid_extensions)) {
            //check file not exist our upload folder path
            if (!file_exists($upload_path . $fileName)) {
                if ($fileSize < 20000000) {
                    move_uploaded_file($tempPath, $upload_path . $renameFile);
                } else {
                    $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 20 MB size", "status" => "error"));
                    echo $errorMSG;
                }

            }
        }
        if (!isset($errorMSG)) {
            $fileUrl = "$renameFile";
            $query = mysqli_query($conn, "UPDATE `cms` SET $Option=' . $fileUrl . '");

            echo json_encode(array("message" => "$Option Updated Successfully", "status" => "success"));
        }

    }
}

$conn->close();
?>