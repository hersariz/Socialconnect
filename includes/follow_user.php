<?php
require_once 'functions.php';

// Pastikan user sudah login dan request melalui POST
if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit;
}

// Ambil user ID dari request
$target_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$follower_id = $_SESSION['user_id'];

if ($target_user_id <= 0 || $target_user_id == $follower_id) {
    echo json_encode(['success' => false, 'message' => 'ID user tidak valid']);
    exit;
}

// Follow/unfollow user
$following = followUser($follower_id, $target_user_id);

// Ambil jumlah follower baru
$followers = getUserFollowerCount($target_user_id);

// Return response
echo json_encode([
    'success' => true,
    'following' => $following,
    'followers' => $followers
]);
?> 