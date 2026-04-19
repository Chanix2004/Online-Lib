<?php
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/book.php';
require_once 'includes/security.php';

$auth = new Auth($conn);
$security = new SecurityHelper($conn);

$book = new Book($conn);
$featured_books = $conn->query("SELECT b.*, c.category_name FROM books b LEFT JOIN categories c ON b.category_id = c.category_id ORDER BY b.created_at DESC LIMIT 8");

$error = '';
$warning = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required!';
    } else {
        // Check if account is locked due to too many failed attempts
        if ($security->isAccountLocked($email)) {
            $remaining = $security->getLockoutTimeRemaining($email);
            $minutes = ceil($remaining / 60);
            $error = "Too many failed login attempts. Please try again in $minutes minute(s).";
        } else {
            // Attempt login
            $user_data = $auth->login($email, $password);
            
            if ($user_data) {
                // Login successful - email is verified
                $security->recordLoginAttempt($email, $user_data['user_id'], true);
                header('Location: pages/dashboard.php');
                exit;
            } else {
                // Check if user exists but password wrong or email not verified
                $check_sql = "SELECT user_id, is_verified FROM users WHERE email = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result()->fetch_assoc();
                $check_stmt->close();
                
                if ($check_result) {
                    $user_id = $check_result['user_id'];
                    if (!$check_result['is_verified']) {
                        // Email not verified
                        $security->recordLoginAttempt($email, $user_id, false);
                        $error = 'Please verify your email address before logging in. Check your email for the verification link.';
                    } else {
                        // User exists and verified but password wrong
                        $security->recordLoginAttempt($email, $user_id, false);
                        $error = 'Invalid password!';
                    }
                } else {
                    // User doesn't exist
                    $error = 'No account found with this email address!';
                }
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
    <title><?php echo SITE_NAME; ?> - Login</title>
    <link rel="stylesheet" href="css/dark-modern.css">
    <style>
        .index-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-tertiary) 100%);
        }

        .login-section {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            z-index: 1;
        }

        .featured-section {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border-left: 1px solid var(--border-color);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .featured-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0, 212, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }

        .featured-section::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 0, 110, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }

        .featured-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .featured-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .featured-title {
            font-size: 1.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .featured-subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 1rem;
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .featured-grid::-webkit-scrollbar {
            width: 6px;
        }

        .featured-grid::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        .featured-grid::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 3px;
        }

        .featured-book {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            height: 180px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-secondary) 100%);
            border: 1px solid rgba(0, 212, 255, 0.1);
        }

        .featured-book:hover {
            transform: translateY(-8px);
            border-color: var(--accent-primary);
            box-shadow: 0 12px 24px rgba(0, 212, 255, 0.3);
        }

        .featured-book img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .featured-book:hover img {
            transform: scale(1.05);
        }

        .featured-book-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
            display: flex;
            align-items: flex-end;
            padding: 0.75rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .featured-book:hover .featured-book-overlay {
            opacity: 1;
        }

        .featured-book-title {
            font-size: 0.75rem;
            color: var(--text-primary);
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .browse-link-section {
            margin-top: 1.5rem;
            text-align: center;
        }

        .browse-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-tertiary) 100%);
            color: var(--bg-primary);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .browse-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.3);
        }

        @media (max-width: 1024px) {
            .index-wrapper {
                grid-template-columns: 1fr;
            }

            .featured-section {
                border-left: none;
                border-top: 1px solid var(--border-color);
                padding: 1.5rem;
                min-height: 300px;
            }

            .featured-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }

            .login-container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="index-wrapper">
        <!-- Login Section -->
        <div class="login-section">
            <div class="login-box">
                <div class="login-header">
                    <span class="login-icon">📚</span>
                    <h1><?php echo SITE_TITLE; ?></h1>
                    <p>Library Management System</p>
                </div>

                <form method="POST">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            ⚠️ <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Sign In</button>

                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="register.php" style="color: #3b82f6; text-decoration: none; font-weight: 600; margin-right: 1.5rem;">Create Account</a>
                        <a href="forgot-password.php" style="color: #6b7280; text-decoration: none; font-weight: 600;">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Featured Books Section -->
        <div class="featured-section">
            <div class="featured-content">
                <div class="featured-header">
                    <div class="featured-title">Latest Releases</div>
                    <div class="featured-subtitle">Discover new additions to our collection</div>
                </div>

                <div class="featured-grid">
                    <?php if ($featured_books && $featured_books->num_rows > 0): ?>
                        <?php while ($book = $featured_books->fetch_assoc()): ?>
                            <a href="pages/book-detail.php?id=<?php echo $book['book_id']; ?>" style="text-decoration: none; color: inherit;">
                                <div class="featured-book">
                                    <?php if ($book['book_cover']): ?>
                                        <img src="<?php echo SITE_URL . BOOK_COVER_PATH . htmlspecialchars($book['book_cover']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">📖</div>
                                    <?php endif; ?>
                                    <div class="featured-book-overlay">
                                        <div class="featured-book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>

                <div class="browse-link-section">
                    <a href="browse.php" class="browse-link">Browse Full Library →</a>
                </div>
            </div>
        </div>
    </div>

    <script src="js/interactions.js"></script>
</body>
</html>

