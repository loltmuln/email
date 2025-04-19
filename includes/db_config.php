<?php
// includes/db_config.php

$host       = 'db-mysql-nyc3-22796-do-user-21090065-0.l.db.ondigitalocean.com';
$dbusername = 'doadmin';
$dbpassword = 'AVNS_8cy3AGMJSGMDiQXgXnW';
$dbname     = 'defaultdb';
$port       = 25060;

// Path to DigitalOcean CA certificate
$ssl_ca = __DIR__ . '/certs/ca-certificate.crt';

$conn = mysqli_init();

// Set SSL
mysqli_ssl_set(
    $conn,
    NULL,
    NULL,
    $ssl_ca,
    NULL,
    NULL
);

// Connect
mysqli_real_connect(
    $conn,
    $host,
    $dbusername,
    $dbpassword,
    $dbname,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL
);

if (mysqli_connect_errno()) {
    die("DB connection failed: " . mysqli_connect_error());
}

// Create the users table if it doesn't exist
$table_sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    country_code VARCHAR(5) NOT NULL,
    address VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if (! $conn->query($table_sql)) {
    die("Table creation failed: " . $conn->error);
}
