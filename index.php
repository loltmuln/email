<?php
session_start();

// Better security settings for session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Only for HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Prevent session fixation
if (empty($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Include database configuration
require_once __DIR__ . '/includes/db_config.php';

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security validation failed. Please try again.";
    } else {
        // LOGIN handling
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
        
        // Validate credentials
        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = "Username must contain only letters, numbers, and underscores (3-20 characters).";
        } else {
            try {
                // Get user from database
                $stmt = $conn->prepare("SELECT id, username, password, failed_attempts, locked_until FROM users WHERE username = ?");
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    // Check if account is locked
                    if (!empty($user['locked_until']) && $user['locked_until'] > time()) {
                        $wait_time = ceil(($user['locked_until'] - time()) / 60);
                        $error = "This account is temporarily locked. Please try again in {$wait_time} minutes.";
                    } else {
                        // Verify password
                        if (password_verify($password, $user['password'])) {
                            // Successful login
                            
                            // Reset failed attempts
                            $reset_stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
                            $reset_stmt->bind_param('i', $user['id']);
                            $reset_stmt->execute();
                            
                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);
                            
                            // Set user session
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['last_activity'] = time();
                            
                            // Log successful login
                            $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
                            $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id, ip_address, status) VALUES (?, ?, 'success')");
                            $log_stmt->bind_param('is', $user['id'], $ip);
                            $log_stmt->execute();
                            
                            // Redirect to dashboard/home
                            header('Location: home.php');
                            exit;
                        } else {
                            // Failed login - increment failed attempts
                            $failed_attempts = $user['failed_attempts'] + 1;
                            
                            // Lock account after 5 failed attempts for 15 minutes
                            $locked_until = null;
                            if ($failed_attempts >= 5) {
                                $locked_until = time() + (15 * 60); // 15 minutes
                            }
                            
                            // Update failed attempts
                            $update_stmt = $conn->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
                            $update_stmt->bind_param('iii', $failed_attempts, $locked_until, $user['id']);
                            $update_stmt->execute();
                            
                            // Log failed login
                            $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
                            $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id, ip_address, status) VALUES (?, ?, 'failure')");
                            $log_stmt->bind_param('is', $user['id'], $ip);
                            $log_stmt->execute();
                            
                            $error = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username not found - use same error message to prevent username enumeration
                    $error = "Invalid username or password.";
                    
                    // Optional: Log unknown username attempts
                    $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
                    $log_stmt = $conn->prepare("INSERT INTO login_logs (username, ip_address, status) VALUES (?, ?, 'unknown_user')");
                    $log_stmt->bind_param('ss', $username, $ip);
                    $log_stmt->execute();
                }
            } catch (Exception $e) {
                // Log error securely (never expose to user)
                error_log("Login error: " . $e->getMessage());
                $error = "An error occurred during login. Please try again later.";
            }
        }
    }
}

// Generate new CSRF token for the form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <div class="wrapper">
        <div class="auth-container">
            <h1>Welcome Back</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="tabs">
                <button class="tab active" data-target="login">Login</button>
                <button class="tab" data-target="signup">Sign Up</button>
            </div>
            
            <div id="login" class="tab-content active">
                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" name="username" placeholder="Enter your username" 
                               value="<?= isset($username) ? htmlspecialchars($username) : '' ?>"
                               autocomplete="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input id="password" name="password" type="password" 
                                   placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <svg class="eye-icon" viewBox="0 0 24 24" width="24" height="24">
                                    <path d="M12 4.5C7 4.5 2.7 7.6 1 12c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5c-1.7-4.4-6-7.5-11-7.5zm0 12.5c-2.8 0-5-2.2-5-5s2.2-5 5-5 5 2.2 5 5-2.2 5-5 5zm0-8c-1.7 0-3 1.3-3 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                
                <?php if (!empty($social_login_enabled)): ?>
                <div class="social-login">
                    <p>Or login with</p>
                    <div class="social-buttons">
                        <a href="auth/google.php" class="btn btn-social google">Google</a>
                        <a href="auth/facebook.php" class="btn btn-social facebook">Facebook</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div id="signup" class="tab-content">
                <p>New user? Please <a href="signup.php">register here</a>.</p>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and content
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById(this.dataset.target).classList.add('active');
            });
        });
        
        // Toggle password visibility
        const togglePassword = document.querySelector('.toggle-password');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.classList.add('visible');
                } else {
                    passwordInput.type = 'password';
                    this.classList.remove('visible');
                }
            });
        }
    });
    </script>
</body>
</html>