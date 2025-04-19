<?php
require_once "includes/db_config.php";
session_start();

$signup_error = "";
$signup_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username     = trim($_POST['username']);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email        = trim($_POST['email']);
    $phone        = trim($_POST['phone']);
    $country_code = trim($_POST['country_code']);
    $address      = trim($_POST['address']);

    if ($username && $password && $email && $phone && $country_code && $address) {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $signup_error = "Username already exists. Please choose another one.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone, country_code, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $password, $email, $phone, $country_code, $address);

            if ($stmt->execute()) {
                $signup_success = "Account created successfully! You can now login.";
                // Redirect to login after 2 seconds
                header("refresh:2;url=index.php");
            } else {
                $signup_error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    } else {
        $signup_error = "Please fill in all required fields.";
    }
}
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
    
    .success-message {
      background-color: rgba(209, 250, 229, 0.9);
      color: var(--success-color);
      padding: 12px 16px;
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      font-size: 14px;
      border-left: 4px solid var(--success-color);
    }
    
    .error-icon, .success-icon {
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
    
    .login-link {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
    }
    
    .login-link a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }
    
    .login-link a:hover {
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
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle password visibility
      const togglePassword = document.querySelector('.toggle-password');
      const passwordField = document.querySelector('#password');
      
      if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', function() {
          const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordField.setAttribute('type', type);
          
          // Change the icon based on password visibility
          const eyeIcon = this.querySelector('.eye-icon');
          if (type === 'password') {
            eyeIcon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>';
          } else {
            eyeIcon.innerHTML = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>';
          }
        });
      }
    });
  </script>
</head>
<body>
  <div class="container">
    <div class="auth-wrapper">
      <h1 class="auth-title">Create Account</h1>
      
      <?php if (!empty($signup_error)): ?>
        <div class="error-message" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="error-icon">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
          </svg>
          <?= htmlspecialchars($signup_error) ?>
        </div>
      <?php endif ?>
      
      <?php if (!empty($signup_success)): ?>
        <div class="success-message" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="success-icon">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
          </svg>
          <?= htmlspecialchars($signup_success) ?>
        </div>
      <?php endif ?>
      
      <form method="POST" class="auth-form" id="signup-form">
        <div class="form-group">
          <label for="username">Username</label>
          <input id="username" name="username" placeholder="Choose a username" required autocomplete="username">
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-input-group">
            <input id="password" name="password" type="password" placeholder="Create a password" required autocomplete="new-password">
            <button type="button" class="toggle-password" aria-label="Show password">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="eye-icon">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
              </svg>
            </button>
          </div>
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" placeholder="Your email address" required autocomplete="email">
        </div>
        
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input id="phone" name="phone" placeholder="Your phone number" required autocomplete="tel">
        </div>
        
        <div class="form-group">
          <label for="country_code">Country Code</label>
          <input id="country_code" name="country_code" placeholder="e.g. +1, +44, +91" required>
        </div>
        
        <div class="form-group">
          <label for="address">Address</label>
          <input id="address" name="address" placeholder="Your address" required autocomplete="street-address">
        </div>
        
        <button type="submit" class="auth-button">Create Account</button>
        
        <div class="login-link">
          Already have an account? <a href="index.php">Login</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>