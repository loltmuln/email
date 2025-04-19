<?php
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?error=invalid");
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];
$confirm  = $_POST['confirm_password'];

// Validate
if ($username === '' || $password === '' || $confirm === '' ||
    !preg_match('/^[a-zA-Z0-9]+$/', $username) ||
    $password !== $confirm) {
    header("Location: index.php?error=signup");
    exit;
}

// Check existing
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: index.php?error=exists");
    exit;
}

// Insert new user
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hash);
if ($stmt->execute()) {
    header("Location: index.php?success=signup");
    exit;
} else {
    header("Location: index.php?error=fail");
    exit;
}
