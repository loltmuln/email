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
  <style>
    :root {
      --primary-color: #4f46e5;
      --primary-hover: #4338ca;
      --dark-color: #1f2937;
      --text-color: #374151;
      --light-gray: #f3f4f6;
      --border-color: #d1d5db;
      --success-color: #10b981;
      --error-color: #ef4444;
      --radius: 8px;
      --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-color);
    }
    
    .container {
      width: 100%;
      max-width: 450px;
      padding: 20px;
    }
    
    .auth-wrapper {
      background-color: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
      padding: 40px 30px;
      position: relative;
    }
    
    .auth-title {
      color: var(--dark-color);
      font-size: 24px;
      font-weight: 700;
      text-align: center;
      margin-bottom: 25px;
      letter-spacing: -0.5px;
    }
    
    .error-message {
      background-color: rgba(254, 226, 226, 0.9);
      color: var(--error-color);
      padding: 12px 16px;
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      font-size: 14px;
      border-left: 4px solid var(--error-color);
    }
    
    .error-icon {
      margin-right: 10px;
      flex-shrink: 0;
    }
    
    .tabs {
      display: flex;
      margin-bottom: 25px;
      border-bottom: 2px solid var(--light-gray);
    }
    
    .tab-btn {
      flex: 1;
      background: none;
      border: none;
      padding: 12px;
      font-size: 16px;
      font-weight: 600;
      color: #9ca3af;
      cursor: pointer;
      transition: all 0.3s;
      position: relative;
    }
    
    .tab-btn.active {
      color: var(--primary-color);
    }
    
    .tab-btn.active::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 100%;
      height: 2px;
      background-color: var(--primary-color);
    }
    
    .forms-container {
      position: relative;
    }
    
    .auth-form {
      display: none;
    }
    
    .auth-form.active {
      display: block;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 500;
      color: var(--dark-color);
    }
    
    .form-group input,
    .password-input-group {
      width: 100%;
      border: 1px solid var(--border-color);
      border-radius: var(--radius);
      padding: 12px 16px;
      font-size: 16px;
      transition: all 0.3s;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }
    
    .password-input-group {
      display: flex;
      align-items: center;
      padding: 0;
      padding-left: 16px;
    }
    
    .password-input-group input {
      border: none;
      flex: 1;
      padding: 12px 0;
    }
    
    .password-input-group input:focus {
      box-shadow: none;
    }
    
    .toggle-password {
      background: none;
      border: none;
      padding: 0 16px;
      cursor: pointer;
      color: #9ca3af;
      display: flex;
      align-items: center;
    }
    
    .toggle-password:hover {
      color: var(--dark-color);
    }
    
    .form-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      font-size: 14px;
    }
    
    .remember-me {
      display: flex;
      align-items: center;
    }
    
    .remember-me input {
      margin-right: 8px;
      accent-color: var(--primary-color);
    }
    
    .forgot-link {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }
    
    .forgot-link:hover {
      color: var(--primary-hover);
      text-decoration: underline;
    }
    
    .auth-button {
      width: 100%;
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: var(--radius);
      padding: 14px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 10px;
      box-shadow: 0 4px 6px rgba(79, 70, 229, 0.25);
    }
    
    .auth-button:hover {
      background-color: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 6px 10px rgba(79, 70, 229, 0.3);
    }
    
    .auth-button:active {
      transform: translateY(0);
    }

    /* Decorative elements */
    .auth-wrapper::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(to right, var(--primary-color), #8b5cf6);
    }
    
    /* Custom checkbox styling */
    .remember-me input[type="checkbox"] {
      appearance: none;
      -webkit-appearance: none;
      width: 18px;
      height: 18px;
      border: 2px solid var(--border-color);
      border-radius: 4px;
      margin-right: 8px;
      position: relative;
      cursor: pointer;
    }
    
    .remember-me input[type="checkbox"]:checked {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .remember-me input[type="checkbox"]:checked::after {
      content: '';
      position: absolute;
      top: 2px;
      left: 5px;
      width: 5px;
      height: 10px;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
    }
  </style>
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