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

$book = new Book($conn);
$page_title = 'Books';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$limit = 14;
$offset = ($page - 1) * $limit;

// Get total count for pagination
$total_count = 0;
if ($search) {
    $result = $book->searchBooks($search);
    $total_count = $result->num_rows;
} elseif ($category) {
    // Get total count for category
    $count_query = $conn->query("SELECT COUNT(*) as total FROM books WHERE category_id = " . (int)$category);
    $count_row = $count_query->fetch_assoc();
    $total_count = $count_row['total'];
    $result = $book->filterByCategory((int)$category, $limit, $offset);
} else {
    // Get total count for all books
    $count_query = $conn->query("SELECT COUNT(*) as total FROM books");
    $count_row = $count_query->fetch_assoc();
    $total_count = $count_row['total'];
    $result = $book->getAllBooks($limit, $offset);
}

$total_books = $result->num_rows;
$total_pages = ceil($total_count / $limit);

$cat_query = $conn->query("SELECT * FROM categories ORDER BY category_name");
$categories = [];
while ($cat = $cat_query->fetch_assoc()) {
    $categories[] = $cat;
}
?>
<?php $current_page = 'books.php'; require_once '../includes/header.php'; ?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title">📚 Browse Books</h1>
            <p class="page-subtitle">Explore our extensive library collection</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <!-- View Toggle -->
            <div style="display: flex; gap: 0.5rem; background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); padding: 0.35rem; border-radius: 8px;">
                <button id="gridViewBtn" class="view-toggle-btn active" onclick="switchViewMode('grid')" style="background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); color: var(--bg-primary); border: none; padding: 0.6rem 0.85rem; border-radius: 6px; cursor: pointer; font-size: 1.1rem; transition: all 0.2s ease; font-weight: 600;" title="Grid View">
                    ⊞
                </button>
                <button id="listViewBtn" class="view-toggle-btn" onclick="switchViewMode('list')" style="background: transparent; color: var(--accent-primary); border: none; padding: 0.6rem 0.85rem; border-radius: 6px; cursor: pointer; font-size: 1.1rem; transition: all 0.2s ease; font-weight: 600;" title="List View">
                    ☰
                </button>
            </div>
            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'librarian'): ?>
                <a href="<?php echo SITE_URL; ?>pages/add-book.php" class="btn btn-primary" style="width: auto; margin: 0;">➕ Add New Book</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, rgba(0, 212, 255, 0.05) 0%, rgba(255, 0, 110, 0.05) 100%); border: 1px solid var(--border-color);">
    <form method="GET" class="grid" style="gap: 1rem; margin: 0; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="form-group" style="margin: 0;">
            <input type="text" name="search" class="form-control" placeholder="Search by title, author, or ISBN..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="form-group" style="margin: 0;">
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($category == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin: 0;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">🔍 Search</button>
        </div>
    </form>
</div>

<!-- Books Grid/List -->
<div class="books-container" id="booksContainer">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="book-card">
            <!-- Book Cover -->
            <div class="book-cover">
                <?php if ($row['book_cover']): ?>
                    <img src="<?php echo SITE_URL . BOOK_COVER_PATH . htmlspecialchars($row['book_cover']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" class="book-cover-img">
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

                <!-- Description (List View Only) -->
                <div class="book-description">
                    <?php echo htmlspecialchars(substr($row['description'] ?? '', 0, 100)) . (strlen($row['description'] ?? '') > 100 ? '...' : ''); ?>
                </div>

                <!-- Action Buttons -->
                <div class="book-actions">
                    <a href="book-detail.php?id=<?php echo $row['book_id']; ?>" class="btn btn-primary">View Details</a>
                    <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'librarian'): ?>
                        <a href="edit-book.php?id=<?php echo $row['book_id']; ?>" class="btn btn-secondary">✏️ Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php if ($total_books == 0): ?>
    <div style="text-align: center; padding: 4rem 2rem; background: linear-gradient(135deg, rgba(0, 212, 255, 0.05) 0%, rgba(255, 0, 110, 0.05) 100%); border-radius: 12px; border: 1px solid var(--border-color);">
        <p style="font-size: 2rem; margin-bottom: 1rem;">📖</p>
        <p style="font-size: 1.25rem; color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 600;">No books found</p>
        <p style="color: var(--text-secondary);">Try searching with different keywords or browse all books.</p>
    </div>
