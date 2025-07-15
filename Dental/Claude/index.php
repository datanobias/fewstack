<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// Initialize database
$db = new Database();

// Handle login
if (isset($_POST['login'])) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}

// Check if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Staff Talent Scout</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h1>🦷 Dental Staff Talent Scout</h1>
                <p>AI-Powered Dental Staff Recruitment</p>
            </div>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>
            
            <div class="demo-info">
                <h3>Demo Credentials:</h3>
                <p>Username: admin<br>Password: dental123</p>
            </div>
        </div>
    </div>
</body>
</html>