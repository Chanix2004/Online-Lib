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
$book_id = $_GET['id'] ?? 0;

if (empty($book_id) || !is_numeric($book_id)) {
    header('Location: ' . SITE_URL . 'pages/books.php');
    exit;
}

$book_data = $book->getBookById($book_id);
if (!$book_data) {
    header('Location: ' . SITE_URL . 'pages/books.php');
    exit;
}

$pdf_file = $book_data['pdf_file'] ?? '';
if (empty($pdf_file)) {
    header('Location: ' . SITE_URL . 'pages/book-detail.php?id=' . $book_id);
    exit;
}

$pdf_path = SITE_URL . PDF_PATH . $pdf_file;
$page_title = 'Read: ' . $book_data['title'];
?>
<?php $current_page = 'read-book.php'; require_once '../includes/header.php'; ?>

<link rel="stylesheet" href="<?php echo SITE_URL; ?>css/pdf-viewer.css">

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
        <div style="flex: 1; min-width: 200px;">
            <h1 class="page-title">📖 <?php echo htmlspecialchars($book_data['title']); ?></h1>
            <p class="page-subtitle">by <?php echo htmlspecialchars($book_data['author']); ?></p>
        </div>
        <a href="<?php echo SITE_URL; ?>pages/book-detail.php?id=<?php echo $book_id; ?>" class="btn btn-secondary" style="width: auto;">← Back to Details</a>
    </div>
</div>

<!-- PDF Viewer Container -->
<div class="pdf-viewer-container" id="pdf-container">
    <!-- PDF Header -->
    <div class="pdf-header">
        <h3>📕 <?php echo htmlspecialchars($book_data['title']); ?></h3>
        <div class="pdf-toolbar">
            <a href="<?php echo $pdf_path; ?>#toolbar=1&navpanes=1&view=FitH" target="_blank" class="pdf-control-button" title="Open in New Tab (Full Controls)">🔗 Open in New Tab</a>
            <a href="<?php echo $pdf_path; ?>" download="<?php echo htmlspecialchars($book_data['title']); ?>.pdf" class="pdf-control-button" title="Download PDF">⬇️ Download PDF</a>
        </div>
    </div>
    
    <!-- PDF Viewer -->
    <div class="pdf-viewer-wrapper">
        <iframe src="<?php echo $pdf_path; ?>#toolbar=1&navpanes=0&view=FitH" class="pdf-viewer" title="PDF Viewer"></iframe>
    </div>
</div>

<!-- Browser Native PDF Controls Note -->
<div class="pdf-control-info" style="background: linear-gradient(135deg, rgba(51, 255, 0, 0.1) 0%, transparent 100%); border: 1px solid rgba(51, 255, 0, 0.3); padding: 1rem; border-radius: 8px; margin-top: 1rem;">
    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">
        💡 <strong>Tip:</strong> Use the PDF viewer's built-in controls (visible in the embedded viewer) for page navigation, zoom, search, and fullscreen. For full editing and annotation features, <a href="<?php echo $pdf_path; ?>" style="color: var(--accent-primary); text-decoration: none; font-weight: 600;">open the PDF in a new tab</a>.
    </p>
</div>

<?php require_once '../includes/footer.php'; ?>

