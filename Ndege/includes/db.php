<?php
$connect = new mysqli('localhost', 'root', '', 'osfms');
if ($connect->connect_error) {
 
    die("Connection failed: " . $connect->connect_error);
}
// Using PDO for better error handling and security
$pdo = new PDO("mysql:host=127.0.0.1;dbname=osfms;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
?>
