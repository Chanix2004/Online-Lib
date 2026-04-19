<?php
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/security.php';

$security = new SecurityHelper($conn);
$success = '';
$error = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $user_data = $security->getEmailByVerificationToken($token);
    
    if ($user_data) {
        if ($security->verifyEmail($token)) {
            $success = 'Email verified successfully! You can now log in.';
        } else {
            $error = 'Failed to verify email. Please try again.';
        }
    } else {
        $error = 'Invalid or expired verification token.';
    }
} else {
    $error = 'No verification token provided.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Library Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .container h1 {
            color: #333;
            margin-bottom: 1rem;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 1rem;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Verification</h1>
        
        <?php if ($success): ?>
            <div class="success-message">
                <strong>✓ Success!</strong><br>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <a href="index.php" class="btn">Go to Login</a>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message">
                <strong>✗ Error!</strong><br>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <a href="index.php" class="btn">Back to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>

