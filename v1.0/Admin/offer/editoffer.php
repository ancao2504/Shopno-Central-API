<?php
require '../../config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $offerId = $_POST["offerId"];
    $category = $_POST["category"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $webFileName = $_FILES['webImage']['name'];
    $mobFileName = $_FILES['mobileImage']['name'];

    $folder = "Admin/Offers";
    $size = 50000;
    $time = date("dmYHis");
    $webImgNewFileName = "web_$title";
    $mobImgNewFileName = "mob_$title";

    $webImgURI = uploadImage("webImage", $size, $folder, $webFileName, $webImgNewFileName);
    $mobImgURI = uploadImage("mobileImage", $size, $folder, $mobFileName, $mobImgNewFileName);

    $sql="UPDATE offers SET `category` = '$category', `title`='$title', `description`='$description', 
    `web_img`='$webImgURI', `mob_img`='$mob_img', `last_updated_at`='$time' WHERE `offerId`='$offerId'";
    if($conn->query($sql))
    {}
}