<?php
require_once '../config/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/book.php';

$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: ' . SITE_URL . 'pages/books.php');
    exit;
}

$book = new Book($conn);
$book_data = $book->getBookById($_GET['id']);

if (!$book_data) {
    header('Location: ' . SITE_URL . 'pages/books.php');
    exit;
}

$page_title = $book_data['title'];
?>
<?php $current_page = 'book-detail.php'; require_once '../includes/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <a href="<?php echo SITE_URL; ?>pages/books.php" style="color: var(--accent-primary); text-decoration: none; font-weight: 600; transition: all 0.3s;">← Back to Books</a>
</div>

<div class="card">
    <div class="grid grid-2">
        <!-- Book Cover -->
        <div>
            <div style="height: 500px; margin-bottom: 1.5rem; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-lg); display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);">
                <?php if ($book_data['book_cover']): ?>
                    <img src="<?php echo SITE_URL . BOOK_COVER_PATH . htmlspecialchars($book_data['book_cover']); ?>" alt="<?php echo htmlspecialchars($book_data['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 6rem;">📖</div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Book Details -->
        <div>
            <h1 style="margin-bottom: 0.5rem; color: var(--text-primary); font-size: 2rem;"><?php echo htmlspecialchars($book_data['title']); ?></h1>
            <p style="color: var(--accent-primary); font-size: 1.15rem; margin-bottom: 2rem; font-weight: 600;">by <?php echo htmlspecialchars($book_data['author']); ?></p>

            <!-- Book Info Grid -->
            <div style="background: linear-gradient(135deg, rgba(0, 212, 255, 0.1) 0%, rgba(255, 0, 110, 0.05) 100%); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                    <div>
                        <div style="font-weight: 700; color: var(--accent-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Publisher</div>
                        <div style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($book_data['publisher'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--accent-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Year</div>
                        <div style="color: var(--text-primary); font-weight: 500;"><?php echo $book_data['publication_year'] ?? 'N/A'; ?></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                    <div>
                        <div style="font-weight: 700; color: var(--accent-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">ISBN</div>
                        <div style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($book_data['isbn'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--accent-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Category</div>
                        <div style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($book_data['category_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                    <div>
                        <div style="font-weight: 700; color: var(--accent-primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Language</div>
                        <div style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($book_data['language'] ?? 'English'); ?></div>
                    </div>
                </div>
            </div>

            <!-- PDF Options -->
            <?php if ($book_data['pdf_file']): ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <a href="<?php echo SITE_URL; ?>pages/read-book.php?id=<?php echo $book_data['book_id']; ?>" class="btn btn-primary" style="width: 100%; font-weight: 700;">
                        👁️ Read Online
                    </a>
                    <a href="<?php echo SITE_URL . PDF_PATH . htmlspecialchars($book_data['pdf_file']); ?>" class="btn btn-secondary" style="width: 100%; font-weight: 700;" download>
                        📥 Download PDF
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Description Section -->
    <?php if ($book_data['description']): ?>
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
            <h3 style="margin-bottom: 1rem; color: var(--accent-primary); font-size: 1.25rem; font-weight: 700;">📝 Description</h3>
            <p style="color: var(--text-secondary); line-height: 1.8;"><?php echo nl2br(htmlspecialchars($book_data['description'])); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

