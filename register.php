<?php
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/security.php';

$auth = new Auth($conn);
$security = new SecurityHelper($conn);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $country = $_POST['country'] ?? '';

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Full name, email, and password are required!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters!';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one uppercase letter and one number!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address!';
    } else {

        $check_sql = "SELECT user_id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered! Please login or use a different email.';
        } else {

            if ($auth->register($full_name, $email, $phone, $password, $date_of_birth, $address, $city, $state, $postal_code, $country)) {
                // Get the newly registered user ID
                $user_sql = "SELECT user_id FROM users WHERE email = ?";
                $user_stmt = $conn->prepare($user_sql);
                $user_stmt->bind_param("s", $email);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result()->fetch_assoc();
                
                if ($user_result) {
                    // Generate and send email verification
                    $token = $security->generateEmailVerificationToken($user_result['user_id']);
                    if ($token && $security->sendEmailVerificationEmail($email, $token)) {
                        $success = 'Registration successful! Please check your email to verify your address before logging in.';
                    } else {
                        $success = 'Registration successful! However, verification email could not be sent. Please contact support.';
                    }
                }
            } else {
                $error = 'Error during registration. Please try again.';
            }
        }
    }
}

if ($auth->isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Register</title>
    <link rel="stylesheet" href="css/dark-modern.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <span class="login-icon">📚</span>
                <h1><?php echo SITE_TITLE; ?></h1>
                <p>Create Account</p>
            </div>

            <form method="POST">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        ⚠️ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        ✅ <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" placeholder="John Doe" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="your@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 characters, 1 uppercase, 1 number" required>
                    <small style="color: #999; margin-top: 0.25rem; display: block;">
                        Requirements: At least 8 characters, one uppercase letter, and one number
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Account</button>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="index.php" style="color: #3b82f6; text-decoration: none; font-weight: 600; margin-right: 1.5rem;">← Back to Login</a>
                    <a href="index.php" style="color: #6b7280; text-decoration: none; font-weight: 600;">Already have an account?</a>
                </div>
            </form>
        </div>
    </div>
    <script src="js/interactions.js"></script>
</body>
</html>

