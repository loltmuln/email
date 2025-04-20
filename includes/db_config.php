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

// Connect using SSL
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

// Check connection
if (mysqli_connect_errno()) {
    die("DB connection failed: " . mysqli_connect_error());
}

// Check if the users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");

if ($table_check && $table_check->num_rows == 0) {
    // Table does not exist, create it
    $create_table_sql = <<<SQL
    CREATE TABLE users (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        country_code VARCHAR(5) NOT NULL,
        address VARCHAR(255) NOT NULL,
        reset_code VARCHAR(255) DEFAULT NULL,
        reset_expiry DATETIME DEFAULT NULL,
        reset_verified BOOLEAN DEFAULT FALSE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    SQL;

    if (! $conn->query($create_table_sql)) {
        die("Table creation failed: " . $conn->error);
    }
} else {
    // Table exists, check for missing columns
    $existing_columns = [];
    $columns_result = $conn->query("SHOW COLUMNS FROM users");

    while ($row = $columns_result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }

    $alterations = [];

    if (!in_array('reset_code', $existing_columns)) {
        $alterations[] = "ADD COLUMN reset_code VARCHAR(255) DEFAULT NULL";
    }

    if (!in_array('reset_expiry', $existing_columns)) {
        $alterations[] = "ADD COLUMN reset_expiry DATETIME DEFAULT NULL";
    }

    if (!in_array('reset_verified', $existing_columns)) {
        $alterations[] = "ADD COLUMN reset_verified BOOLEAN DEFAULT FALSE";
    }

    if (!empty($alterations)) {
        $alter_sql = "ALTER TABLE users " . implode(", ", $alterations) . ";";
        if (! $conn->query($alter_sql)) {
            die("Table alteration failed: " . $conn->error);
        }
    }
}
?>
