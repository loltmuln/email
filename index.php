<?php
session_start();
include 'db_config.php';

// Handle Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic validation
    if ($username === '' || $password === '' ||
        !preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        die("Invalid input.");
    }

    // Lookup user
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hash);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['username'] = $username;
        $_SESSION['loggedin'] = true;
        header("Location: index.php?success=1");
        exit;
    } else {
        header("Location: index.php?error=login");
        exit;
    }
}

// If not POST (or after redirect), show the form & any messages:
$error   = $_GET['error']   ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login / Signup</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="assets/js/script.js" defer></script>
</head>
<body>
  <?php if ($success): ?>
    <p class="msg success">✅ Login successful! Welcome, <?=htmlspecialchars($_SESSION['username'])?></p>
  <?php elseif ($error === 'login'): ?>
    <p class="msg error">❌ Invalid username or password.</p>
  <?php endif; ?>

  <div class="wrapper">
    <!-- slide-controls, etc. -->
    <div class="form-inner">
      <!-- Login Form -->
      <form action="index.php" method="POST" class="login">
        <input type="text"  name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>
      <!-- Signup Form -->
      <form action="signup.php" method="POST" class="signup">
        <input type="text"  name="username"         placeholder="Username"        required>
        <input type="password" name="password"        placeholder="Password"        required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Signup</button>
      </form>
    </div>
  </div>
</body>
</html>
