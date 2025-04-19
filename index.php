<?php
session_start();
include __DIR__ . '/includes/db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = trim($_POST['username'] ?? '');
  $p = trim($_POST['password'] ?? '');

  if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $u) || !$p) {
    $error = "Please enter a valid username and password.";
  } else {
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param('s', $u);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1 && password_verify($p, $res->fetch_assoc()['password'])) {
      $_SESSION['username'] = $u;
      header('Location: home.php');
      exit;
    } else {
      $error = "Username or password is incorrect.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <div class="container">
    <div class="login-box">
      <h2>Welcome Back</h2>

      <?php if (!empty($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" class="form">
        <label for="username">Username</label>
        <input 
          type="text" 
          name="username" 
          id="username" 
          placeholder="Enter your username" 
          required 
        />

        <label for="password">Password</label>
        <input 
          type="password" 
          name="password" 
          id="password" 
          placeholder="Enter your password" 
          required 
        />

        <button type="submit">Login</button>
      </form>

      <div class="signup-redirect">
        <p>Don't have an account?</p>
        <a href="signup.php" class="btn-link">Sign up here</a>
      </div>
    </div>
  </div>
</body>
</html>
