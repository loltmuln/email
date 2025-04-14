<?php
// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "login_db";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from the form
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
  // Input validation for username: only alphanumeric characters
    if (!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9]*$/", $username)) {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Signup Error</title>
            <link rel="stylesheet" type="text/css" href="../loginapp/assets/css/style.css">
        </head>
        <body>
            <div class="wrapper">
                <div class="title-text">
                    <div class="title signup">Signup Error</div>
                </div>
                <div class="form-container">
                    <p class="error-message" style="text-align:center; font-size:18px; margin-top:30px;">
                        Хэрэглэгчийн нэр нь зөвхөн латин үсэг болон тоо агуулж байх ёстой.
                    </p>
                    <div style="text-align:center; margin-top:20px;">
                      <a href="login.html?signup" style="color:#1a75ff; text-decoration:none;">Бүртгүүлэхээр дахин оролдоно</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        exit;
    }
    // Check if the username already exists
    $sql_check = "SELECT * FROM users WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Username already taken message with a retry option
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Signup Error</title>
            <link rel="stylesheet" type="text/css" href="../loginapp/assets/css/style.css">
        </head>
        <body>
            <div class="wrapper">
                <div class="title-text">
                    <div class="title signup">Signup Error</div>
                </div>
                <div class="form-container">
                    <p class="error-message" style="text-align:center; font-size:18px; margin-top:30px;">
                        Хэрэглэгчийн нэрийг аль хэдийн авсан. Өөрийг сонгоно уу..
                    </p>
                    <div style="text-align:center; margin-top:20px;">
                      <a href="login.html?signup" style="color:#1a75ff; text-decoration:none;">Бүртгүүлэхээр дахин оролдоно</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    } else {
        // Insert new user into the database
        $sql_insert = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ss", $username, $hashed_password);
        if ($stmt_insert->execute()) {
            // Success message after inserting
            echo '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Signup Success</title>
                <link rel="stylesheet" type="text/css" href="../loginapp/assets/css/style.css">
            </head>
            <body>
                <div class="wrapper">
                    <div class="title-text">
                        <div class="title signup">Signup Success</div>
                    </div>
                    <div class="form-container">
                        <p style="text-align:center; font-size:18px; margin-top:30px;">
                            Амжилттай бүртгүүллээ. Одоо <a href="login.html" style="color:#1a75ff; text-decoration:none;">Login хийх</a> боломжтой.
                        </p>
                    </div>
                </div>
            </body>
            </html>';
        } else {
            echo "Error: " . $stmt_insert->error;
        }
    }

    // Close the statements
    $stmt_check->close();
    if (isset($stmt_insert)) {
        $stmt_insert->close();
    }
}

$conn->close();
?>