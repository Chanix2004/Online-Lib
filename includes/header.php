<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/dark-modern.css">
    <script>
        function closeSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            sidebar.classList.remove('active');
            sidebar.style.transform = 'translateX(-100%)';
            body.classList.remove('sidebar-open');
        }
        
        function changeMenuDisplay(state) {
            const menu = document.getElementById('userMenu');
            menu.style.display = state;
        }
        
        function toggleMenu(event) {
            event.stopPropagation();
            event.preventDefault();
            
            // Toggle menu WITHOUT closing sidebar
            const menu = document.getElementById('userMenu');
            const isVisible = menu.style.display === 'block';
            changeMenuDisplay(isVisible ? 'none' : 'block');
        }
        
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            
            // Toggle sidebar WITHOUT closing menu
            if (sidebar.classList.contains('active')) {
                closeSidebar();
            } else {
                sidebar.classList.add('active');
                sidebar.style.transform = 'translateX(0)';
                document.body.classList.add('sidebar-open');
            }
        }
        
        // Close menus when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menu = document.getElementById('userMenu');
            const toggleBtn = document.getElementById('sidebarToggle');
            const userInfo = document.querySelector('.user-info');
            
            const clickedOnToggle = toggleBtn && toggleBtn.contains(event.target);
            const clickedOnUserInfo = userInfo && userInfo.contains(event.target);
            const clickedOnSidebar = sidebar && sidebar.contains(event.target);
            const clickedOnMenu = menu && menu.contains(event.target);
            
            // If clicked outside everything, close all
            if (!clickedOnToggle && !clickedOnUserInfo && !clickedOnSidebar && !clickedOnMenu) {
                closeSidebar();
                changeMenuDisplay('none');
            }
        });
        
        window.addEventListener('scroll', function() {
            const sidebar = document.querySelector('.sidebar');
            const navbar = document.querySelector('.navbar');
            
            if (sidebar && navbar) {
                sidebar.style.position = 'fixed';
                navbar.style.position = 'fixed';
            }
        }, { passive: true });
    </script>
    <script src="<?php echo SITE_URL; ?>js/interactions.js"></script>
    <script src="<?php echo SITE_URL; ?>js/custom-select.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <button id="sidebarToggle" class="navbar-toggle" onclick="toggleSidebar()" style="background: none; border: none; color: var(--accent-primary); font-size: 1.5rem; cursor: pointer; padding: 0.5rem;">☰</button>
            <a href="<?php echo SITE_URL; ?>" class="navbar-brand">📚 <?php echo SITE_TITLE; ?></a>
        </div>
        
        <div class="navbar-right">
            <div class="user-info" onclick="toggleMenu(event)">
                <?php 

                $logged_in_user_id = $_SESSION['user_id'];
                $logged_in_user_query = $conn->prepare("SELECT profile_picture, full_name FROM users WHERE user_id = ?");
                $logged_in_user_query->bind_param("i", $logged_in_user_id);
                $logged_in_user_query->execute();
                $logged_in_user = $logged_in_user_query->get_result()->fetch_assoc();
                $logged_in_user_query->close();
                $profile_pic = $logged_in_user['profile_picture'] ?? '';
                $full_name = $logged_in_user['full_name'] ?? $_SESSION['full_name'];
                ?>
                <div class="user-avatar" style="<?php echo $profile_pic ? 'background-image: url(' . SITE_URL . 'uploads/profile_pics/' . htmlspecialchars($profile_pic) . '); background-size: cover; background-position: center; background-color: transparent;' : ''; ?>">
                    <?php if (!$profile_pic): echo strtoupper(substr($full_name, 0, 1)); endif; ?>
                </div>
                <span class="user-name"><?php echo $_SESSION['full_name']; ?></span>
                <span style="font-size: 0.7rem;">▼</span>
            </div>
            <div id="userMenu" style="position: absolute; top: 70px; right: 2rem; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: var(--shadow); min-width: 200px; display: none; z-index: 1000; overflow: hidden; animation: slideDown 0.3s ease;">
                <a href="<?php echo SITE_URL; ?>pages/profile.php" style="display: block; padding: 0.875rem 1.25rem; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color); transition: all 0.3s; font-weight: 500;">👤 Profile</a>
                <a href="<?php echo SITE_URL; ?>api/logout.php" style="display: block; padding: 0.875rem 1.25rem; color: var(--danger); text-decoration: none; transition: all 0.3s; font-weight: 500;">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="<?php echo SITE_URL; ?>pages/dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>📊 Dashboard</a></li>
            
            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'librarian'): ?>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Admin</div>
                </div>
                <li><a href="<?php echo SITE_URL; ?>pages/books.php" <?php echo $current_page == 'books.php' ? 'class="active"' : ''; ?>>📖 Books</a></li>
                <li><a href="<?php echo SITE_URL; ?>pages/members.php" <?php echo $current_page == 'members.php' ? 'class="active"' : ''; ?>>👥 Members</a></li>
            <?php else: ?>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Library</div>
                </div>
                <li><a href="<?php echo SITE_URL; ?>pages/books.php" <?php echo $current_page == 'books.php' ? 'class="active"' : ''; ?>>🔍 Browse</a></li>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">

