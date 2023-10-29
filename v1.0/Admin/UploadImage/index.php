<?php

require '../../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    if (array_key_exists("popupimg", $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $actionAt = date('Y-m-d H:i:s');
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (!empty($fileName)) {
                $upload_path = "../../../asset/Admin/Popup/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);

                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension

                // valid image extensions
                $valid_extensions = array('png', 'PNG', 'jpg', 'JPG');

                $renameFile = "noticepopupimg.webp";

                // allow valid image file formats
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        $arrtest = getimagesize($upload_path . $renameFile);
                        move_uploaded_file($tempPath, $upload_path . $renameFile);
                    }
                }
            }

            // if no error caused, continue ....
            if (!isset($errorMSG)) {
                $fileUrl = $renameFile;
            }

            $sql = "INSERT INTO `popupimage`(
        `image`,
        `platform`,
        `status`,
        `actionAt`
        )
    VALUES(
        '$fileUrl',
        'B2B',
        'true',
        '$actionAt'
    )";

            if ($conn->query($sql) == true) {
                $response['status'] = 'success';
                $response['message'] = 'Popup Image Added successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Query failed';
            }
            echo json_encode($response);
        }
    }else if (array_key_exists('editpopupimg', $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (!empty($fileName)) {
                $upload_path = "../../../asset/Admin/Popup/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);

                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension

                // valid image extensions
                $valid_extensions = array('png', 'PNG', 'jpg', 'JPG', 'webp', 'WEBP');

                $renameFile = "noticepopupimgw.webp";

                // allow valid image file formats
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        $arrtest = getimagesize($upload_path . $renameFile);
                        move_uploaded_file($tempPath, $upload_path . $renameFile);

                    }
                }

               // echo $renameFile;
            }

            // if no error caused, continue ....
            if (!isset($errorMSG)) {
                $fileUrl = $renameFile;
            }

            $getPopupImg="SELECT * FROM popupimage WHERE `platform`='B2B';";
            $popupImg=$conn->query($getPopupImg)->fetch_assoc();
            
            if(empty($popupImg)) 
            {
                $sql = "INSERT INTO `popupimage`(
                    `image`,
                    `platform`,
                    `status`,
                    `actionAt`
                    )
                VALUES(
                    '$fileUrl',
                    'B2B',
                    'true',
                    '$actionAt'
                )";

            }else
            {
                $sql = "UPDATE popupimage SET
                image = '$fileUrl'
                WHERE id = '$id' AND platform='B2B'";
            }


            if ($conn->query($sql) == true) {
                $response['status'] = 'success';
                $response['message'] = 'Popup Image update successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Query failed';
            }
            echo json_encode($response);

        }
    }else if (array_key_exists('all', $_GET)) {
        $response = $conn->query("SELECT * FROM popupimage WHERE platform='B2B'")->fetch_all(MYSQLI_ASSOC);

        echo json_encode($response);

    }else if (array_key_exists('status', $_GET)) {
        $Status = $_POST['status'];
        if (isset($Status)) {
            $sql = "UPDATE popupimage SET status ='$Status' WHERE platform='B2B'";
            if ($conn->query($sql) == true) {
                $response['status'] = "success";
                $response['message'] = "Popup Status Updated";
            } else {
                $response['status'] = "error";
                $response['message'] = "Query Failed";
            }
            echo json_encode($response);
        }
    }else if (array_key_exists('addslideimg', $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];
            $actionAt = date('Y-m-d H:i:s');

            $sql = "SELECT * FROM slideimage ORDER BY id DESC LIMIT 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["id"]);
                    $number = (int) $outputString + 1;
                    $Id = "$number";
                }
            } else {
                $Id = "1";

            }
            if (!empty($fileName)) {
                $upload_path = "../../../../cdn.flyfarint.com/Slide/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);

                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension

                // valid image extensions
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');

                $renameFile = "slide$Id.$fileExt";

                // allow valid image file formats
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {

                        move_uploaded_file($tempPath, $upload_path . $renameFile);
                        $fileUrl = $renameFile;
                    }
                }
            }

            $sql = "INSERT INTO `slideimage`(`image`,`status`,`actionAt`)VALUES('$fileUrl','true', '$actionAt')";

            if ($conn->query($sql) == true) {
                $response['status'] = 'success';
                $response['message'] = 'Slide Image Added successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Query failed';
            }

            echo json_encode($response);

        }
    }else if (array_key_exists('editslideimg', $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $Id = $_POST['id'];
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];
            if (!empty($fileName)) {
                $upload_path = "../../../../cdn.flyfarint.com/Slide/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension

                // valid image extensions
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');
                $renameFile = "slide$Id.$fileExt";

                // allow valid image file formats
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        move_uploaded_file($tempPath, $upload_path . $renameFile);
                        $fileUrl = $renameFile;

                    } else {
                        move_uploaded_file($tempPath, $upload_path . $renameFile);

                    }
                }
            }

            $sql = "UPDATE slideimage SET
                image='$fileUrl' WHERE id='$Id'";

            if ($conn->query($sql) == true) {
                $response['status'] = 'success';
                $response['message'] = 'Slide Image Update Successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Query failed';
            }

            echo json_encode($response);
        }
    }else if (array_key_exists('deleteslideimg', $_GET)) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $Id = $_POST['id'];
            $checker = $conn->query("SELECT id FROM slideimage WHERE id='$Id'")->fetch_all(MYSQLI_ASSOC);
            if (!empty($checker)) {
                $sql = "DELETE FROM slideimage WHERE id='$Id'";
                if ($conn->query($sql) == true) {
                    $response['status'] = 'success';
                    $response['message'] = 'Slide Image Delete Successfully';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Query Failed';
                }

            } else {
                $response['status'] = 'error';
                $response['message'] = 'Id Not Found';
            }
            echo json_encode($response);
        }
    }else if (array_key_exists("getallslideimg", $_GET)) {
        $response = $conn->query("SELECT * FROM slideimage")->fetch_all(MYSQLI_ASSOC);
        echo json_encode($response);
    }
