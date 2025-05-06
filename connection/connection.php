<?php
$host = "localhost:3306";
$username = "root"; // Default sa XAMPP
$password = ""; // Default sa XAMPP (walang password)
$database = "realivingdata"; // Palitan ng tamang database name

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}