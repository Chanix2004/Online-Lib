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

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'librarian') {
    header('Location: ' . SITE_URL . 'pages/dashboard.php');
    exit;
}

$book = new Book($conn);
$page_title = 'Edit Book';
$error = '';
$success = '';
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($book_id <= 0) {
    header('Location: ' . SITE_URL . 'pages/books.php');
    exit;
}

$categories_sql = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat;
}

$fetch_sql = "SELECT * FROM books WHERE book_id = ?";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bind_param("i", $book_id);
$fetch_stmt->execute();
$book_result = $fetch_stmt->get_result();

if ($book_result->num_rows === 0) {
    header('Location: ' . SITE_URL . 'pages/books.php');
    exit;
}

$current_book = $book_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $isbn = trim($_POST['isbn'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $publication_year = trim($_POST['publication_year'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $language = trim($_POST['language'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $errors = [];
    if (empty($isbn)) $errors[] = 'ISBN is required';
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($author)) $errors[] = 'Author is required';
    if (empty($publisher)) $errors[] = 'Publisher is required';
    if (empty($publication_year) || !is_numeric($publication_year)) $errors[] = 'Valid publication year is required';
    if (empty($category_id)) $errors[] = 'Category is required';
    if (empty($language)) $errors[] = 'Language is required';

    if ($isbn !== $current_book['isbn']) {
        $check_sql = "SELECT book_id FROM books WHERE isbn = ? AND book_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $isbn, $book_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = 'ISBN already exists in the system';
        }
    }

    if (empty($errors)) {
        try {

            $update_sql = "UPDATE books SET isbn = ?, title = ?, author = ?, publisher = ?, publication_year = ?, category_id = ?, language = ?, description = ? WHERE book_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssisssi", $isbn, $title, $author, $publisher, $publication_year, $category_id, $language, $description, $book_id);
            
            if ($update_stmt->execute()) {

                if (isset($_FILES['book_cover']) && $_FILES['book_cover']['error'] != UPLOAD_ERR_NO_FILE) {
                    $cover_path = __DIR__ . '/../' . BOOK_COVER_PATH;

                    if (!is_dir($cover_path)) {
                        mkdir($cover_path, 0777, true);
                    }

                    if ($_FILES['book_cover']['error'] == UPLOAD_ERR_OK) {
                        $file_ext = strtolower(pathinfo($_FILES['book_cover']['name'], PATHINFO_EXTENSION));
                        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                        $max_size = 5 * 1024 * 1024;

                        if (in_array($file_ext, $allowed_exts)) {
                            if ($_FILES['book_cover']['size'] <= $max_size) {

                                if (!empty($current_book['book_cover'])) {
                                    $old_cover = $cover_path . $current_book['book_cover'];
                                    if (file_exists($old_cover)) {
                                        unlink($old_cover);
                                    }
                                }

                                $new_cover_name = 'cover_' . $book_id . '.' . $file_ext;
                                $cover_full_path = $cover_path . $new_cover_name;

                                if (move_uploaded_file($_FILES['book_cover']['tmp_name'], $cover_full_path)) {
                                    @chmod($cover_full_path, 0644);  // Suppress warnings on shared hosting

                                    $cover_update_sql = "UPDATE books SET book_cover = ? WHERE book_id = ?";
                                    $cover_update_stmt = $conn->prepare($cover_update_sql);
                                    $cover_update_stmt->bind_param("si", $new_cover_name, $book_id);
                                    $cover_update_stmt->execute();
                                }
                            } else {
                                $errors[] = 'Book cover file is too large (max 5MB)';
                            }
                        } else {
                            $errors[] = 'Invalid book cover format (JPG/PNG only)';
                        }
                    }
                }

                if (isset($_FILES['book_pdf']) && $_FILES['book_pdf']['error'] != UPLOAD_ERR_NO_FILE) {
                    $pdf_path = __DIR__ . '/../' . PDF_PATH;

                    if (!is_dir($pdf_path)) {
                        mkdir($pdf_path, 0777, true);
                    }

                    if ($_FILES['book_pdf']['error'] == UPLOAD_ERR_OK) {
                        $file_ext = strtolower(pathinfo($_FILES['book_pdf']['name'], PATHINFO_EXTENSION));
                        $max_size = 50 * 1024 * 1024;

                        if ($file_ext == 'pdf') {
                            if ($_FILES['book_pdf']['size'] <= $max_size) {

                                if (!empty($current_book['pdf_file'])) {
                                    $old_pdf = $pdf_path . $current_book['pdf_file'];
                                    if (file_exists($old_pdf)) {
                                        unlink($old_pdf);
                                    }
                                }

                                $new_pdf_name = 'book_' . $book_id . '.pdf';
                                $pdf_full_path = $pdf_path . $new_pdf_name;

                                if (move_uploaded_file($_FILES['book_pdf']['tmp_name'], $pdf_full_path)) {
                                    @chmod($pdf_full_path, 0644);  // Suppress warnings on shared hosting

                                    $pdf_update_sql = "UPDATE books SET pdf_file = ? WHERE book_id = ?";
                                    $pdf_update_stmt = $conn->prepare($pdf_update_sql);
                                    $pdf_update_stmt->bind_param("si", $new_pdf_name, $book_id);
                                    $pdf_update_stmt->execute();
                                }
                            } else {
                                $errors[] = 'Book PDF file is too large (max 50MB)';
                            }
                        } else {
                            $errors[] = 'Invalid book PDF format (PDF only)';
                        }
                    }
                }

                if (empty($errors)) {
                    $success = 'Book updated successfully! Redirecting to books page...';
                    header('refresh:2;url=' . SITE_URL . 'pages/books.php');
                }
            } else {
                $errors[] = 'Error updating book in database';
            }
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>
<?php $current_page = 'edit-book.php'; require_once '../includes/header.php'; ?>

<div class="page-header">
    <h1 class="page-title">📚 Edit Book</h1>
    <p class="page-subtitle">Update book details, cover & PDF</p>
</div>

<div style="display: grid; grid-template-columns: 1fr; max-width: 900px; margin: 0 auto;">
    <div class="card">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <strong>⚠️ Validation Error</strong><br>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <strong>✓</strong><br>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- ISBN and Title -->
            <div class="form-group inline">
                <div>
                    <label class="form-label">ISBN *</label>
                    <input type="text" name="isbn" class="form-control" placeholder="e.g., 978-0-06-112008-4" required value="<?php echo htmlspecialchars($current_book['isbn'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" placeholder="Book title" required value="<?php echo htmlspecialchars($current_book['title'] ?? ''); ?>">
                </div>
            </div>

            <!-- Author and Publisher -->
            <div class="form-group inline">
                <div>
                    <label class="form-label">Author *</label>
                    <input type="text" name="author" class="form-control" placeholder="Author name" required value="<?php echo htmlspecialchars($current_book['author'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Publisher *</label>
                    <input type="text" name="publisher" class="form-control" placeholder="Publisher name" required value="<?php echo htmlspecialchars($current_book['publisher'] ?? ''); ?>">
                </div>
            </div>

            <!-- Publication Year and Category -->
            <div class="form-group inline">
                <div>
                    <label class="form-label">Publication Year *</label>
                    <input type="number" name="publication_year" class="form-control" required value="<?php echo htmlspecialchars($current_book['publication_year'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        <?php 
                        foreach ($categories as $cat): 
                        ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($current_book['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Language -->
            <div class="form-group">
                <label class="form-label">Language *</label>
                <select name="language" class="form-control" required>
                    <option value="">-- Select Language --</option>
                    <option value="English" <?php echo ($current_book['language'] == 'English') ? 'selected' : ''; ?>>English</option>
                    <option value="Urdu" <?php echo ($current_book['language'] == 'Urdu') ? 'selected' : ''; ?>>Urdu</option>
                    <option value="Spanish" <?php echo ($current_book['language'] == 'Spanish') ? 'selected' : ''; ?>>Spanish</option>
                    <option value="French" <?php echo ($current_book['language'] == 'French') ? 'selected' : ''; ?>>French</option>
                    <option value="German" <?php echo ($current_book['language'] == 'German') ? 'selected' : ''; ?>>German</option>
                    <option value="Chinese" <?php echo ($current_book['language'] == 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                    <option value="Japanese" <?php echo ($current_book['language'] == 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                </select>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="Enter book description..."><?php echo htmlspecialchars($current_book['description'] ?? ''); ?></textarea>
            </div>

            <!-- Book Cover Upload -->
            <div class="form-group">
                <label class="form-label">📷 Book Cover (JPG/PNG, max 5MB)</label>
                <?php if (!empty($current_book['book_cover'])): ?>
                    <div style="margin-bottom: 1rem; padding: 0.75rem; background: var(--bg-secondary); border-radius: 8px;">
                        <small style="color: var(--text-secondary);">Current cover: <strong><?php echo htmlspecialchars($current_book['book_cover']); ?></strong></small>
                    </div>
                <?php endif; ?>
                <input type="file" name="book_cover" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                <small style="color: var(--text-gray); display: block; margin-top: 0.5rem;">Upload a new cover to replace the current one</small>
            </div>

            <!-- PDF Upload -->
            <div class="form-group">
                <label class="form-label">📄 Book PDF (PDF only, max 50MB)</label>
                <?php if (!empty($current_book['pdf_file'])): ?>
                    <div style="margin-bottom: 1rem; padding: 0.75rem; background: var(--bg-secondary); border-radius: 8px;">
                        <small style="color: var(--text-secondary);">Current PDF: <strong><?php echo htmlspecialchars($current_book['pdf_file']); ?></strong></small>
                    </div>
                <?php endif; ?>
                <input type="file" name="book_pdf" class="form-control" accept=".pdf">
                <small style="color: var(--text-gray); display: block; margin-top: 0.5rem;">Upload a new PDF to replace the current one</small>
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">✓ Update Book</button>
                <a href="<?php echo SITE_URL; ?>pages/books.php" class="btn btn-secondary" style="flex: 1;">← Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

