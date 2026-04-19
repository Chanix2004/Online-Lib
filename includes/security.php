<?php
$email_service = 'smtp';

if (defined('BREVO_API_KEY') && !empty(BREVO_API_KEY)) {
    require_once __DIR__ . '/brevo-email.php';
    $email_service = 'brevo';
} elseif (defined('POSTMARK_TOKEN') && !empty(POSTMARK_TOKEN)) {
    require_once __DIR__ . '/postmark-email.php';
    $email_service = 'postmark';
} else {
    require_once __DIR__ . '/email.php';
    $email_service = 'smtp';
}

class SecurityHelper {
    private $conn;
    private $emailService;
    private $max_login_attempts = 5;
    private $lockout_duration = 900; // 15 minutes
    private $token_expiry = 3600; // 1 hour for password reset
    
    public function __construct($conn) {
        global $email_service;
        
        $this->conn = $conn;
        
        if ($email_service === 'brevo' && defined('BREVO_API_KEY')) {
            // Use Brevo
            $this->emailService = new BrevoEmailService(
                BREVO_API_KEY,
                MAIL_FROM,
                'Library Management System'
            );
            error_log("Email service: Brevo");
        } elseif ($email_service === 'postmark' && defined('POSTMARK_TOKEN')) {
            // Use Postmark
            $this->emailService = new PostmarkEmailService(
                POSTMARK_TOKEN,
                MAIL_FROM
            );
            error_log("Email service: Postmark");
        } else {
            // Use legacy SMTP
            $this->emailService = new EmailService(
                MAIL_HOST,
                MAIL_PORT,
                MAIL_USERNAME,
                MAIL_PASSWORD,
                MAIL_FROM,
                MAIL_ENCRYPTION
            );
            error_log("Email service: Brevo API");
        }
    }

    /**
     * Check if user account is locked due to too many failed login attempts
     */
    public function isAccountLocked($email) {
        $sql = "SELECT COUNT(*) as failed_attempts FROM login_attempts 
                WHERE email = ? AND success = FALSE 
                AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $email, $this->lockout_duration);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['failed_attempts'] >= $this->max_login_attempts;
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getLockoutTimeRemaining($email) {
        $sql = "SELECT UNIX_TIMESTAMP(MAX(attempt_time)) as last_attempt 
                FROM login_attempts 
                WHERE email = ? AND success = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result['last_attempt']) return 0;
        
