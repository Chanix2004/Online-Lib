<?php
require_once '../config/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/book.php';
require_once '../includes/member.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$auth->checkSessionTimeout();

$book = new Book($conn);
$member = new Member($conn);

$page_title = 'Dashboard';

$stats = [
    'total_books' => $book->getBookCount(),
];

$recent_books = $conn->query("SELECT book_id, title, author, publisher, publication_year, book_cover, category_name, isbn, language FROM books JOIN categories ON books.category_id = categories.category_id LIMIT 4");
?>
<?php $current_page = 'dashboard.php'; require_once '../includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
    <p class="page-subtitle">Here's your library dashboard overview</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-<?php echo $_SESSION['role'] == 'member' ? '1' : '2'; ?>">
    <a href="books.php" style="text-decoration: none;">
        <div class="card" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <div class="stats-box">
                <div class="stats-number"><?php echo $stats['total_books']; ?></div>
                <div class="stats-label">Total Books</div>
            </div>
        </div>
    </a>

    <?php if ($_SESSION['role'] != 'member'): ?>
        <a href="members.php" style="text-decoration: none;">
            <div class="card" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                <div class="stats-box">
                    <div class="stats-number"><?php echo $member->getMemberCount(); ?></div>
                    <div class="stats-label">Total Members</div>
                </div>
            </div>
        </a>
    <?php endif; ?>
</div>

<!-- Featured Books Section -->
<div style="margin-top: 3rem; margin-bottom: 2rem;">
    <h2 style="color: var(--text-primary); font-size: 1.5rem; margin-bottom: 1.5rem; font-weight: 700;">📚 Featured Books</h2>
    
    <?php while ($book_row = $recent_books->fetch_assoc()): ?>
        <div style="background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; display: grid; grid-template-columns: 300px 1fr; gap: 2.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow-md);" onmouseover="this.style.boxShadow='var(--shadow-xl)'" onmouseout="this.style.boxShadow='var(--shadow-md)'">
            <!-- Book Cover -->
            <div style="display: flex; align-items: center; justify-content: center;">
                <div style="height: 300px; width: 220px; border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-secondary) 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 20px 50px rgba(0, 212, 255, 0.3);">
                    <?php if ($book_row['book_cover']): ?>
                        <img src="<?php echo SITE_URL . BOOK_COVER_PATH . htmlspecialchars($book_row['book_cover']); ?>" alt="<?php echo htmlspecialchars($book_row['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="font-size: 5rem; color: var(--text-secondary);">📖</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Book Details -->
            <div>
                <!-- Title & Author -->
                <h3 style="font-size: 1.8rem; font-weight: 900; background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 0.5rem; line-height: 1.2;">
                    <?php echo htmlspecialchars($book_row['title']); ?>
                </h3>
                <div style="font-size: 1.1rem; color: var(--accent-primary); font-weight: 700; margin-bottom: 1.5rem;">
                    by <?php echo htmlspecialchars($book_row['author']); ?>
                </div>

                <!-- Metadata Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; padding: 1.5rem; background: linear-gradient(135deg, rgba(0, 212, 255, 0.05) 0%, rgba(255, 0, 110, 0.05) 100%); border: 1px solid var(--border-color); border-radius: 10px;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Publisher</div>
                        <div style="font-size: 0.95rem; color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($book_row['publisher'] ?? 'Unknown'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Year</div>
                        <div style="font-size: 0.95rem; color: var(--text-primary); font-weight: 500;"><?php echo $book_row['publication_year'] ?? 'Unknown'; ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">ISBN</div>
                        <div style="font-size: 0.95rem; color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($book_row['isbn'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Category</div>
                        <div style="font-size: 0.95rem; color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($book_row['category_name']); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--accent-primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Language</div>
                        <div style="font-size: 0.95rem; color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($book_row['language'] ?? 'English'); ?></div>
                    </div>
                </div>

                <!-- View Button -->
                <a href="<?php echo $_SESSION['role'] != 'member' ? 'book-detail.php' : 'book-detail.php'; ?>?id=<?php echo $book_row['book_id']; ?>" class="btn btn-primary" style="font-weight: 600; padding: 0.75rem 1.5rem; font-size: 0.95rem;">📖 View Full Details</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

