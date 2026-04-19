<?php

header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

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

$page_title = 'Member Details: ' . $user['full_name'];
?>
<?php $current_page = 'member-detail.php'; require_once '../includes/header.php'; ?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="page-title">👤 <?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p class="page-subtitle">Member Details & Activity</p>
        </div>
        <a href="members.php" class="btn btn-secondary" style="width: auto;">← Back to Members</a>
    </div>
</div>

<div class="grid grid-2">
    <!-- Member Information -->
    <div class="card">
        <div class="card-header">Member Information</div>
        <div class="card-body">
            <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; overflow: hidden; background-size: cover; background-position: center;<?php if ($user['profile_picture']): ?> background-image: url('<?php echo SITE_URL . PROFILE_PIC_PATH . htmlspecialchars($user['profile_picture']); ?>')<?php endif; ?>">
                        <?php if (!$user['profile_picture']): echo strtoupper(substr($user['full_name'], 0, 1)); endif; ?>
                    </div>
                </div>
                <div style="flex: 1;">
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-weight: bold; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">EMAIL</div>
                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-weight: bold; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">PHONE</div>
                        <div><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></div>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-weight: bold; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">STATUS</div>
                        <span class="badge badge-<?php echo $user['membership_status'] == 'active' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($user['membership_status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border); padding-top: 1.5rem;">
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-weight: bold; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">MEMBERSHIP NUMBER</div>
                    <div><?php echo htmlspecialchars($user['membership_number']); ?></div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-weight: bold; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">MEMBER SINCE</div>
                    <div><?php echo date('F d, Y', strtotime($user['membership_date'])); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Summary -->
    <div class="card">
        <div class="card-header">Activity Summary</div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div>
                    <div style="text-align: center;">
                        <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Member Status</div>
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border); margin-top: 1.5rem; padding-top: 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <div style="font-weight: bold; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem;">LAST LOGIN</div>
                    <div><?php echo $user['last_login'] ? date('F d, Y H:i', strtotime($user['last_login'])) : 'Never logged in'; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

