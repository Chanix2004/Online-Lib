<?php

// ========== DATABASE CONFIGURATION ==========
// InfinityFree credentials
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_41697683');
define('DB_PASS', 'QUwcuZtGFfVxyX');
define('DB_NAME', 'if0_41697683_library_management');

// ========== SITE CONFIGURATION ==========
define('SITE_URL', 'https://online-lib.great-site.net/');
define('SITE_NAME', 'Library Management System');
define('SITE_TITLE', 'LMS');

define('UPLOAD_PATH', 'uploads/');
define('BOOK_COVER_PATH', 'uploads/book_covers/');
define('PROFILE_PIC_PATH', 'uploads/profile_pics/');
define('PDF_PATH', 'uploads/pdfs/');

define('MAX_FILE_SIZE', 10 * 1024 * 1024);
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);
define('MAX_PDF_SIZE', 50 * 1024 * 1024);

define('SESSION_TIMEOUT', 3600);
define('REMEMBER_ME_DAYS', 30);

define('MAX_BORROW_DAYS', 14);

// ========== EMAIL CONFIGURATION - BREVO ==========
// Get your API key from https://www.brevo.com/ (Free tier: 300 emails/day)
// Brevo API key for email delivery (Free tier: 300 emails/day)
define('BREVO_API_KEY', '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');

// Sender email address - using verified Gmail
define('MAIL_FROM', 'rohan.14yahoo@gmail.com');
// Legacy SMTP settings (not used with Brevo, but kept for reference)
define('MAIL_HOST', 'mail.infinityfree.app');
define('MAIL_PORT', 465);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_ENCRYPTION', 'ssl');

define('TIMEZONE', 'UTC');
date_default_timezone_set(TIMEZONE);

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

$ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif'];

$ALLOWED_PDF_TYPES = ['pdf'];

define('SUCCESS_MSG', 'Operation completed successfully!');
define('ERROR_MSG', 'An error occurred. Please try again.');
define('INVALID_MSG', 'Invalid input provided.');
define('UNAUTHORIZED_MSG', 'You are not authorized to perform this action.');
define('NOT_FOUND_MSG', 'Resource not found.');
?>
