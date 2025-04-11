<?php
require_once 'functions.php';

// Pastikan user sudah login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil jumlah notifikasi baru
$notifications = getUnreadNotificationsCount($user_id);

// Return response
echo json_encode([
    'success' => true,
    'new_notifications' => $notifications
]);
?> 