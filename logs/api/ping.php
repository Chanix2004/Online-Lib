<?php
require_once '../config/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

$db = new Database();
$conn = $db->connect();

$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    exit;
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
?>

