<?php
session_start();



$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Home</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="wrapper">
    <div class="title-text">
      <div class="title home">Welcome</div>
    </div>
    <div class="form-container">
      <p style="text-align:center; font-size:18px; margin-top:30px;">
        Сайн байна уу, <strong><?= $username ?></strong>!  
      </p>
      <div style="text-align:center; margin-top:20px;">
        <a href="logout.php" style="color:#1a75ff; text-decoration:none;">Logout</a>
      </div>
    </div>
  </div>
</body>
</html>
