<?php
session_start();
include('db_config.php'); 

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get username and password from POST data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate username format
    if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
      echo "Invalid username.";
      exit;
    }

    if (empty($username) || empty($password)) {
      die("Username болон Password хоосон байж болохгүй.");
    }

    // Prepare SQL and check if the user exists
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user exists, verify the password
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Successful login: set session variables
            $_SESSION['username'] = $row['username'];
            $_SESSION['loggedin'] = true;
            ?>
            <!DOCTYPE html>
            <html>
            <head>
              <meta charset="UTF-8">
              <title>Login Success</title>
              <link rel="stylesheet" type="text/css" href="assets/css/style.css">
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
            exit;
        } else {
            // Wrong password: show error page with link back to login/signup interface
            ?>
            <!DOCTYPE html>
            <html>
            <head>
              <meta charset="UTF-8">
              <title>Login Failed</title>
              <link rel="stylesheet" type="text/css" href="assets/css/style.css">
            </head>
            <body>
              <div class="wrapper">
                <div class="title-text">
                  <div class="title signup">Password буруу</div>
                </div>
                <div class="form-container">
                  <p style="text-align:center; font-size:18px; margin-top:30px;">
                    Password буруу байна <a href="index.php" style="color:#1a75ff; text-decoration:none;">дахин оролдох</a>.
                  </p>
                </div>
              </div>
            </body>
            </html>
            <?php
            exit;
        }
    } else {
        // Username not found: show error page with link to signup
        ?>
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="UTF-8">
          <title>Login Failed</title>
          <link rel="stylesheet" type="text/css" href="assets/css/style.css">
        </head>
        <body>
          <div class="wrapper">
            <div class="title-text">
              <div class="title signup">Бүртгэлгүй Username байна</div>
            </div>
            <div class="form-container">
              <p style="text-align:center; font-size:18px; margin-top:30px;">
                Бүртгэлгүй Username байна <a href="index.php?signup" style="color:#1a75ff; text-decoration:none;">Бүртгүүлэх</a>.
              </p>
            </div>
          </div>
        </body>
        </html>
        <?php
        exit;
    }
    $stmt->close();
    $conn->close();
}
?>
<!-- If not a POST request, display the login/signup form -->
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login / Signup</title>
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <script src="assets/js/script.js" defer></script>
</head>
<body>
  <div class="wrapper">
    <div class="title-text">
      <div class="title login">Login Form</div>
      <div class="title signup">Signup Form</div>
    </div>
    <div class="form-container">
      <div class="slide-controls">
        <input type="radio" name="slide" id="login" checked>
        <input type="radio" name="slide" id="signup">
        <label for="login" class="slide login">Login</label>
        <label for="signup" class="slide signup">Signup</label>
        <div class="slider-tab"></div>
      </div>
      <div class="form-inner">
        <!-- Login Form -->
        <form action="index.php" method="POST" class="login">
          <div class="field">
            <input type="text" placeholder="Username" name="username" required>
          </div>
          <div class="field">
            <input type="password" placeholder="Password" name="password" required>
          </div>
          <div class="pass-link"><a href="#">Forgot password?</a></div>
          <div class="field btn">
            <div class="btn-layer"></div>
            <input type="submit" value="Login">
          </div>
          <div class="signup-link"><a href="#">Signup</a></div>
        </form>
        <!-- Signup Form -->
        <form action="signup.php" method="POST" class="signup">
          <div class="field">
            <input type="text" placeholder="Username" name="username" required>
          </div>
          <div class="field">
            <input type="password" placeholder="Password" name="password" required>
          </div>
          <div class="field">
            <input type="password" placeholder="Confirm Password" name="confirm_password" required>
          </div>
          <div class="field btn">
            <div class="btn-layer"></div>
            <input type="submit" value="Signup">
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="assets/js/script.js"></script>
</body>
</html>
