<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



    $agentId  = $_POST['agentId'];
    $Name  = $_POST['name'];
    $CompanyName  = $_POST['companyname'];
    $Email  = $_POST['email'];
    $Phone  = $_POST['phone'];
    $Password  = $_POST['password'];
    $Address  = $_POST['address'];

    $Date = date("Y-m-d H:i:s");
    $DateTime = date("D d M Y h:i A");

$fileName  =  $_FILES['file']['name'];
$tempPath  =  $_FILES['file']['tmp_name'];
$fileSize  =  $_FILES['file']['size'];

$needheight = 80;
$needwidth = 150;
		
if(empty($fileName)){
	$errorMSG = json_encode(array("message" => "please select image", "status" => false));	
	echo $errorMSG;
}
else{
	$upload_path = "../../asset/Agent/$agentId/CompanyImg/"; // set upload folder path 
	
	if (!file_exists($upload_path)) {
    	mkdir($upload_path, 0777, true);
	}
	
	$fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
		
	// valid image extensions
	$valid_extensions = array('png', 'PNG'); 

    $renameFile ="companylogo.$fileExt";
	
					
	// allow valid image file formats
	if(in_array($fileExt, $valid_extensions)){				
		//check file not exist our upload folder path
		if(!file_exists($upload_path . $fileName)){
				$arrtest = getimagesize($upload_path . $renameFile);
				$actualwidth = $arrtest[0];
				$actualheight = $arrtest[1];
				if($fileSize < 2000000){
					move_uploaded_file($tempPath, $upload_path . $renameFile); 
				}
				else{		
					$errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));	
					echo $errorMSG;
				}
		}
		else
		{		
			// check file size '5MB'
			if($fileSize < 2000000){
				move_uploaded_file($tempPath, $upload_path . $renameFile);
			}
			else{		
				$errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => "error"));	
				echo $errorMSG;
			}
		}
	}
	else
	{		
		$errorMSG = json_encode(array("message" => "Sorry, only  PNG files are allowed", "status" => "error"));	
		echo $errorMSG;		
	}
}

$fileUrl = isset($renameFile)? $renameFile:"";
    $sql = "UPDATE agent SET 
            name='$Name',
            company ='$CompanyName',
            email='$Email',
            password='$Password',
            companyadd='$Address',
            companyImage='$fileUrl',
            phone='$Phone',updated_at='$Date' where  agentId='$agentId'";

    if ($conn->query($sql) === TRUE) {

        $response['status']="success";
        $response['message']="Updated Successfully";
    }else{
        $response['status']="error";
           $response['message']="Update Failed";
    }

    echo json_encode($response);
