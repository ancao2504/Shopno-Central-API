<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");


if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
        $_POST = json_decode(file_get_contents('php://input'), true);

        $company_name = $_POST['companyname'];
        $companyaddress = $_POST['companyaddress'];
        $firstName = $_POST['fname'];
        $lastName = $_POST['lname'];
        $userEmail = trim($_POST['contactpersonemail']);
        $mypassword = trim($_POST['password']);
        $phone = $_POST['contactpersonphonenumber'];

        $name = $firstName . " " . $lastName;
        $createdTime = date('Y-m-d H:i:s');

            $sql = "INSERT INTO `agent_failed`(
                `name`,
                `email`,
                `password`,
                `phone`,
                `status`,
                `company`,
                `companyadd`,
                `joinAt`
            )
            VALUES(
                '$name',
                '$userEmail',
                '$mypassword',
                '$phone',
                'failed',
                '$company_name',
                '$companyaddress',
                '$createdTime'
                
            )";

            if ($conn->query($sql) === TRUE) {
                
               
              //Agent maill
//               $OwnerEmail = '
//               <!DOCTYPE html>
//               <html lang="en">
//                 <head>
//                   <meta charset="UTF-8" />
//                   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
//                   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
//                   <title>Deposit Request 
//               </title>
//                 </head>
//                 <body>
//                   <div
//                     class="div"
//                     style="
//                       width: 650px;
//                       height: 100vh;
//                       margin: 0 auto;
//                     "
//                   >
//                     <div
//                       style="
//                         width: 650px;
//                         height: 200px;
//                         background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
//                         border-radius: 20px 0px  20px  0px;
              
//                       "
//                     >
//                       <table
//                         border="0"
//                         cellpadding="0"
//                         cellspacing="0"
//                         align="center"
//                         style="
//                           border-collapse: collapse;
//                           border-spacing: 0;
//                           padding: 0;
//                           width: 650px;
//                           border-radius: 10px;
              
//                         "
//                       >
//                         <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               font-weight: bold;
//                               font-size: 20px;
//                               line-height: 38px;
//                               padding-top: 30px;
//                               padding-bottom: 10px;
//                             "
//                           >
//                             <a href="https://www.flyfarint.com/"
//                               ><img
//                               src="https://cdn.flyfarint.com/logo.png"
//                                 width="130px"
//                             /></a>
              
//                           </td>
//                         </tr>
//                       </table>
              
//                       <table
//                         border="0"
//                         cellpadding="0"
//                         cellspacing="0"
//                         align="center"
//                         bgcolor="white"
//                         style="
//                           border-collapse: collapse;
//                           border-spacing: 0;
//                           padding: 0;
//                           width: 550px;
//                         "
//                       >
//                         <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               font-size: 19px;
//                               line-height: 38px;
//                               padding-top: 20px;
//                               background-color: white;
              
              
//                             "
//                           >
//               New Agent Register
//                       </td>
//                         </tr>
//                         <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               padding-top: 15px;
//                               font-size: 12px;
//                               line-height: 18px;
//                               color: #929090;
//                               padding-right: 20px;
//                               background-color: white;
              
//                             "
//                           >
//               Dear Concern, We Register as a Agent of Flyway International
//                   </td>     </tr>
              
                   
                  
//                                   <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               padding-top: 20px;
//                               font-size: 13px;
//                               line-height: 18px;
//                               color: #929090;
//                               padding-top: 20px;
//                               width: 100%;
//                               background-color: white;
              
//                             "
//                           >
//                              Agent Name: <span style="color: #003566">'.$name.'</span> 
//                           </td>
//                         </tr>
              
//                                             <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               padding-top: 20px;
//                               font-size: 13px;
//                               line-height: 18px;
//                               color: #929090;
//                               padding-top: 10px;
//                               width: 100%;
//                               background-color: white;
              
//                             "
//                           >
//                              Email:  <span style="color: #003566">'.$userEmail.'	</span> 
//                           </td>
//                         </tr>
//                                                       <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               padding-top: 20px;
//                               font-size: 13px;
//                               line-height: 18px;
//                               color: #929090;
//                               padding-top: 10px;
//                               width: 100%;
//                               background-color: white;
              
//                             "
//                           >
//                              Address: <span style="color: #003566">'.$companyaddress.'	</span> 
//                           </td>
//                         </tr>
              
              
              
//                            <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               padding-top: 20px;
//                               font-size: 13px;
//                               line-height: 18px;
//                               color: #929090;
//                               padding-top: 20px;
//                               width: 100%;
//                               background-color: white;
              
//                             "
//                           >
//                                As a result, We pray and hope that you would accept our agent request and oblige accordingly.
//                           </td>
//                         </tr>
              
              
//                            <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               padding-top: 20px;
//                               font-size: 13px;
//                               line-height: 18px;
//                               color: #929090;
//                               padding-top: 20px;
//                               width: 100%;
//                               background-color: white;
              
//                             "
//                           >
//                              Sincerely,
              
//                           </td>
//                         </tr>
              
//                            <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               font-size: 13px;
//                               line-height: 18px;
//                               color: #929090;
//                               width: 100%;
//                               background-color: white;
//                               padding-bottom: 20px
              
//                             "
//                           >
//                           '.$company_name.'
              
//                           </td>
//                         </tr>
              
//                     </div>
//                   </div>
//                 </body>
//               </html>
              
// ';

//                 $mail1 = new PHPMailer();

//                 try {
//                     $mail1->isSMTP();                                    
//                     $mail1->Host       = 'b2b.flyfarint.com';                     
//                     $mail1->SMTPAuth   = true;                                  
//                     $mail1->Username   = 'noreply@b2b.flyfarint.com';                    
//                     $mail1->Password   = '123Next2$';                            
//                     $mail1->SMTPSecure = 'ssl';            
//                     $mail1->Port       = 465;                                    

//                     //Recipients
//                     $mail1->setFrom('noreply@flyfarint.com', 'Fly Far Int');
//                      $mail1->addAddress("otaoperation@flyfarint.com", "Failed Sign UP");
                    
//                     $mail1->isHTML(true);                                  
//                     $mail1->Subject = "Sign Up Request - $company_name";
//                     $mail1->Body    = $OwnerEmail;


//                     if(!$mail1->Send()) {
//                         $response['status']="success";
//                         $response['message']="Data Saved Successfully";
//                         $response['error']="Email Not Send";
//                     } else {
//                         $response['status']="success";
//                         $response['message']="Data Saved Successfully";
//                     }
                        
                    
                                                  
//                 }catch (Exception $e) {
//                     $response['status']="error";
//                     $response['message']="Mail Doesn't Send"; 
//                 } 

                            $response['status']="success";
                            $response['message']="Data Saved Successfully";      
                              
            }
        }
        
        echo json_encode($response);
    



?>