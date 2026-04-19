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
    header('Location: ' . SITE_URL);
    exit;
}

$member = new Member($conn);
$page_title = 'Members Management';
$page = max(1, $_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM users WHERE role = 'member'";
if ($search) {
    $sql .= " AND (full_name LIKE '%" . $conn->real_escape_string($search) . "%' OR email LIKE '%" . $conn->real_escape_string($search) . "%' OR membership_number LIKE '%" . $conn->real_escape_string($search) . "%')";
}
if ($status) {
    $sql .= " AND membership_status = '" . $conn->real_escape_string($status) . "'";
}
$sql .= " ORDER BY full_name ASC LIMIT " . $limit . " OFFSET " . $offset;

$result = $conn->query($sql);
?>
<?php $current_page = 'members.php'; require_once '../includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">👥 Member Management</h1>
    <p class="page-subtitle">Manage library members and their account status</p>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 2rem;">
    <form method="GET" class="grid grid-3" style="gap: 1rem; margin: 0;">
        <div class="form-group" style="margin: 0;">
            <input type="text" name="search" class="form-control" placeholder="Search members...">
        </div>
        <div class="form-group" style="margin: 0;">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="suspended" <?php echo $status == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">🔍 Search</button>
    </form>
</div>

<!-- Members Table -->
<div class="card">
    <div class="card-header">Members List</div>
    <div class="card-body">
        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Join Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['membership_number'] ?? $row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['membership_status'] == 'active' ? 'success' : ($row['membership_status'] == 'suspended' ? 'danger' : 'secondary'); ?>">
                                    <?php echo ucfirst($row['membership_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($row['membership_date'])); ?></td>
                            <td>
                                <a href="member-detail.php?id=<?php echo $row['user_id']; ?>&t=<?php echo time(); ?>" class="btn btn-sm btn-primary">View</a>
                                <a href="edit-member.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #7f8c8d; padding: 2rem 0;">No members found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

