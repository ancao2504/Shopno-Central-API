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




if($_SERVER["REQUEST_METHOD"] == 'POST')
{
    $jsonData=json_decode($_POST["requestedBody"], true);

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
    $img=$_FILES["img"]["name"];
    $sql="SELECT EMP_ID FROM users ORDER BY id DESC LIMIT 1";
    
    $result=$conn->query($sql)->fetch_assoc();
    // echo json_encode($result);
    $empId="EMP1000";
    if(!empty($result))
    {   
        $outputString = preg_replace('/[^0-9]/', '', $result["EMP_ID"]);
        $num= ((int)$outputString)+1;
        $empId="EMP$num";
    }

    $img=uploadImage("img", 5000000, "../../../asset/users/$empId/profilePhoto/", $img, $role);

    // echo $img;

    $sql="INSERT INTO users 
    (
    `EMP_ID`, `username`, `fname`, `lname`, `email`,
    `password`, `role`, `add`, `edit`, `delete`, 
    `status`, `img`
    )
    VALUES
    (
    '$empId', '$userName', '$fName', '$lName', '$email', 
    '$password','$role', '$add', '$edit', '$delete', 
    '$status', '$img'
    )
    ";

    if($conn->query($sql))
    {
        $response["status"] ="Success";
        $response["message"] ="User Registration Successfull";
    }
    else
    {
        $response["status"] ="error";
        $response["message"] ="User Registration Failed";
    }
    


}
else if (array_key_exists("users", $_GET))
{
    $response=$conn->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);
    
}

echo json_encode($response);


?>