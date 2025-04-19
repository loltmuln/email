<?php
$servername = "lab123-server.mysql.database.azure.com";
$dbusername = "xyftmqlidm@lab123-server";
$dbpassword = "2$45dSSmsURJr7W5";
$dbname = "login_db";

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the database exists and create it if not
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (!$conn->query($sql)) {
    echo "Error creating database: " . $conn->error;
    exit;
}

// Check if the users table exists and create it if not
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";
if (!$conn->query($sql)) {
    echo "Error creating table: " . $conn->error;
    exit;
}
?>
