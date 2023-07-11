<?php
require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


function uploadImage($imagename, $acceptablesize, $cdnpath, $fileName, $name)
{           
            $tempname=$_FILES[$imagename]['tmp_name'];
            $filesize=$_FILES[$imagename]['size'];

            $validExt=['jpg', 'jpeg', 'png'];
            $fileExt= strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $imageType=substr($imagename,0,-1);
            $savedFileName=$name.$imageType.'.'.$fileExt;
            
            if (!file_exists($cdnpath)) {
                mkdir($cdnpath, 0777, true);
            }

            if(in_array($fileExt, $validExt))
            {
                if($filesize<$acceptablesize)
                {
                    move_uploaded_file($tempname, $cdnpath.$savedFileName);
                    return $savedFileName;
                }
                else
                {
                    echo json_encode(
                        array(
                            "status" => "error",
                            "message" => "Large Image Size"
                        )
                
                        );
                        return 'Not Found';
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
                    return 'Not Found';
            }
}




if(array_key_exists("dataupdate", $_GET))
{
    if($_SERVER["REQUEST_METHOD"] == 'POST')
    {
        $jsonData=json_decode(file_get_contents("php://input"), true);

        $empId=$jsonData["empId"];
        $userName=$jsonData["userName"];
        $fName=$jsonData["fName"];
        $lName=$jsonData["lName"];
        $email=$jsonData["email"];
        $password=$jsonData["password"];
        $role=$jsonData["role"];
        $add=$jsonData["add"];
        $edit=$jsonData["edit"];
        $delete=$jsonData["delete"];
        $status=$jsonData["status"];
        

        $sql="UPDATE users 
        SET
        username='$userName', fname='$fName', lname='$lName', email='$email',
        pass='$password', role='$role', addd='$add', edit='$delete', del='$delete', 
        status='$status', img=
        WHERE
        EMP_ID= '$empId'
        ";

        if($conn->query($sql))
        {
            $response["status"] ="Success";
            $response["message"] ="Update Successfull";
        }
        else
        {
            $response["status"] ="error";
            $response["message"] ="Update Failed";
        }
    }
    else
    {
        $response["status"] ="error";
        $response["message"] ="Wrong Request";
    }
    
    


}


echo json_encode($response);


?>