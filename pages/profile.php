<?php
require_once '../config/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/member.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$success = null;
$error = null;

// Handle session success message from redirect
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $member = new Member($conn);

    // Handle profile form submission
    if (isset($_POST['profile_form'])) {
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $state = $_POST['state'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $country = $_POST['country'] ?? '';

        if ($member->updateMemberProfile($user_id, $full_name, $phone, $address, $city, $state, $postal_code, $country)) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['success_message'] = 'Profile updated successfully!';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $error = 'Error updating profile!';
        }
    }
    // Handle password form submission
    elseif (isset($_POST['password_form'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password)) {
            $error = 'Current password is required!';
        } elseif (empty($new_password)) {
            $error = 'New password is required!';
        } elseif (empty($confirm_password)) {
            $error = 'Please confirm your new password!';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match!';
        } else {
            // Get current user password from database
            $sql = "SELECT password_hash FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (!$result) {
                $error = 'User not found!';
            } else {
                $stored_password = $result['password_hash'];
                
                // Verify current password
                if (!password_verify($current_password, $stored_password)) {
                    $error = 'Current password is incorrect!';
                } elseif ($current_password === $new_password) {
                    $error = 'New password cannot be the same as current password!';
                } else {
                    // Hash and update the new password
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $update_sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $_SESSION['success_message'] = 'Password changed successfully!';
                        header('Location: ' . $_SERVER['REQUEST_URI']);
                        exit;
                    } else {
                        $error = 'Error changing password!';
                    }
                }
            }
        }
    }
    // Handle profile picture form submission
    elseif (isset($_POST['profile_picture_form'])) {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
                $upload_errors = array(
                    0 => 'File uploaded successfully',
                    1 => 'File is too large (max 5MB)',
                    2 => 'File is too large (max 5MB)',
                    3 => 'File upload was cancelled',
                    4 => 'No file was uploaded',
                    6 => 'Missing upload directory',
                    7 => 'Failed to write file to disk',
                    8 => 'Upload stopped by extension'
                );
                $error = $upload_errors[$_FILES['profile_picture']['error']] ?? 'Unknown error';
            } elseif ($_FILES['profile_picture']['size'] == 0) {
                $error = 'No file selected!';
            } else {
                if ($member->uploadProfilePicture($user_id, $_FILES['profile_picture'])) {
                    $_SESSION['success_message'] = 'Profile picture updated successfully!';
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    $error = 'Error uploading profile picture! Please check file format and size.';
                }
            }
        } else {
            $error = 'Please select a file to upload!';
        }
    }
}

// Load user data
$member = new Member($conn);
$user_id = $_SESSION['user_id'];

if ($_SESSION['role'] == 'member') {
    $user = $member->getMemberById($user_id);
} else {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

$page_title = 'Profile';
?>
<?php $current_page = 'profile.php'; require_once '../includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">👤 My Profile</h1>
    <p class="page-subtitle">Manage your account and personal information</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom: 2rem; padding: 1rem; background: rgba(76, 175, 80, 0.1); border: 1px solid rgba(76, 175, 80, 0.3); border-radius: 8px; color: #4caf50;"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger" style="margin-bottom: 2rem; padding: 1rem; background: rgba(244, 67, 54, 0.1); border: 1px solid rgba(244, 67, 54, 0.3); border-radius: 8px; color: #f44336;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="grid grid-2">
    <!-- Profile Information Card -->
    <div class="card">
        <div class="card-header">Personal Information</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="profile_form" value="1">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <!-- Profile Picture Card -->
    <div class="card">
        <div class="card-header">Profile Picture</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="profile_picture_form" value="1">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <?php 
                    $pic_src = '';
                    if ($user['profile_picture']) {
                        $pic_filename = htmlspecialchars($user['profile_picture']);
                        $pic_src = SITE_URL . 'uploads/profile_pics/' . $pic_filename;
                    }
                    ?>
                    <?php if ($pic_src): ?>
                        <img src="<?php echo $pic_src; ?>" alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto; border: 3px solid var(--accent-primary);" onerror="this.style.display='none'; document.getElementById('default-avatar').style.display='flex';">
                    <?php endif; ?>
                    <div id="default-avatar" style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); display: <?php echo !$pic_src ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; color: white; font-size: 3rem; margin: 0 auto; border: 3px solid var(--accent-primary); font-weight: bold;">
                        <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload New Picture</label>
                    <input type="file" name="profile_picture" class="form-control" accept="image/*" required style="border: 2px solid rgba(0, 212, 255, 0.3); transition: all 0.2s ease;">
                    <small style="display: block; margin-top: 0.5rem; color: #b0b0b0;">Allowed: JPG, PNG, GIF (Max 5MB)</small>
                </div>

                <button type="submit" class="btn btn-primary">📤 Upload Picture</button>
            </form>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="card">
        <div class="card-header">🔒 Change Password</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="password_form" value="1">
                
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Enter your current password" required>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                </div>

                <div style="background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem; color: #64d8ff;">
                    <strong>ℹ️ Password Requirements:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        <li>At least 6 characters</li>
                        <li>Cannot be the same as current password</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary">🔄 Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

