<?php
// /includes/db_config.php
$servername = "lab123-server.mysql.database.azure.com";
$dbusername = "xyftmqlidm@lab123-server";
$dbpassword = "2$45dSSmsURJr7W5";
$dbname     = "login_db";

// Connect (no initial db)—so we can create it if it doesn’t exist
$conn = new mysqli($servername, $dbusername, $dbpassword);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create DB if needed
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`") 
    or die("DB create error: " . $conn->error);

// Select the DB
$conn->select_db($dbname);

// Create table if needed
$conn->query("
  CREATE TABLE IF NOT EXISTS users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
  )
") or die("Table create error: " . $conn->error);
