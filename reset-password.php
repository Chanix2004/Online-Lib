<?php
session_start();
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Set database timezone to UTC to match PHP
$conn->query("SET time_zone='+00:00'");

// Now include security and other files
require_once 'includes/security.php';

$security = new SecurityHelper($conn);
$error = '';
$warning = '';
$success = '';
$user_id = null;
$step = 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("=== FORM SUBMITTED ===");
    error_log("POST data: " . json_encode($_POST));
    
    if (isset($_POST['step'])) {
        $step = intval($_POST['step']);
        
        // Step 1: Request password reset
        if ($step == 1 && isset($_POST['email'])) {
            $email = trim($_POST['email']);
            
            error_log("=== PASSWORD RESET REQUEST ===");
            error_log("Email: " . $email);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
                error_log("ERROR: Invalid email format: " . $email);
            } else {
                try {
                    // Check if user exists
                    $sql = "SELECT user_id FROM users WHERE email = ?";
                    $stmt = $conn->prepare($sql);
                    
                    if (!$stmt) {
                        $error = 'Database error. Please try again.';
                        error_log("ERROR: prepare() failed: " . $conn->error);
                    } else {
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $result = $stmt->get_result()->fetch_assoc();
                        
                        if ($result) {
                            $user_id = $result['user_id'];
                            error_log("User found: $user_id");
                            
                            $token = $security->generatePasswordResetToken($user_id);
                            
                            if ($token) {
                                error_log("Token generated successfully (first 30 chars): " . substr($token, 0, 30));
                                
                                // Try to send email
                                $email_sent = $security->sendPasswordResetEmail($email, $token);
                                
                                $_SESSION['reset_email'] = $email;
                                $_SESSION['reset_token'] = $token; // For testing when email doesn't work
                                
                                if ($email_sent) {
                                    $success = 'Password reset link has been sent to your email. Check your inbox (and spam folder) for the link.';
                                    error_log("Email sent successfully to: " . $email);
                                } else {
                                    // Email failed - show token for testing/development
                                    $warning = 'Note: Email sending is not configured on this server. Below is your reset link for testing:';
                                    $_SESSION['show_reset_link'] = true;
                                    error_log("WARNING: Email sending failed. Test mode enabled.");
                                }
                                $step = 2;
                            } else {
                                $error = 'Failed to generate reset token. Please try again later.';
                                error_log("ERROR: Token generation failed");
                            }
                        } else {
                            // Don't reveal if email exists or not (security best practice)
                            $success = 'If an account with this email exists, a password reset link has been sent.';
                            error_log("No user found for email: " . $email);
                            $step = 2;
                        }
                        $stmt->close();
                    }
                } catch (Exception $e) {
                    $error = 'An error occurred while processing your request. Please try again.';
                    error_log("EXCEPTION in password reset request: " . $e->getMessage());
                }
            }
        }
    }
}

