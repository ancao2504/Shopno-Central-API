<?php

require 'config.php';

function getALL($tablename)
{
    global $conn;

    $sql = "SELECT * FROM $tablename";

    $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    if (!empty($result)) {
        echo json_encode($result);
    } else {
        getErrorMsg();
    }
}


function getOne($id, $tablename)
{
    global $conn;

    $sql = "SELECT * FROM $tablename WHERE id='$id'";
    $result = $conn->query($sql)->fetch_assoc();

    if (!empty($result)) {
        echo json_encode($result);
    } else {
        getErrorMsg();
    }
}

function uploadImage($imagename, $acceptablesize, $folder, $fileName, $newFileName)
{
    //finding path to Asset
    $cwd = getcwd();
    $ommit = "/home/flyfarin/shopno.api.flyfarint.com/";

    $subString = str_replace($ommit, "", $cwd);

    $dirs = explode("/", $subString);
    $pathToAsset = str_replace($dirs, "..", $subString) . "/asset";
    ///////////
    
    $tempname = $_FILES[$imagename]['tmp_name'];
    $filesize = $_FILES[$imagename]['size'];

    $validExt = ['jpg', 'jpeg', 'png', 'webp'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!is_dir("$pathToAsset/$folder")) {
        if (!mkdir("$pathToAsset/$folder", 0777, true)) {
            return [
                "status" => "error",
                "message" => "Failed to create the directory for file storage."
            ];
        }
    }

    $newFileName = "$newFileName.$fileExt";
    $folder = "$folder/$newFileName";
    $cdnpath = "$pathToAsset/$folder";

    if (in_array($fileExt, $validExt)) {
        if ($filesize < $acceptablesize) {

            if (move_uploaded_file($tempname, $cdnpath)) {
                return "https://shopno.api.flyfarint.com/asset/$folder";
            } else {
                echo json_encode(
                    array(
                        "status" => "error",
                        "message" => "File Not Moved"
                    )

                );
                exit;
            }
        } else {
            echo json_encode(
                array(
                    "status" => "error",
                    "message" => "Large Image Size"
                )

            );
            exit;
        }
    } else {
        echo json_encode(
            array(
                "status" => "error",
                "message" => "Invalid Extension"
            )
        );
        exit;
    }
}




function deletedata($id, $tablename)
{
    global $conn;

    $sql = "DELETE FROM $tablename WHERE id='$id'";
    if ($conn->query($sql)) {
        echo json_encode(
            array(
                "status" => "success",
                "message" => "success"
            )
        );
    } else {
        getErrorMsg();
    }
}

function getErrorMsg()
{
    echo json_encode(array(
        "status" => "error",
        "message" => "data not found"
    ));
}
