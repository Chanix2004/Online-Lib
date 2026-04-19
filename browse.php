<?php
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/book.php';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die('Database connection failed. Please try again later.');
}

$auth = new Auth($conn);
$is_logged_in = $auth->isLoggedIn();

$book = new Book($conn);
$page_title = 'Browse Books';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Get total count of books matching search/filter (without limit/offset)
$count_sql = "SELECT COUNT(*) as total FROM books b WHERE 1=1";
$count_params = [];
$count_types = '';

if (!empty($search)) {
    $search_term = "%$search%";
    $count_sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.description LIKE ?)";
    $count_params = [$search_term, $search_term, $search_term, $search_term];
    $count_types = 'ssss';
}

if (!empty($category)) {
    $cat_id = (int)$category;
    $count_sql .= " AND b.category_id = ?";
    $count_params[] = $cat_id;
    $count_types .= 'i';
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_books = $count_result['total'];

$result = $book->searchAndFilterBooks($search, !empty($category) ? (int)$category : null, $limit, $offset);

if (!$result) {
    die('Query failed. Please try again later.');
}

$cat_query = $conn->query("SELECT * FROM categories ORDER BY category_name");
if (!$cat_query) {
    die('Failed to load categories.');
}

$categories = [];
while ($cat = $cat_query->fetch_assoc()) {
    $categories[] = $cat;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Library System</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/unified.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
            border-bottom: 2px solid var(--border-color);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: #d0d0d0;
            margin: 0;
        }

        .login-prompt {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #ffffff;
            font-weight: 500;
        }

        .login-prompt a {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .login-prompt a:hover {
            color: var(--accent-secondary);
            text-decoration: underline;
        }

        .search-card {
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.05) 0%, rgba(255, 0, 110, 0.05) 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin: 0 !important;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: #ffffff;
            padding: 0.75rem;
            border-radius: 6px;
            font-size: 0.95rem;
            accent-color: var(--accent-primary);
        }

        .form-control option {
            background: #2a2a4a;
            color: #ffffff;
            padding: 0.5rem;
        }

        .form-control::placeholder {
            color: #a0a0a0;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent-primary);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 212, 255, 0.3);
        }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 2rem;
        }

        .book-card {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            box-shadow: var(--shadow-md);
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .book-cover {
            height: 280px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .book-cover-placeholder {
            font-size: 5rem;
            color: var(--text-secondary);
        }

        .book-info {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .book-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            line-height: 1.3;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-author {
            color: #64d8ff;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-category {
            display: inline-block;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.3);
            color: #64d8ff;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
            width: fit-content;
        }

        .book-description {
            color: #b0b0b0;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: auto;
        }

        .btn-action {
            flex: 1;
            padding: 0.6rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-view {
            background: rgba(0, 212, 255, 0.15);
            color: var(--accent-primary);
            border: 1px solid rgba(0, 212, 255, 0.3);
        }

        .btn-view:hover {
            background: rgba(0, 212, 255, 0.25);
            border-color: var(--accent-primary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
            border: none;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
        }

        .btn-disabled {
            background: rgba(128, 128, 128, 0.15);
            color: var(--text-secondary);
            border: 1px solid rgba(128, 128, 128, 0.3);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-state-text {
            color: #c0c0c0;
            font-size: 1.1rem;
        }

        .pagination-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .pagination-buttons a.btn-action {
            flex: none;
            width: auto;
            padding: 0.6rem 1.5rem;
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .grid-4 {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">📚 Browse Books</h1>
            <p class="page-subtitle">Explore our extensive library collection</p>
        </div>

        <?php if (!$is_logged_in): ?>
            <div class="login-prompt">
                <strong>ℹ️ You're browsing as a guest.</strong> 
                <a href="<?php echo SITE_URL; ?>index.php">Login</a> or 
                <a href="<?php echo SITE_URL; ?>register.php">Register</a> to read books and borrow from the library.
            </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="search-card">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by title, author, or ISBN..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($category == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">🔍 Search</button>
                </div>
            </form>
        </div>

        <?php if ($total_books > 0): ?>
            <div class="grid-4">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="book-card">
                        <!-- Book Cover -->
                        <div class="book-cover">
                            <?php if ($row['book_cover']): ?>
                                <img src="<?php echo SITE_URL . BOOK_COVER_PATH . htmlspecialchars($row['book_cover']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <?php else: ?>
                                <div class="book-cover-placeholder">📖</div>
                            <?php endif; ?>
                        </div>

                        <!-- Book Info -->
                        <div class="book-info">
                            <!-- Title -->
                            <div class="book-title">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </div>

                            <!-- Author -->
                            <div class="book-author">
                                <?php echo htmlspecialchars(substr($row['author'], 0, 40)); ?>
                            </div>

                            <!-- Category Badge -->
                            <?php if ($row['category_name']): ?>
                                <div class="book-category">
                                    <?php echo htmlspecialchars($row['category_name']); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Description -->
                            <div class="book-description">
                                <?php echo htmlspecialchars(substr($row['description'] ?? 'No description available.', 0, 150)); ?>
                            </div>

                            <!-- Actions -->
                            <div class="book-actions">
                                <?php if ($is_logged_in): ?>
                                    <a href="<?php echo SITE_URL; ?>pages/book-detail.php?id=<?php echo $row['book_id']; ?>" class="btn-action btn-view">📖 View Details</a>
                                <?php else: ?>
                                    <a href="<?php echo SITE_URL; ?>index.php" class="btn-action btn-login">🔒 Login to Read</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php 
                $has_next = ($offset + $limit) < $total_books;
                if ($total_books > $limit): 
            ?>
                <div class="pagination-buttons">
                    <?php if ($page > 1): ?>
                        <a href="?search=<?php echo htmlspecialchars($search); ?>&category=<?php echo htmlspecialchars($category); ?>&page=<?php echo $page - 1; ?>" class="btn-action btn-view">← Previous</a>
                    <?php endif; ?>
                    
                    <span style="padding: 0.6rem 1rem; border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 6px; color: #b0b0b0;">
                        Page <?php echo $page; ?>
                    </span>
                    
                    <?php if ($has_next): ?>
                        <a href="?search=<?php echo htmlspecialchars($search); ?>&category=<?php echo htmlspecialchars($category); ?>&page=<?php echo $page + 1; ?>" class="btn-action btn-view">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <p class="empty-state-text">No books found matching your search.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

