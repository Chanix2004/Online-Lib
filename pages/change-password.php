<?php
require_once '../config/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required!';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match!';
    } else {

        $sql = "SELECT password_hash FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!password_verify($current_password, $user['password_hash'])) {
            $error = 'Current password is incorrect!';
        } else {

            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_sql = "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_stmt->execute()) {
                $success = 'Password changed successfully!';

                $current_password = '';
                $new_password = '';
                $confirm_password = '';
            } else {
                $error = 'Error changing password. Please try again.';
            }
        }
    }
}

$page_title = 'Change Password';
?>
<?php $current_page = 'change-password.php'; require_once '../includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">🔐 Change Password</h1>
    <p class="page-subtitle">Update your account password</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <strong>✅ Success</strong><br>
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <strong>⚠️ Error</strong><br>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <div class="card-header">Update Your Password</div>
    <div class="card-body" style="padding: 1.5rem;">
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" placeholder="Enter your current password" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter your new password" required>
                <small style="color: var(--text-gray); display: block; margin-top: 0.5rem;">Minimum 6 characters</small>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your new password" required>
            </div>

            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <a href="profile.php" style="color: var(--accent); text-decoration: none; font-weight: 500;">← Back to Profile</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

