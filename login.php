<?php
session_start(); // Start the session

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "login_db";

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get username and password from POST data
$username = trim($_POST['username']);
$password = trim($_POST['password']);
if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
  echo "Invalid username.";
  exit;
}
if (empty($username) || empty($password)) {
 die("Username болон Passowrd хоосон байж болохгүй.");
}


$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    // Verify the hashed password
    if (password_verify($password, $row['password'])) {
        // Set session variables if login is successful
        $_SESSION['username'] = $row['username']; // use the username from the database
        $_SESSION['loggedin'] = true;
        // Output the styled login success page:
        ?>
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="UTF-8">
          <title>Login Success</title>
          <link rel="stylesheet" type="text/css" href="../loginapp/assets/css/style.css">
        </head>
        <body>
          <div class="wrapper">
            <div class="title-text">
              <div class="title login">Login Success</div>
            </div>
            <div class="form-container">
              <p style="text-align:center; font-size:18px; margin-top:30px;">
                Амжилттай login хийлээ, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
              </p>
              <div style="text-align:center; margin-top:20px;">
                <a href="logout.php" style="color:#1a75ff; text-decoration:none;">Logout</a>
              </div>
            </div>
          </div>
        </body>
        </html>
        <?php
    } else {
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
                  <div class="title signup">Password буруу</div>
              </div>
              <div class="form-container">
                  <p style="text-align:center; font-size:18px; margin-top:30px;">
                      Password буруу байна <a href="login.html" style="color:#1a75ff; text-decoration:none;">дахин оролдох</a>.
                  </p>
              </div>
          </div>
      </body>
      </html>';
    }
} else {
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
              <div class="title signup">Password буруу</div>
          </div>
          <div class="form-container">
              <p style="text-align:center; font-size:18px; margin-top:30px;">
                  Бүртгэлгүй Username байна <a href="login.html?signup" style="color:#1a75ff; text-decoration:none;">Бүртгүүлэх</a>.
              </p>
          </div>
      </div>
  </body>
  </html>';
}

$stmt->close();
$conn->close();
?>