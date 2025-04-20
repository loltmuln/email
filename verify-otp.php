<?php
session_start();
include __DIR__ . '/includes/db_config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    $otp = trim($_POST['otp']);

    $stmt = $conn->prepare("SELECT reset_code, reset_expiry FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['reset_code'] === $otp && strtotime($row['reset_expiry']) > time()) {
            $update = $conn->prepare("UPDATE users SET reset_verified = 1 WHERE email = ?");
            $update->bind_param('s', $email);
            $update->execute();
            header("Location: forgot-password.php");
            exit;
        } else {
            $error = "Invalid or expired OTP.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Verify OTP</title></head>
<body>
<h2>Verify OTP</h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <input type="text" name="otp" required placeholder="Enter OTP"><br>
    <button type="submit">Verify</button>
</form>
</body>
</html>
