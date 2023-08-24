<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = json_decode(file_get_contents('php://input'), true);
    if (array_key_exists("option", $_GET)) {
        $Option = $_GET['option'];
         if ($Option == 'companylogo') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select image", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');
                $renameFile = "$Option.webp";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {

                        move_uploaded_file($tempPath, $upload_path . $renameFile);

                    }

                }
            }

            if (!isset($errorMSG)) {
                $fileUrl = "$renameFile";
                $query = mysqli_query($conn, 'UPDATE `cms` SET `companylogo`="' . $fileUrl . '"');

                echo json_encode(array("message" => "Image Updated Successfully", "status" => "success"));
            }
        } else if ($Option == 'companyname') {
            $companyname = $_POST['data'];
            $sql = "UPDATE cms SET companyname = '$companyname'";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Website Title Update Successfully";
            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);
        } else if ($Option == 'iata') {
            $iata = $_POST['data'];
            $sql = "UPDATE cms SET iata = '$iata' ";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Update Successfully";
            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);
        } else if ($Option == 'toab') {
            $toab = $_POST['data'];
            $sql = "UPDATE cms SET toab = '$toab' ";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Update Successfully";
            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);
        } else if ($Option == 'atab') {
            $toab = $_POST['data'];
            $sql = "UPDATE cms SET atab = '$toab' ";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Update Successfully";
            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);
        } else if ($Option == 'pata') {
            $pata = $_POST['data'];
            $sql = "UPDATE cms SET pata = '$pata' ";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Update Successfully";
            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);
        } else if ($Option == 'bg') {
            $data = $_POST['data'];
            $sql = "UPDATE cms SET bg = '$data' ";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Update Successfully";
            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);
        } else if ($Option == 'baira') {
            $data = $_POST['data'];
            $sql = "UPDATE cms SET baira = '$data' ";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Update Successfully";
            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);
        } else if ($Option == 'primarycolor') {
            $primary_color = $_POST['data'];
            $sql = "UPDATE cms SET primary_color = '$primary_color' ";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Primary Color Update Successfully";

            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);
        } else if ($Option == 'secondarycolor') {
            $secondary_color = $_POST['data'];
            $sql = "UPDATE cms SET secondary_color = '$secondary_color' ";
            if ($conn->query($sql) === true) {
                $response['status'] = "success";
                $response['message'] = "Secondary Color Update Successfully";

            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);

        } else if ($Option == "aboutus") {
            $about_us = $_POST["data"];
            if (mysqli_num_rows($result) > 0) {

                $sql = "UPDATE cms SET about_us ='$about_us' ";
                if ($conn->query($sql) === true) {

                    $response["status"] = "success";
                    $response["message"] = "About us Update successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == "address") {
            $address = $_POST["data"];
            $sql = "UPDATE cms SET address ='$address' ";
            if ($conn->query($sql) === true) {
                $response["status"] = "success";
                $response["message"] = "address Update successfully";
            } else {
                $response['status'] = "Error";
                $response['message'] = "Update Failed";
            }
            echo json_encode($response);

        } else if ($Option == "email") {

            $email = $_POST["data"];
            $agentIdChecker = "SELECT * FROM cms ";
            $result = $conn->query($agentIdChecker);

            if (mysqli_num_rows($result) > 0) {

                $sql = "UPDATE cms SET email ='$email' ";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "Email Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == "othersemail") {

            $othersemail = $_POST["data"];
            $agentIdChecker = "SELECT * FROM cms ";
            $result = $conn->query($agentIdChecker);

            if (mysqli_num_rows($result) > 0) {

                $sql = "UPDATE cms SET othersemail ='$othersemail' ";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "Email Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == "phone") {
            $phone = $_POST["data"];
            $agentIdChecker = "SELECT * FROM cms ";
            $result = $conn->query($agentIdChecker);
            if (mysqli_num_rows($result) > 0) {

                $sql = "UPDATE cms SET phone = '$phone' ";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "phone Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == "fblink") {
            $fb_link = $_POST["data"];
            if (mysqli_num_rows($result) > 0) {
                $sql = "UPDATE cms SET fb_link ='$fb_link' ";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "facebook_link Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == "whatsappnum") {
            $whatsapp_num = $_POST['data'];
            if (mysqli_num_rows($result) > 0) {
                $sql = "UPDATE cms SET whatsapp_num = '$whatsapp_num'";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "whatsapp Number Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == "linkedinlink") {

            $linkedin_link = $_POST['data'];
            if (mysqli_num_rows($result) > 0) {
                $sql = "UPDATE cms SET linkedin_link= '$linkedin_link' ";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "linkedin_link Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == "aboutuslong") {

            $aboutuslong = str_replace("'", "''", $_POST['data']);
            if (mysqli_num_rows($result) > 0) {
                $sql = "UPDATE cms SET about_us_long ='$aboutuslong'";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "About Us Long Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == "privacy") {
            $privacy_policy = str_replace("'", "''", $_POST['data']);
            if (mysqli_num_rows($result) > 0) {
                $sql = "UPDATE cms SET privacy_policy='$privacy_policy' WHERE agentId='$agentId'";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "privacy policy Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);

            }

        } else if ($Option == 'terms') {
            $terms_condition = str_replace("'", "''", $_POST["data"]);
            if (mysqli_num_rows($result) > 0) {
                $sql = "UPDATE cms SET terms_condition='$terms_condition' ";

                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "terms_condition Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == 'refund') {
            $refund_policy = str_replace("'", "''", $_POST["data"]);
            if (mysqli_num_rows($result) > 0) {
                $sql = "UPDATE cms SET refund_policy= '$refund_policy'";
                if ($conn->query($sql) === true) {
                    $response['status'] = "success";
                    $response['message'] = "refund policy Update Successfully";
                } else {
                    $response['status'] = "Error";
                    $response['message'] = "Update Failed";
                }
                echo json_encode($response);
            }

        } else if ($Option == 'favicon1') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select favicon", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'ico', 'JPG', 'jpg', 'jpeg');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }

                    }

                }
                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `favicon1`="' . $fileUrl .'"');

                    echo json_encode(array("message" => "Favicon Updated Successfully", "status" => "success"));
                }

            }

        } else if ($Option == 'favicon2') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select favicon", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'ico', 'JPG', 'jpg', 'jpeg');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }

                    }

                }
                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `favicon2`="' . $fileUrl . '"');

                    echo json_encode(array("message" => "Favicon Updated Successfully", "status" => "success"));
                }

            }

        } else if ($Option == 'favicon3') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select favicon", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'ico', 'JPG', 'jpg', 'jpeg');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }

                    } else {
                        // check file size '5MB'
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }
                    }

                } else {
                    $errorMSG = json_encode(array("message" => "PNG, ico, JPG, jpeg files are allowed", "status" => "error"));
                    echo $errorMSG;
                }
                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `favicon3`="'. $fileUrl .'"');

                    echo json_encode(array("message" => "Favicon Updated Successfully", "status" => "success"));
                }

            }

        } else if ($Option == 'mainbannerimg') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select image", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
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

                } else {
                    $errorMSG = json_encode(array("message" => "Sorry, only  PNG files are allowed", "status" => "error"));
                    echo $errorMSG;
                }
                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `mainbannerimg`="' . $fileUrl . '" WHERE agentId="' . $agentId . '"');

                    echo json_encode(array("message" => "Image Updated Successfully", "status" => "success"));
                }

            }

        } else if ($Option == 'mainbannervideo') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select video", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('avi', 'flv', 'wmv', 'mov', 'mp4');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        if ($fileSize < 30000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 10 MB size", "status" => "error"));
                            echo $errorMSG;
                        }

                    } else {
                        // check file size '5MB'
                        if ($fileSize < 30000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 10 MB size", "status" => "error"));
                            echo $errorMSG;
                        }
                    }

                }
                //else{
                //     $errorMSG = json_encode(array("message" => "Sorry, only  PNG files are allowed", "status" => "error"));
                //     echo $errorMSG;
                // }
                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `mainbannervideo`="' . $fileUrl . '" WHERE agentId="' . $agentId . '"');

                    echo json_encode(array("message" => "Video Updated Successfully", "status" => "success"));
                }

            }

        } else if ($Option == 'sliderimg1') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select image", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }

                    } else {
                        // check file size '5MB'
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }
                    }

                } else {
                    $errorMSG = json_encode(array("message" => "Sorry, only  PNG files are allowed", "status" => "error"));
                    echo $errorMSG;
                }
                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `slider_img1`="' . $fileUrl . '" WHERE agentId="' . $agentId . '"');

                    echo json_encode(array("message" => "Image Updated Successfully", "status" => "success"));
                }

            }

        } else if ($Option == 'sliderimg2') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select image", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }

                    } else {
                        // check file size '5MB'
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }
                    }

                } else {
                    $errorMSG = json_encode(array("message" => "Sorry, only  PNG files are allowed", "status" => "error"));
                    echo $errorMSG;
                }
                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `slider_img2`="' . $fileUrl . '" WHERE agentId="' . $agentId . '"');

                    echo json_encode(array("message" => "Image Updated Successfully", "status" => "success"));
                }

            }

        } else if ($Option == 'sliderimg3') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select image", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path
                    if (!file_exists($upload_path . $fileName)) {
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }

                    } else {
                        // check file size '5MB'
                        if ($fileSize < 2000000) {
                            move_uploaded_file($tempPath, $upload_path . $renameFile);
                        } else {
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));
                            echo $errorMSG;
                        }
                    }

                } else {
                    $errorMSG = json_encode(array("message" => "Sorry, only  PNG files are allowed", "status" => "error"));
                    echo $errorMSG;
                }
                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `slider_img3`="' . $fileUrl . '" WHERE agentId="' . $agentId . '"');

                    echo json_encode(array("message" => "Image Updated Successfully", "status" => "success"));
                }

            }

        } else if ($Option == 'sliderimg4') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select image", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');
                $renameFile = "$Option.$fileExt";
                if (in_array($fileExt, $valid_extensions)) {
                    //check file not exist our upload folder path

                    move_uploaded_file($tempPath, $upload_path . $renameFile);

                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `slider_img4`="' . $fileUrl . '" WHERE agentId="' . $agentId . '"');

                    echo json_encode(array("message" => "Image Updated Successfully", "status" => "success"));

                }
            }

        } else if ($Option == 'sliderimg5') {
            $fileName = $_FILES['file']['name'];
            $tempPath = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            if (empty($fileName)) {
                $errorMSG = json_encode(array("message" => "please select image", "status" => false));
                echo $errorMSG;
            } else {
                $upload_path = "../../../asset/Admin/Company/"; // set upload folder path

                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension
                $valid_extensions = array('png', 'PNG', 'jpg', 'jpeg', 'JPG', 'JPEG', 'webp', 'WEBP');
                $renameFile = "$Option.$fileExt";
                //check file not exist our upload folder path
                if (!file_exists($upload_path . $fileName)) {

                    move_uploaded_file($tempPath, $upload_path . $renameFile);

                }

                if (!isset($errorMSG)) {
                    $fileUrl = "$renameFile";
                    $query = mysqli_query($conn, 'UPDATE `cms` SET `slider_img5`="' . $fileUrl . '" WHERE agentId="' . $agentId . '"');

                    echo json_encode(array("message" => "Image Updated Successfully", "status" => "success"));
                }

            }

        } else {
            $response['status'] = "error";
            $response['message'] = $Option . " is invalid";
            echo json_encode($response);
        }

    }

}else if(array_key_exists('all', $_GET)){
    $data = $conn->query("SELECT * FROM cms")->fetch_all(MYSQLI_ASSOC);
    if(!empty($data)){
        echo json_encode($data);
    }else {
        $response['status'] = 'error';
        $response['message'] = "Data not found";
        echo json_encode($response);
    }

}

$conn->close();
?>