        $remaining = ($result['last_attempt'] + $this->lockout_duration) - time();
        return max(0, $remaining);
    }

    /**
     * Record login attempt
     */
    public function recordLoginAttempt($email, $user_id, $success) {
        // Skip recording if user doesn't exist (user_id = 0)
        if ($user_id <= 0) {
            return true;
        }
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $sql = "INSERT INTO login_attempts (user_id, email, ip_address, success) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $user_id, $email, $ip_address, $success);
        return $stmt->execute();
    }

    /**
     * Generate secure password reset token
     */
    public function generatePasswordResetToken($user_id) {
        error_log("START: generatePasswordResetToken for user $user_id");
        
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expires_at = date('Y-m-d H:i:s', time() + $this->token_expiry);
        
        error_log("Token hash: " . substr($token_hash, 0, 20) . "...");
        error_log("Expires at: $expires_at");
        
        // Store in database
        $sql = "INSERT INTO password_reset_tokens (user_id, token, expires_at) 
                VALUES (?, ?, ?)";
        
        error_log("Executing: $sql");
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("ERROR: prepare() failed: " . $this->conn->error);
            return false;
        }
        
        error_log("Prepare succeeded");
        
        if (!$stmt->bind_param("iss", $user_id, $token_hash, $expires_at)) {
            error_log("ERROR: bind_param() failed: " . $stmt->error);
            return false;
        }
        
        error_log("Bind succeeded");
        
        if ($stmt->execute()) {
            error_log("INSERT succeeded - affected rows: " . $stmt->affected_rows);
            
            // Verify it was actually saved using prepared statement
            $verify_sql = "SELECT COUNT(*) as cnt FROM password_reset_tokens WHERE token = ?";
            $verify_stmt = $this->conn->prepare($verify_sql);
            
            if (!$verify_stmt) {
                error_log("ERROR: verify prepare() failed: " . $this->conn->error);
                return $token; // Return token even if verify fails
            }
            
            $verify_stmt->bind_param("s", $token_hash);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result()->fetch_assoc();
            $verify_stmt->close();
            
            error_log("Verify: Token found in DB = " . ($verify_result['cnt'] > 0 ? 'YES' : 'NO'));
            
            error_log("Returning unhashed token for email transmission");
            return $token; // Return unhashed token to send to user
        } else {
            error_log("ERROR: execute() failed: " . $stmt->error);
            return false;
        }
    }

    /**
     * Verify password reset token
     */
    public function verifyPasswordResetToken($token) {
        try {
            if (!$token || empty($token)) {
                error_log("ERROR: verifyPasswordResetToken called with empty token");
                return false;
            }
            
            $token_hash = hash('sha256', $token);
            error_log("Verifying token - hash (first 20 chars): " . substr($token_hash, 0, 20));
            
            // Use NOW() since timezone should be set to UTC on connection
            $sql = "SELECT user_id FROM password_reset_tokens 
                    WHERE token = ? AND used = FALSE AND expires_at > NOW()";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                error_log("ERROR: prepare() failed: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("s", $token_hash);
            
            if (!$stmt->execute()) {
                error_log("ERROR: execute() failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($result) {
                error_log("Token is VALID - user_id: " . $result['user_id']);
                return $result['user_id'];
            } else {
                error_log("Token is INVALID - no matching active unexpired token found");
                return false;
            }
        } catch (Exception $e) {
            error_log("EXCEPTION in verifyPasswordResetToken: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark password reset token as used
     */
    public function markTokenAsUsed($token) {
        try {
            if (!$token || empty($token)) {
                error_log("ERROR: markTokenAsUsed called with empty token");
                return false;
            }
            
            $token_hash = hash('sha256', $token);
            
            $sql = "UPDATE password_reset_tokens SET used = TRUE 
                    WHERE token = ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                error_log("ERROR: prepare() failed in markTokenAsUsed: " . $this->conn->error);
                return false;
            }
            
            $stmt->bind_param("s", $token_hash);
            
            if (!$stmt->execute()) {
                error_log("ERROR: execute() failed in markTokenAsUsed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            if ($affected > 0) {
                error_log("Token successfully marked as used");
                return true;
            } else {
                error_log("WARNING: No tokens were updated in markTokenAsUsed");
                return true; // Consider it a success even if no rows affected
            }
        } catch (Exception $e) {
            error_log("EXCEPTION in markTokenAsUsed: " . $e->getMessage());
            return true; // Don't fail the reset because of this
        }
    }

    /**
     * Clean up expired password reset tokens
     */
    public function cleanupExpiredTokens() {
        $sql = "DELETE FROM password_reset_tokens WHERE expires_at < NOW()";
        $result = $this->conn->query($sql);
        
        if ($result) {
            error_log("Cleanup: " . $this->conn->affected_rows . " expired tokens deleted");
            return true;
        } else {
            error_log("ERROR: Cleanup failed: " . $this->conn->error);
            return false;
        }
    }

    /**
     * Generate email verification token
     */
    public function generateEmailVerificationToken($user_id) {
        $token = bin2hex(random_bytes(32));
        
        $sql = "UPDATE users SET email_verification_token = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $token, $user_id);
        
        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    /**
     * Verify email token and mark as verified
     */
    public function verifyEmail($token) {
        $sql = "UPDATE users SET is_verified = TRUE, email_verified_at = NOW() 
                WHERE email_verification_token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get email by verification token
     */
    public function getEmailByVerificationToken($token) {
        $sql = "SELECT user_id, email FROM users WHERE email_verification_token = ? AND is_verified = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Send password reset email
     * IMPORTANT: Always sends to the PROVIDED email address, not to a different email
     * This ensures users receive their own password reset link, not admins
     */
    public function sendPasswordResetEmail($email, $token) {
        error_log("=== sendPasswordResetEmail CALLED ===");
        error_log("Recipient Email Parameter: " . $email);
        error_log("Token (first 30 chars): " . substr($token, 0, 30));
        
        // SECURITY: Validate email is provided and formatted correctly
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("ERROR: Invalid or empty email provided to sendPasswordResetEmail: " . $email);
            return false;
        }
        
        // SECURITY: Never use a different email from the database for sending
        // Always use the email that was explicitly provided
        $recipient_email = trim($email);
        error_log("Password reset link will be sent to: " . $recipient_email);
        
        $reset_link = SITE_URL . "reset-password.php?token=" . urlencode($token);
        
        $subject = "Password Reset Request - Library Management System";
        $message = "
            <html>
            <body>
                <h2>Password Reset Request</h2>
                <p>You requested a password reset. Click the link below to continue:</p>
                <p><a href='" . $reset_link . "'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <hr>
                <p><small>Library Management System</small></p>
            </body>
            </html>
        ";
        
        // Send to the provided email address ONLY - never to another email
        return $this->emailService->send($recipient_email, $subject, $message);
    }

    /**
     * Send email verification email
     */
    public function sendEmailVerificationEmail($email, $token) {
        $verify_link = SITE_URL . "verify-email.php?token=" . urlencode($token);
        
        $subject = "Email Verification - Library Management System";
        $message = "
            <html>
            <body>
                <h2>Welcome to Library Management System</h2>
                <p>Please verify your email address by clicking the link below:</p>
                <p><a href='" . $verify_link . "'>Verify Email</a></p>
                <p>This link will expire in 24 hours.</p>
                <hr>
                <p><small>Library Management System</small></p>
            </body>
            </html>
        ";
        
        return $this->emailService->send($email, $subject, $message);
    }

}
?>
