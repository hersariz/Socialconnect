<?php
require_once 'functions.php';

// Pastikan user sudah login dan request melalui POST
if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit;
}

// Ambil data dari request
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';
$user_id = $_SESSION['user_id'];

if ($post_id <= 0 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Tambahkan komentar
if (addComment($post_id, $user_id, $comment)) {
    $user = getUserById($user_id);
    
    // Return response
    echo json_encode([
        'success' => true,
        'username' => $user['username'],
        'profile_pic' => $user['profile_pic'],
        'comment' => $comment
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan komentar']);
}
?> 