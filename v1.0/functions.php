<?php

require './config.php';

function getALL($tablename)
{   
    global $conn;

    $sql="SELECT * FROM $tablename";
    
    $result=$conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    if(!empty($result)){
        echo json_encode($result);
    }
    else{
        getErrorMsg();
    }
}


function getOne($id, $tablename)
{   global $conn;

    $sql="SELECT * FROM $tablename WHERE id='$id'";
    $result=$conn->query($sql)->fetch_assoc();
    
    if(!empty($result)){
        echo json_encode($result);
    }
     else
     {
         getErrorMsg();
    }
}

function uploadImage($imagename, $acceptablesize, $cdnpath, $fileName, $newFileName)
{           
            $tempname=$_FILES[$imagename]['tmp_name'];
            $filesize=$_FILES[$imagename]['size'];

            $validExt=['jpg', 'jpeg', 'png', 'webp'];
            $fileExt= strtolower(pathinfo($fileName, PATHINFO_EXTENSION));


            if(in_array($fileExt, $validExt))
            {
                if($filesize<$acceptablesize)
                {
                    if(move_uploaded_file($tempname, $cdnpath.$newFileName))
                    {
                        $uri=$cdnpath.$newFileName;
                        return $uri;
                    }
                    else
                    {
                        echo json_encode(
                            array(
                                "status" => "error",
                                "message" => "File Not Moved"
                            )
                    
                            );
                            exit;
                    }
                }
                else
                {
                    echo json_encode(
                        array(
                            "status" => "error",
                            "message" => "Large Image Size"
                        )
                
                        );
                        exit;
                }
            }
            else
            {
                echo json_encode(
                    array(
                        "status" => "error",
                        "message" => "Invalid Extension"
                    )
                    );
                    exit;
            }
}


function deletedata($id, $tablename){
        global $conn;

        $sql="DELETE FROM $tablename WHERE id='$id'";
        if($conn->query($sql))
        {
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => "success"
                )
                );
        }else
        {
            getErrorMsg(); 
        }
}

function getErrorMsg(){
    echo json_encode(array(
        "status" => "error",
        "message" => "data not found"
    ));
}



?>