<?php
session_start();
include __DIR__ . '/includes/db_config.php';

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get and sanitize email
  $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
  
  // Validate email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Please enter a valid email address.";
  } else {
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
      // Don't reveal if email exists or not (security best practice)
      $success = "If your email is registered, you will receive reset instructions shortly.";
    } else {
      // Generate a reset code (simple implementation)
      $reset_code = md5(uniqid(rand(), true));
      $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
      
      // Store reset code in database
      $update_stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_expiry = ? WHERE email = ?");
      $update_stmt->bind_param('sss', $reset_code, $expiry, $email);
      
      if ($update_stmt->execute()) {
        // Send email with reset link
        $reset_link = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?code=" . $reset_code;
        
        $to = $email;
        $subject = "Password Reset Request";
        $message = "
        <html>
        <head>
          <title>Password Reset</title>
        </head>
        <body>
          <p>Hello,</p>
          <p>We received a request to reset your password. Click the link below to set a new password:</p>
          <p><a href=\"$reset_link\">Reset Password</a></p>
          <p>This link will expire in 1 hour.</p>
          <p>If you didn't request this, please ignore this email.</p>
          <p>Regards,<br>Your Website Team</p>
        </body>
        </html>
        ";
        
        // Headers for HTML email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        
        if (mail($to, $subject, $message, $headers)) {
          $success = "Reset instructions have been sent to your email.";
        } else {
          $error = "Could not send reset email. Please try again later.";
        }
      } else {
        $error = "An error occurred. Please try again later.";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="assets/css/style.css">
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
    
    .auth-subtitle {
      text-align: center;
      margin-bottom: 25px;
      color: var(--text-color);
      font-size: 15px;
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
    
    .message-icon {
      margin-right: 10px;
      flex-shrink: 0;
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
    
    .form-group input {
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
    
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
    }
    
    .back-link:hover {
      text-decoration: underline;
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
</head>
<body>
  <div class="container">
    <div class="auth-wrapper">
      <h1 class="auth-title">Forgot Password</h1>
      <p class="auth-subtitle">Enter your email address and we'll send you instructions to reset your password.</p>
      
      <?php if (!empty($error)): ?>
        <div class="error-message" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="message-icon">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
          </svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif ?>
      
      <?php if (!empty($success)): ?>
        <div class="success-message" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="message-icon">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
          </svg>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif ?>
      
      <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="Enter your email address" required autocomplete="email">
        </div>
        
        <button type="submit" class="auth-button">Send Reset Instructions</button>
      </form>
      
      <a href="index.php" class="back-link">Back to Login</a>
    </div>
  </div>
</body>
</html>