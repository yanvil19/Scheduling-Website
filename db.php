<?php
$host = "localhost";
$user = "root";        
$pass = "";            // update if you set a MySQL password
$db   = "schedulingdb"; 
$port = 3307;          // your MySQL port

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>
