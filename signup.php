<?php
require_once "includes/db_config.php";

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
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone, country_code, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $password, $email, $phone, $country_code, $address);

        if ($stmt->execute()) {
            $signup_success = "✅ Account created successfully!";
        } else {
            $signup_error = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $signup_error = "❗ Please fill in all required fields.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f8f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .signup-form {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 400px;
        }
        .signup-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .signup-form input, .signup-form button {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .signup-form button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .signup-form button:hover {
            background: #0056b3;
        }
        .msg {
            text-align: center;
            margin-bottom: 10px;
        }
        .msg.success { color: green; }
        .msg.error { color: red; }
    </style>
</head>
<body>
    <form class="signup-form" method="POST" action="">
        <h2>Signup</h2>

        <?php if ($signup_success): ?>
            <div class="msg success"><?= $signup_success ?></div>
        <?php elseif ($signup_error): ?>
            <div class="msg error"><?= $signup_error ?></div>
        <?php endif; ?>

        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="text" name="country_code" placeholder="Country Code (e.g. +976)" required>
        <input type="text" name="address" placeholder="Address" required>
        <button type="submit">Sign Up</button>
    </form>
</body>
</html>
