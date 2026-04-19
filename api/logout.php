<?php
require_once '../config/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

$db = new Database();
$conn = $db->connect();

$auth = new Auth($conn);
$auth->logout();
header('Location: ' . SITE_URL); 
exit;
?>

