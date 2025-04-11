<?php
require_once 'functions.php';

// Pastikan user sudah login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk membuat post']);
    exit;
}

// Pastikan ada content yang dikirim
if (!isset($_POST['content']) || empty(trim($_POST['content']))) {
    echo json_encode(['success' => false, 'message' => 'Konten tidak boleh kosong']);
    exit;
}

$user_id = $_SESSION['user_id'];
$content = clean_input($_POST['content']);
$image = null;

// Handle upload gambar jika ada
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung']);
        exit;
    }
    
    // Generate unique filename
    $new_filename = uniqid('post_') . '.' . $ext;
    $upload_path = '../assets/uploads/' . $new_filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $image = $new_filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload gambar']);
        exit;
    }
}

// Buat post
$result = createPost($user_id, $content, $image);
$post_id = mysqli_insert_id($GLOBALS['conn']);

// Jika post berhasil dibuat
if ($post_id) {
    // Cek apakah ada hashtag dalam konten
    preg_match_all('/#(\w+)/', $content, $hashtags);
    if (!empty($hashtags[1])) {
        // Update trending hashtag
        foreach ($hashtags[1] as $hashtag) {
            $hashtag = clean_input($hashtag);
            // Cek apakah hashtag sudah ada di trending
            $check_query = "SELECT * FROM trending_topics WHERE hashtag = '$hashtag'";
            $check_result = mysqli_query($GLOBALS['conn'], $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Update count
                $update_query = "UPDATE trending_topics SET post_count = post_count + 1, last_used = NOW() WHERE hashtag = '$hashtag'";
                mysqli_query($GLOBALS['conn'], $update_query);
            } else {
                // Insert new hashtag
                $insert_query = "INSERT INTO trending_topics (hashtag, post_count, last_used) VALUES ('$hashtag', 1, NOW())";
                mysqli_query($GLOBALS['conn'], $insert_query);
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Post berhasil dibuat!', 'post_id' => $post_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal membuat post.']);
}
?> 