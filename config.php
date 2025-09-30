<?php
$host = "localhost";
$user = "root";
$pass = "";   // or "root" if your MySQL has a password
$dbname = "schedulingdb";
$port = 3307; // custom port

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}
?>
