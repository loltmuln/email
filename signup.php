<?php
session_start();
include __DIR__ . '/includes/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = trim($_POST['username']);
  $p = trim($_POST['password']);

  // Validate
  if (!preg_match('/^[a-zA-Z0-9]+$/', $u) || !$p) {
    $error = "Invalid username or password.";
  } else {
    // Already exists?
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username=?");
    $stmt->bind_param('s', $u);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) {
      $error = "Username already taken.";
    } else {
      // Insert
      $hash = password_hash($p, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (username,password) VALUES (?,?)");
      $stmt->bind_param('ss', $u, $hash);
      if ($stmt->execute()) {
        header('Location: index.php?signup=success');
        exit;
      }
      $error = "Signup failed, please try again.";
    }
  }
}

// Show form + any $error
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Signup</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif ?>

  <form method="POST">
    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Password" required>
    <button type="submit">Signup</button>
  </form>
</body>
</html>
