<?php
require_once 'functions.php';

// Periksa apakah user sudah login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User tidak login']);
    exit;
}

// Periksa apakah ada post_id yang dikirim
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID post tidak valid']);
    exit;
}

$post_id = (int)$_POST['post_id'];
$user_id = $_SESSION['user_id'];

// Periksa apakah post yang akan dibagikan ada
global $conn;
$check_query = "SELECT * FROM posts WHERE id = '$post_id'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Post tidak ditemukan']);
    exit;
}

$post = mysqli_fetch_assoc($result);

// Bagikan post ke timeline user
$query = "INSERT INTO posts (user_id, content, image, original_post_id, created_at) 
          VALUES ('$user_id', 'membagikan postingan', NULL, '$post_id', NOW())";

if (mysqli_query($conn, $query)) {
    // Ambil info post dan user pembuat asli
    $original_user_id = $post['user_id'];
    
    // Tambahkan notifikasi jika bukan post sendiri
    if ($user_id != $original_user_id) {
        $notification_query = "INSERT INTO notifications (user_id, from_user_id, type, post_id, created_at) 
                             VALUES ('$original_user_id', '$user_id', 'share', '$post_id', NOW())";
        mysqli_query($conn, $notification_query);
        
        // Rekam aktivitas
        $username_query = "SELECT username FROM users WHERE id = '$original_user_id'";
        $username_result = mysqli_query($conn, $username_query);
        $username_row = mysqli_fetch_assoc($username_result);
        $post_owner_username = $username_row['username'];
        
        $activity_query = "INSERT INTO activities (user_id, activity_type, post_id, message, created_at) 
                          VALUES ('$user_id', 'share', '$post_id', 'membagikan post dari $post_owner_username', NOW())";
        mysqli_query($conn, $activity_query);
    }
    
    echo json_encode(['success' => true, 'message' => 'Post berhasil dibagikan']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal membagikan post']);
}

// Fungsi untuk mendapatkan base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    
    // Mendapatkan path ke root aplikasi
    $path = $_SERVER['REQUEST_URI'];
    $path = substr($path, 0, strpos($path, '/includes'));
    
    return $protocol . $domainName . $path;
}
?> 