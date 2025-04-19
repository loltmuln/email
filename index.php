<?php
session_start();
include __DIR__ . '/includes/db_config.php';
// LOGIN handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = trim($_POST['username']);
  $p = trim($_POST['password']);
  // Validate
  if (!preg_match('/^[a-zA-Z0-9]+$/', $u) || !$p) {
    $error = "Invalid credentials.";
  } else {
    $stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
    $stmt->bind_param('s', $u);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1 && password_verify($p, $res->fetch_assoc()['password'])) {
      $_SESSION['username'] = $u;
      header('Location: home.php'); // or show success template
      exit;
    }
    $error = "Username or password incorrect.";
  }
}
// HTML + error or form
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login / Signup</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="assets/js/script.js" defer></script>
</head>
<body>
  <div class="container">
    <div class="auth-wrapper">
      <h1 class="auth-title">Welcome Back</h1>
      
      <?php if (!empty($error)): ?>
        <div class="error-message" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="error-icon">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
          </svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif ?>
      
      <div class="tabs">
        <button type="button" class="tab-btn active" data-target="login">Login</button>
        <button type="button" class="tab-btn" data-target="signup">Sign Up</button>
      </div>
      
      <div class="forms-container">
        <!-- Login form -->
        <form method="POST" class="auth-form login-form active" id="login-form">
          <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" placeholder="Enter your username" required autocomplete="username">
          </div>
          
          <div class="form-group">
            <label for="password">Password</label>
            <div class="password-input-group">
              <input id="password" name="password" type="password" placeholder="Enter your password" required autocomplete="current-password">
              <button type="button" class="toggle-password" aria-label="Show password">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="eye-icon">
                  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                </svg>
              </button>
            </div>
          </div>
          
          <div class="form-footer">
            <div class="remember-me">
              <input type="checkbox" id="remember" name="remember">
              <label for="remember">Remember me</label>
            </div>
            <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
          </div>
          
          <button type="submit" class="auth-button">Login</button>
        </form>
        
        <!-- Signup form -->
        <form action="signup.php" method="POST" class="auth-form signup-form" id="signup-form">
          <!-- signup form here -->
        </form>
      </div>
    </div>
  </div>
</body>
</html>