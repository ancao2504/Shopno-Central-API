<?php


date_default_timezone_set('Asia/Dhaka');
require_once realpath(__DIR__ . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$SECRETE_KEY = $_ENV['SECRETE_KEY'];
$AUTH_DOMAIN = $_ENV['AUTH_DOMAIN'];
$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER_NAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo '';
}