// If token provided in URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    error_log("=== TOKEN VERIFICATION ===");
    error_log("Token from URL (first 30 chars): " . substr($token, 0, 30));
    error_log("Token length: " . strlen($token));
    
    if (empty($token)) {
        $error = 'No reset token provided.';
        error_log("ERROR: Empty token");
    } else {
        try {
            $user_id = $security->verifyPasswordResetToken($token);
            error_log("Verification result - user_id: " . ($user_id ? $user_id : 'FALSE'));
            
            if (!$user_id) {
                $error = 'Invalid or expired password reset link. Please request a new one.';
                error_log("ERROR: Token verification failed");
            } else {
                $step = 3; // Show password reset form
                error_log("SUCCESS: Token verified for user $user_id");
            }
        } catch (Exception $e) {
            $error = 'An error occurred while processing your request. Please try again.';
            error_log("EXCEPTION in verifyPasswordResetToken: " . $e->getMessage());
        }
    }
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_token'])) {
    $token = trim($_POST['reset_token']);
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    error_log("=== PASSWORD RESET SUBMISSION ===");
    error_log("Token (first 30 chars): " . substr($token, 0, 30));
    
    if (empty($token)) {
        $error = 'No reset token provided in form.';
        error_log("ERROR: Empty token in form");
    } else {
        try {
            $user_id = $security->verifyPasswordResetToken($token);
            error_log("Token verification result: " . ($user_id ? $user_id : 'FALSE'));
            
            if (!$user_id) {
                $error = 'Invalid or expired reset token.';
                error_log("ERROR: Token verification failed during password reset");
            } elseif (empty($new_password)) {
                $error = 'Please enter a new password.';
            } elseif (strlen($new_password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Passwords do not match.';
            } elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
                $error = 'Password must contain at least one uppercase letter and one number.';
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    $error = 'Database error occurred. Please try again.';
                    error_log("ERROR: prepare() failed: " . $conn->error);
                } else {
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        // Mark token as used
                        $security->markTokenAsUsed($token);
                        error_log("SUCCESS: Password reset for user $user_id, token marked as used");
                        
                        // Clear sensitive session data
                        unset($_SESSION['reset_email']);
                        unset($_SESSION['reset_token']);
                        unset($_SESSION['show_reset_link']);
                        
                        $success = 'Password reset successfully! You can now log in with your new password.';
                        $step = 4; // Success step
                    } else {
                        $error = 'Failed to reset password. Please try again.';
                        error_log("ERROR: execute() failed: " . $stmt->error);
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'An error occurred while processing your reset. Please try again.';
            error_log("EXCEPTION in password reset: " . $e->getMessage());
        }
    }
}

// Clean up expired tokens after all processing is done
$security->cleanupExpiredTokens();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Library Management System</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/unified.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            background: rgba(30, 30, 50, 0.9);
            padding: 2.5rem;
            border-radius: 12px;
            border: 1px solid rgba(0, 212, 255, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            width: 100%;
        }
        
        h1 {
            color: #ffffff;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #b0b0b0;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 5px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }
        
        
        input:focus {
            outline: none;
            border-color: #00d4ff;
            background: rgba(0, 212, 255, 0.05);
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.2);
        }
        
        button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: #000;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        button:hover {
            background: linear-gradient(135deg, #00e8ff 0%, #00b3e6 100%);
            box-shadow: 0 5px 20px rgba(0, 212, 255, 0.3);
        }
        
        .error-message {
            background: rgba(255, 77, 77, 0.1);
            color: #ff6b6b;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 77, 77, 0.3);
        }
        
        .success-message {
            background: rgba(51, 255, 153, 0.1);
            color: #33ff99;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid rgba(51, 255, 153, 0.3);
        }
        
        .info-box {
            background: rgba(0, 212, 255, 0.1);
            color: #64d8ff;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid rgba(0, 212, 255, 0.3);
        }
        
        .btn-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .btn-link a {
            color: #00d4ff;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .btn-link a:hover {
            color: #00e8ff;
            text-decoration: underline;
        }
    </style>
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Reset Password</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($warning): ?>
            <div style="background: rgba(255, 193, 7, 0.1); color: #ffc107; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid rgba(255, 193, 7, 0.3);">
                ⚠️ <?php echo htmlspecialchars($warning); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success && $step != 4): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <div class="info-box">
                Enter your email address and we'll send you a password reset link.
            </div>
            <form method="POST">
                <input type="hidden" name="step" value="1">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit">Send Reset Link</button>
            </form>
        <?php endif; ?>
        
        <?php if ($step == 2): ?>
            <div class="info-box">
                If an account with that email exists, you should receive a password reset link shortly. Check your email and spam folder.
            </div>
            
            <div class="btn-link">
                <a href="index.php">Back to Login</a>
            </div>
        <?php endif; ?>
        
        <?php if ($step == 3 && $user_id): ?>
            <div class="info-box">
                <strong>Password Requirements:</strong>
                <ul style="margin-left: 1rem; margin-top: 0.5rem;">
                    <li>At least 8 characters</li>
                    <li>At least one uppercase letter (A-Z)</li>
                    <li>At least one number (0-9)</li>
                </ul>
            </div>
            <form method="POST">
                <input type="hidden" name="reset_token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
        
        <?php if ($step == 4): ?>
            <div class="success-message">
                <strong>✓ Success!</strong><br>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <div class="btn-link">
                <a href="index.php">Go to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
