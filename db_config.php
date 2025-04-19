<?php
// -----------------------------------------------------------------
// Centralized DB setup: connect → create DB if needed → select it → create table if needed
// -----------------------------------------------------------------

$host     = "lab123-server.mysql.database.azure.com";
$user     = "xyftmqlidm@lab123-server";
$pass     = "2\$45dSSmsURJr7W5";
$dbname   = "login_db";

// 1) Connect *without* specifying a database
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2) Create the database if it doesn't exist
if (! $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
    die("Error creating database: " . $conn->error);
}

// 3) Select the database
if (! $conn->select_db($dbname)) {
    die("Error selecting database: " . $conn->error);
}

// 4) Create the users table if it doesn't exist
$createUsers = <<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if (! $conn->query($createUsers)) {
    die("Error creating users table: " . $conn->error);
}
