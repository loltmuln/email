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
<html>
<head>
  <meta charset="UTF-8">
  <title>Login / Signup</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="assets/js/script.js" defer></script>
</head>
<body>
  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif ?>

  <div class="wrapper">
    <!-- same form markup as before -->
    <!-- form action left blank so it posts back to index.php -->
    <form method="POST" class="login">
      <input name="username" placeholder="Username" required>
      <input name="password" type="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <form action="signup.php" method="POST" class="signup">
      <!-- signup form here -->
    </form>
  </div>
</body>
</html>
