<?php
session_start(); // Start the session

// Destroy the session and redirect to the login page
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

header("Location: login.html"); // Redirect to login page
exit();
?>