<?php endif; ?>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin: 1.5rem 0 2rem 0; flex-wrap: wrap;">
        <!-- Previous Button -->
        <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn" style="padding: 0.6rem 0.75rem; font-weight: 600; font-size: 0.85rem; background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); color: var(--accent-primary); border-radius: 6px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='rgba(0, 212, 255, 0.2)'" onmouseout="this.style.background='rgba(0, 212, 255, 0.1)'">← Prev</a>
        <?php else: ?>
            <button class="btn" disabled style="padding: 0.6rem 0.75rem; font-weight: 600; font-size: 0.85rem; background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); color: var(--accent-primary); border-radius: 6px; opacity: 0.5; cursor: not-allowed;">← Prev</button>
        <?php endif; ?>

        <!-- Page Numbers -->
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; margin: 0 -0.25rem;">
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            // Show first page if we're far from it
            if ($start_page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="btn" style="padding: 0.6rem 0.75rem; font-weight: 600; font-size: 0.85rem; background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); color: var(--accent-primary); border-radius: 6px; cursor: pointer;">1</a>
                <?php if ($start_page > 2): ?>
                    <span style="color: var(--text-secondary);">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if ($i == $page): ?>
                    <button class="btn" style="padding: 0.6rem 0.75rem; font-weight: 600; font-size: 0.85rem; background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); color: var(--bg-primary); border: 1px solid var(--accent-primary); border-radius: 6px; cursor: default;">
                        <?php echo $i; ?>
                    </button>
                <?php else: ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="btn" style="padding: 0.6rem 0.75rem; font-weight: 600; font-size: 0.85rem; background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); color: var(--accent-primary); border-radius: 6px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='rgba(0, 212, 255, 0.2)'" onmouseout="this.style.background='rgba(0, 212, 255, 0.1)'">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Show last page if we're far from it -->
            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span style="color: var(--text-secondary);">...</span>
                <?php endif; ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" class="btn" style="padding: 0.6rem 0.75rem; font-weight: 600; font-size: 0.85rem; background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); color: var(--accent-primary); border-radius: 6px; cursor: pointer;">
                    <?php echo $total_pages; ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Next Button -->
        <?php if ($page < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn" style="padding: 0.6rem 0.75rem; font-weight: 600; font-size: 0.85rem; background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); color: var(--bg-primary); border: 1px solid var(--accent-primary); border-radius: 6px; cursor: pointer;">Next →</a>
        <?php else: ?>
            <button class="btn" disabled style="padding: 0.6rem 0.75rem; font-weight: 600; font-size: 0.85rem; background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%); color: var(--bg-primary); border: 1px solid var(--accent-primary); border-radius: 6px; opacity: 0.5; cursor: not-allowed;">Next →</button>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>

<style>
    
    .view-toggle-btn {
        transition: all 0.2s ease;
    }
    
    .view-toggle-btn:hover:not(.active) {
        background: rgba(0, 212, 255, 0.15) !important;
    }

    
    .books-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    
    .books-container.grid-view {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }

    
    .books-container.list-view {
        grid-template-columns: 1fr;
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

    
    .books-container.grid-view .book-card {
        flex-direction: column;
    }

    .books-container.grid-view .book-cover {
        height: 280px;
    }

    
    .books-container.list-view .book-card {
        flex-direction: row;
        gap: 1.5rem;
        padding: 1rem;
    }

    .books-container.list-view .book-card:hover {
        transform: translateX(8px);
    }

    .books-container.list-view .book-cover {
        height: 150px;
        width: 100px;
        flex-shrink: 0;
        border-radius: 8px;
        overflow: hidden;
    }

    .books-container.list-view .book-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    
    .book-cover {
        overflow: hidden;
        background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-secondary) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .book-cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .book-cover-placeholder {
        font-size: 5rem;
        color: var(--text-secondary);
    }

    .books-container.list-view .book-cover-placeholder {
        font-size: 2.5rem;
    }

    
    .book-info {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .books-container.list-view .book-info {
        padding: 0;
    }

    
    .book-title {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 0.95rem;
        line-height: 1.3;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .books-container.list-view .book-title {
        font-size: 1.1rem;
        -webkit-line-clamp: 1;
    }

    
    .book-author {
        color: var(--accent-primary);
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
        background: linear-gradient(135deg, rgba(131, 56, 236, 0.2) 0%, rgba(0, 212, 255, 0.1) 100%);
        border: 1px solid rgba(131, 56, 236, 0.3);
        color: var(--accent-primary);
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        margin-bottom: 0.75rem;
        width: fit-content;
    }

    
    .book-description {
        color: var(--text-secondary);
        font-size: 0.85rem;
        line-height: 1.4;
        margin-bottom: 0.75rem;
        display: none;
    }

    .books-container.list-view .book-description {
        display: block;
    }

    
    .book-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: auto;
    }

    .books-container.list-view .book-actions {
        flex-direction: row;
        gap: 0.5rem;
    }

    .books-container.list-view .book-actions .btn {
        flex: 1;
        padding: 0.6rem 0.8rem !important;
        font-size: 0.8rem !important;
    }

    .book-actions .btn {
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
        padding: 0.75rem;
        transition: all 0.2s ease;
    }

    
    @media (max-width: 768px) {
        .books-container.grid-view {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .books-container.list-view .book-card {
            flex-direction: column;
            gap: 1rem;
        }

        .books-container.list-view .book-cover {
            height: 200px;
            width: 100%;
        }

        .books-container.list-view .book-actions {
            flex-direction: column;
        }

        .books-container.list-view .book-actions .btn {
            flex: 1;
        }
    }
</style>

<script>
    // Initialize view mode from localStorage
    function initializeViewMode() {
        const viewMode = localStorage.getItem('bookViewMode') || 'grid';
        switchViewMode(viewMode, false);
    }

    // Switch view mode
    function switchViewMode(mode, save = true) {
        const container = document.getElementById('booksContainer');
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');

        if (mode === 'grid') {
            container.classList.remove('list-view');
            container.classList.add('grid-view');
            gridBtn.classList.add('active');
            listBtn.classList.remove('active');
            gridBtn.style.background = 'linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%)';
            gridBtn.style.color = 'var(--bg-primary)';
            listBtn.style.background = 'transparent';
            listBtn.style.color = 'var(--accent-primary)';
        } else if (mode === 'list') {
            container.classList.remove('grid-view');
            container.classList.add('list-view');
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');
            listBtn.style.background = 'linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%)';
            listBtn.style.color = 'var(--bg-primary)';
            gridBtn.style.background = 'transparent';
            gridBtn.style.color = 'var(--accent-primary)';
        }

        if (save) {
            localStorage.setItem('bookViewMode', mode);
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', initializeViewMode);
</script>

