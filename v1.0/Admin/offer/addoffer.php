<?php

require '../../config.php';
require '../../functions.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $category = $_POST["category"];
    $title = $_POST["title"];
    $description = str_replace("'","''", $_POST["description"]);
    $webFileName = $_FILES['webImage']['name'];
    $mobFileName = $_FILES['mobileImage']['name'];


    $title=str_replace("'","''", $title);
    $folder = "Admin/Offers";
    $size = 5000000;
    $time = date("dmYHis");
    $webImgNewFileName = "web_$title";
    $mobImgNewFileName = "mob_$title";

    $webImgURI = uploadImage("webImage", $size, $folder, $webFileName, $webImgNewFileName);
    $mobImgURI = uploadImage("mobileImage", $size, $folder, $mobFileName, $mobImgNewFileName);

    $offerId = "";
    $result = $conn->query("SELECT offerId FROM offers ORDER BY offerId DESC LIMIT 1");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $outputString = preg_replace('/[^0-9]/', '', $row["offerId"]);
        $number = (int) $outputString + 1;
        $offerId = "STOFF$number";
    } else {
        $offerId = "STOFF1000";
    }

    $sql = "INSERT INTO offers (`offerId`, `category`, `title`, `description`, `web_img`, `mob_img`, `created_at`) 
    VALUES ('$offerId', '$category', '$title', '$description', '$webImgURI', '$mobImgURI', '$time')";
    // echo $sql;
    if($conn->query($sql))
    {
        echo json_encode(
            array(
                "status" => "success",
                "message" => "Offer Added Successfully"
            )
            );
    }
    else
    {
        echo json_encode(
            array(
                "status" => "error",
                "message" => "Failed To Add Offer"
            )
            );
    }

}
else
{
    echo json_encode(
        array(
            "status" => "error",
            "message" => "Wrong Request Method"
        )
        );
}
