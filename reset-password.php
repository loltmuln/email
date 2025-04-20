<?php
session_start();
include __DIR__ . '/includes/db_config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT reset_verified FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && $row['reset_verified']) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_expiry = NULL, reset_verified = 0 WHERE email = ?");
            $update->bind_param('ss', $hashed, $email);
            if ($update->execute()) {
                $success = "Your password has been reset.";
                session_destroy();
            } else {
                $error = "Could not update password.";
            }
        } else {
            $error = "OTP verification is required.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
<h2>Reset Password</h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
<form method="POST">
    <input type="password" name="password" required placeholder="New Password"><br>
    <input type="password" name="confirm_password" required placeholder="Confirm Password"><br>
    <button type="submit">Reset Password</button>
</form>
</body>
</html>
