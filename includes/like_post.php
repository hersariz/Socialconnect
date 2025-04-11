<?php
require_once 'functions.php';

// Pastikan user sudah login dan request melalui POST
if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit;
}

// Ambil post ID dari request
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID post tidak valid']);
    exit;
}

// Like/unlike post
$liked = likePost($post_id, $user_id);

// Ambil jumlah like baru
$likes = getPostLikes($post_id);

// Return response
echo json_encode([
    'success' => true,
    'liked' => $liked,
    'likes' => $likes
]);
?> 