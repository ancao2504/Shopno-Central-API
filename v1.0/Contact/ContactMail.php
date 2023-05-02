<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");


if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);

    $email = $_POST["email"];
    $phone = $_POST['phone'];
    $name = $_POST["name"];
    $subject = $_POST["subject"];
    $message = $_POST["message"];

    
    $mail = new PHPMailer();

        try {
            $mail->isSMTP();                                    
            $mail->Host       = 'b2b.flyfarint.com';                     
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = 'contact@b2b.flyfarint.com';                    
            $mail->Password   = '123Next2$';                            
            $mail->SMTPSecure = 'ssl';            
            $mail->Port       = 465;                                    

            //Recipients
            $mail->setFrom('contact@flyfarint.com', 'Fly Far Int');
            $mail->addAddress("$email", "$name");
            
            $mail->isHTML(true);                                  
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            
            $response['status']="success";
            $response['message']="New Email Send Successfully";   
        } catch (Exception $e) {
            $response['status']="error";
            $response['message']="Mail Doesn't Send"; 
        } 
    
    echo json_encode($response);
}


?>