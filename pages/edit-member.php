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

if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'librarian') {
    header('Location: ' . SITE_URL . 'pages/dashboard.php');
    exit;
}

$member_id = $_GET['id'] ?? 0;
if (empty($member_id) || !is_numeric($member_id)) {
    header('Location: ' . SITE_URL . 'pages/members.php');
    exit;
}

$member = new Member($conn);
$user = $member->getMemberById($member_id);

if (!$user) {
    header('Location: ' . SITE_URL . 'pages/members.php');
    exit;
}

$error = '';
$success = '';

if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $status = trim($_POST['membership_status'] ?? 'active');

    if (empty($full_name)) {
        $error = 'Full name is required';
    } else {
        if ($member->updateMemberProfile($member_id, $full_name, $phone, $address, $city, $state, $postal_code, $country)) {

            if ($_SESSION['role'] == 'admin') {
                $sql = "UPDATE users SET membership_status = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $status, $member_id);
                $stmt->execute();
            }
            
            $success = 'Member updated successfully!';

            $user = $member->getMemberById($member_id);
        } else {
            $error = 'Failed to update member. Please try again.';
        }
    }
}

$page_title = 'Edit Member: ' . $user['full_name'];
?>
<?php $current_page = 'edit-member.php'; require_once '../includes/header.php'; ?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">✏️ Edit Member</h1>
            <p class="page-subtitle"><?php echo htmlspecialchars($user['full_name']); ?></p>
        </div>
        <a href="member-detail.php?id=<?php echo $member_id; ?>" class="btn btn-secondary" style="width: auto;">← Back</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success" role="alert">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Member Information</div>
    <div class="card-body">
        <form method="POST" class="grid grid-2" style="gap: 2rem;">
            <input type="hidden" name="action" value="update">
            
            <div class="form-group">
                <label for="full_name">Full Name <span style="color: red;">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-control" 
                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                <small style="color: var(--text-secondary); margin-top: 0.5rem;">Email cannot be changed</small>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-control" 
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" class="form-control" 
                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" class="form-control" 
                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state" class="form-control" 
                       value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control" 
                       value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" class="form-control" 
                       value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
            </div>

            <?php if ($_SESSION['role'] == 'admin'): ?>
            <div class="form-group">
                <label for="membership_status">Membership Status</label>
                <select id="membership_status" name="membership_status" class="form-control">
                    <option value="active" <?php echo $user['membership_status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $user['membership_status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="suspended" <?php echo $user['membership_status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
            <?php endif; ?>

            <div style="grid-column: 1 / -1; display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">💾 Update Member</button>
                <a href="member-detail.php?id=<?php echo $member_id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

