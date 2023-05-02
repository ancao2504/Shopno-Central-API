<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include '../config.php';

$data = json_decode(file_get_contents("php://input"), true);

$agentId = $_GET['agentId'];

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
	$upload_path = "../../../cdn.flyfarint.com/Agent/$agentId/"; // set upload folder path 
	
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
		
// if no error caused, continue ....
if(!isset($errorMSG))
{
    $fileUrl = "https://cdn.flyfarint.com/Agent/$agentId/$renameFile";
    
	$query = mysqli_query($conn,'UPDATE `agent` SET `companyImage`="'.$fileUrl.'" WHERE agentId="'.$agentId.'"');
			
	echo json_encode(array("message" => "Image Updated Successfully", "status" => "success"));	
}

?>