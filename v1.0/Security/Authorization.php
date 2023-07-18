<?php
function authorization(){
    $header = getallheaders();
    $authHead = base64_decode($header['Authorization']);
    print_r($authHead);
    $adminkey = $authHead['admin_secretKey'];
    // if($adminkey == )
       
}

authorization();

?>