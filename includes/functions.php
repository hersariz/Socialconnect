<?php
session_start();
require_once 'config.php';

// Fungsi untuk registrasi user
function registerUser($username, $email, $password) {
    global $conn;
    
    // Cek apakah username sudah digunakan
    $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        return false; // User sudah ada
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $query = "INSERT INTO users (username, email, password, created_at) 
              VALUES ('$username', '$email', '$hashed_password', NOW())";
    
    if (mysqli_query($conn, $query)) {
        return true; // Registrasi berhasil
    } else {
        return false; // Registrasi gagal
    }
}

// Fungsi untuk login
function loginUser($username, $password) {
    global $conn;
    
    $username = clean_input($username);
    
    $query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            // Buat session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Update last_activity
            $user_id = $user['id'];
            $update_query = "UPDATE users SET last_activity = NOW() WHERE id = '$user_id'";
            mysqli_query($conn, $update_query);
            
            return true; // Login berhasil
        }
    }
    
    return false; // Login gagal
}

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mendapatkan info user
function getUserById($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $query = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Fungsi untuk membuat post
function createPost($user_id, $content, $image = null) {
    global $conn;
    
    $user_id = (int)$user_id;
    $content = clean_input($content);
    $image = $image ? clean_input($image) : null;
    
    $query = "INSERT INTO posts (user_id, content, image, created_at) 
              VALUES ('$user_id', '$content', " . ($image ? "'$image'" : "NULL") . ", NOW())";
    
    if (mysqli_query($conn, $query)) {
        $post_id = mysqli_insert_id($conn);
        
        // Rekam aktivitas
        $activity_query = "INSERT INTO activities (user_id, activity_type, post_id, message, created_at) 
                          VALUES ('$user_id', 'post', '$post_id', 'membuat post baru', NOW())";
        mysqli_query($conn, $activity_query);
        
        return true;
    }
    
    return false;
}

// Fungsi untuk mengambil semua post
function getAllPosts($limit = 20) {
    global $conn;
    
    $limit = (int)$limit;
    $query = "SELECT posts.*, users.username, users.profile_pic
              FROM posts 
              JOIN users ON posts.user_id = users.id
              ORDER BY posts.created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $posts = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

// Fungsi untuk mendapatkan post user tertentu
function getUserPosts($user_id, $limit = 10) {
    global $conn;
    
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    
    $query = "SELECT * FROM posts 
              WHERE user_id = '$user_id' 
              ORDER BY created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $posts = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

// Fungsi untuk like post
function likePost($post_id, $user_id) {
    global $conn;
    
    $post_id = (int)$post_id;
    $user_id = (int)$user_id;
    
    // Cek apakah sudah like
    $check_query = "SELECT * FROM likes WHERE post_id = '$post_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        // Hapus like
        $query = "DELETE FROM likes WHERE post_id = '$post_id' AND user_id = '$user_id'";
        mysqli_query($conn, $query);
        
        // Hapus aktivitas
        $activity_query = "DELETE FROM activities WHERE user_id = '$user_id' AND activity_type = 'like' AND post_id = '$post_id'";
        mysqli_query($conn, $activity_query);
        
        return false; // Unlike
    } else {
        // Tambah like
        $query = "INSERT INTO likes (post_id, user_id, created_at) 
                  VALUES ('$post_id', '$user_id', NOW())";
        mysqli_query($conn, $query);
        
        // Ambil info post dan user
        $post_query = "SELECT user_id FROM posts WHERE id = '$post_id'";
        $post_result = mysqli_query($conn, $post_query);
        $post = mysqli_fetch_assoc($post_result);
        $post_owner_id = $post['user_id'];
        
        // Rekam aktivitas
        if ($user_id != $post_owner_id) {
            $username_query = "SELECT username FROM users WHERE id = '$post_owner_id'";
            $username_result = mysqli_query($conn, $username_query);
            $username_row = mysqli_fetch_assoc($username_result);
            $post_owner_username = $username_row['username'];
            
            $activity_query = "INSERT INTO activities (user_id, activity_type, post_id, message, created_at) 
                              VALUES ('$user_id', 'like', '$post_id', 'menyukai post dari $post_owner_username', NOW())";
            mysqli_query($conn, $activity_query);
            
            // Tambah notifikasi
            $notification_query = "INSERT INTO notifications (user_id, from_user_id, type, post_id, created_at) 
                                 VALUES ('$post_owner_id', '$user_id', 'like', '$post_id', NOW())";
            mysqli_query($conn, $notification_query);
        }
        
        return true; // Like
    }
}

// Fungsi untuk mengecek apakah post disukai user
function isPostLikedByUser($post_id, $user_id) {
    global $conn;
    
    if (!$user_id) return false;
    
    $post_id = (int)$post_id;
    $user_id = (int)$user_id;
    
    $query = "SELECT * FROM likes WHERE post_id = '$post_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    
    return mysqli_num_rows($result) > 0;
}

// Fungsi untuk menghitung jumlah like pada post
function getPostLikes($post_id) {
    global $conn;
    
    $post_id = (int)$post_id;
    $query = "SELECT COUNT(*) as count FROM likes WHERE post_id = '$post_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

// Fungsi untuk menambahkan komentar
function addComment($post_id, $user_id, $comment) {
    global $conn;
    
    $post_id = (int)$post_id;
    $user_id = (int)$user_id;
    $comment = clean_input($comment);
    
    $query = "INSERT INTO comments (post_id, user_id, comment, created_at) 
              VALUES ('$post_id', '$user_id', '$comment', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $comment_id = mysqli_insert_id($conn);
        
        // Ambil info post dan user
        $post_query = "SELECT user_id FROM posts WHERE id = '$post_id'";
        $post_result = mysqli_query($conn, $post_query);
        $post = mysqli_fetch_assoc($post_result);
        $post_owner_id = $post['user_id'];
        
        // Rekam aktivitas
        if ($user_id != $post_owner_id) {
            $username_query = "SELECT username FROM users WHERE id = '$post_owner_id'";
            $username_result = mysqli_query($conn, $username_query);
            $username_row = mysqli_fetch_assoc($username_result);
            $post_owner_username = $username_row['username'];
            
            $activity_query = "INSERT INTO activities (user_id, activity_type, post_id, message, created_at) 
                              VALUES ('$user_id', 'comment', '$post_id', 'mengomentari post dari $post_owner_username', NOW())";
            mysqli_query($conn, $activity_query);
            
            // Tambah notifikasi
            $notification_query = "INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id, created_at) 
                                 VALUES ('$post_owner_id', '$user_id', 'comment', '$post_id', '$comment_id', NOW())";
            mysqli_query($conn, $notification_query);
        }
        
        return true;
    }
    
    return false;
}

// Fungsi untuk mengambil komentar pada post
function getPostComments($post_id) {
    global $conn;
    
    $post_id = (int)$post_id;
    $query = "SELECT comments.*, users.username, users.profile_pic
              FROM comments 
              JOIN users ON comments.user_id = users.id
              WHERE comments.post_id = '$post_id'
              ORDER BY comments.created_at ASC";
    
    $result = mysqli_query($conn, $query);
    $comments = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }
    
    return $comments;
}

// Fungsi untuk menghitung jumlah komentar pada post
function getPostCommentCount($post_id) {
    global $conn;
    
    $post_id = (int)$post_id;
    $query = "SELECT COUNT(*) as count FROM comments WHERE post_id = '$post_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

// Fungsi untuk follow user
function followUser($follower_id, $following_id) {
    global $conn;
    
    $follower_id = (int)$follower_id;
    $following_id = (int)$following_id;
    
    // Cek apakah sudah follow
    $check_query = "SELECT * FROM follows WHERE follower_id = '$follower_id' AND following_id = '$following_id'";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        // Hapus follow
        $query = "DELETE FROM follows WHERE follower_id = '$follower_id' AND following_id = '$following_id'";
        mysqli_query($conn, $query);
        
        // Hapus aktivitas
        $activity_query = "DELETE FROM activities WHERE user_id = '$follower_id' AND activity_type = 'follow' AND target_id = '$following_id'";
        mysqli_query($conn, $activity_query);
        
        return false; // Unfollow
    } else {
        // Tambah follow
        $query = "INSERT INTO follows (follower_id, following_id, created_at) 
                  VALUES ('$follower_id', '$following_id', NOW())";
        mysqli_query($conn, $query);
        
        // Ambil username yang difollow
        $username_query = "SELECT username FROM users WHERE id = '$following_id'";
        $username_result = mysqli_query($conn, $username_query);
        $username_row = mysqli_fetch_assoc($username_result);
        $following_username = $username_row['username'];
        
        // Rekam aktivitas
        $activity_query = "INSERT INTO activities (user_id, activity_type, target_id, message, created_at) 
                          VALUES ('$follower_id', 'follow', '$following_id', 'mulai mengikuti $following_username', NOW())";
        mysqli_query($conn, $activity_query);
        
        // Tambah notifikasi
        $notification_query = "INSERT INTO notifications (user_id, from_user_id, type, created_at) 
                              VALUES ('$following_id', '$follower_id', 'follow', NOW())";
        mysqli_query($conn, $notification_query);
        
        return true; // Follow
    }
}

// Fungsi untuk mengecek apakah user mengikuti user lain
function isFollowing($follower_id, $following_id) {
    global $conn;
    
    $follower_id = (int)$follower_id;
    $following_id = (int)$following_id;
    
    $query = "SELECT * FROM follows WHERE follower_id = '$follower_id' AND following_id = '$following_id'";
    $result = mysqli_query($conn, $query);
    
    return mysqli_num_rows($result) > 0;
}

// Fungsi untuk mendapatkan jumlah post user
function getUserPostCount($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $query = "SELECT COUNT(*) as count FROM posts WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

// Fungsi untuk mendapatkan jumlah follower user
function getUserFollowerCount($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $query = "SELECT COUNT(*) as count FROM follows WHERE following_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

// Fungsi untuk mendapatkan jumlah following user
function getUserFollowingCount($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $query = "SELECT COUNT(*) as count FROM follows WHERE follower_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

// Fungsi untuk mendapatkan user following
function getUserFollowing($user_id, $limit = 100) {
    global $conn;
    
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    
    $query = "SELECT users.* 
              FROM follows 
              JOIN users ON follows.following_id = users.id
              WHERE follows.follower_id = '$user_id' 
              ORDER BY follows.created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $users = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    return $users;
}

// Fungsi untuk mendapatkan user follower
function getUserFollowers($user_id, $limit = 100) {
    global $conn;
    
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    
    $query = "SELECT users.* 
              FROM follows 
              JOIN users ON follows.follower_id = users.id
              WHERE follows.following_id = '$user_id' 
              ORDER BY follows.created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $users = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    return $users;
}

// Fungsi untuk mendapatkan saran teman
function getSuggestedUsers($limit = 5) {
    global $conn;
    
    $limit = (int)$limit;
    $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    
    if ($current_user_id) {
        // Exclude users yang sudah difollow dan diri sendiri
        $query = "SELECT * FROM users 
                  WHERE id != '$current_user_id' 
                  AND id NOT IN (
                    SELECT following_id FROM follows WHERE follower_id = '$current_user_id'
                  )
                  ORDER BY RAND()
                  LIMIT $limit";
    } else {
        // Untuk user yang belum login, tampilkan semua user secara acak
        $query = "SELECT * FROM users ORDER BY RAND() LIMIT $limit";
    }
    
    $result = mysqli_query($conn, $query);
    $users = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    return $users;
}

// Fungsi untuk mendapatkan user online
function getOnlineUsers($limit = 5) {
    global $conn;
    
    $limit = (int)$limit;
    $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $time_threshold = date('Y-m-d H:i:s', strtotime('-15 minutes'));
    
    if ($current_user_id) {
        // Menampilkan user yang difollow dan online
        $query = "SELECT users.* 
                  FROM users 
                  JOIN follows ON users.id = follows.following_id 
                  WHERE follows.follower_id = '$current_user_id' 
                  AND users.last_activity > '$time_threshold'
                  LIMIT $limit";
    } else {
        // Untuk user yang belum login, tampilkan semua user online secara acak
        $query = "SELECT * FROM users 
                  WHERE last_activity > '$time_threshold' 
                  ORDER BY RAND()
                  LIMIT $limit";
    }
    
    $result = mysqli_query($conn, $query);
    $users = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    return $users;
}

// Fungsi untuk mendapatkan aktivitas terbaru
function getRecentActivities($limit = 5) {
    global $conn;
    
    $limit = (int)$limit;
    $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    
    if ($current_user_id) {
        // Aktivitas dari user yang difollow
        $query = "SELECT activities.*, users.username, users.profile_pic
                  FROM activities 
                  JOIN users ON activities.user_id = users.id
                  WHERE activities.user_id IN (
                    SELECT following_id FROM follows WHERE follower_id = '$current_user_id'
                  ) OR activities.user_id = '$current_user_id'
                  ORDER BY activities.created_at DESC
                  LIMIT $limit";
    } else {
        // Untuk user yang belum login, tampilkan semua aktivitas terakhir
        $query = "SELECT activities.*, users.username, users.profile_pic
                  FROM activities 
                  JOIN users ON activities.user_id = users.id
                  ORDER BY activities.created_at DESC
                  LIMIT $limit";
    }
    
    $result = mysqli_query($conn, $query);
    $activities = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = $row;
    }
    
    return $activities;
}

// Fungsi untuk mendapatkan post dengan gambar dari user
function getUserMediaPosts($user_id, $limit = 12) {
    global $conn;
    
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    
    $query = "SELECT * FROM posts 
              WHERE user_id = '$user_id' 
              AND image IS NOT NULL 
              ORDER BY created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $posts = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

// Fungsi untuk mendapatkan post yang disukai user
function getUserLikedPosts($user_id, $limit = 10) {
    global $conn;
    
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    
    $query = "SELECT posts.*, users.username, users.profile_pic 
              FROM likes 
              JOIN posts ON likes.post_id = posts.id 
              JOIN users ON posts.user_id = users.id
              WHERE likes.user_id = '$user_id' 
              ORDER BY likes.created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $posts = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

// Fungsi untuk mendapatkan foto user
function getUserPhotos($user_id, $limit = 9) {
    global $conn;
    
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    
    $query = "SELECT id, image FROM posts 
              WHERE user_id = '$user_id' 
              AND image IS NOT NULL 
              ORDER BY created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $photos = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $photos[] = $row;
    }
    
    return $photos;
}

// Fungsi untuk mendapatkan jumlah notifikasi yang belum dibaca
function getUnreadNotificationsCount($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    
    $query = "SELECT COUNT(*) as count FROM notifications 
              WHERE user_id = '$user_id' AND is_read = 0";
    
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

// Fungsi untuk mendapatkan notifikasi
function getUserNotifications($user_id, $limit = 20) {
    global $conn;
    
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    
    $query = "SELECT notifications.*, users.username, users.profile_pic 
              FROM notifications 
              JOIN users ON notifications.from_user_id = users.id
              WHERE notifications.user_id = '$user_id' 
              ORDER BY notifications.created_at DESC
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $notifications = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

// Fungsi untuk menandai notifikasi sebagai sudah dibaca
function markNotificationsAsRead($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'";
    return mysqli_query($conn, $query);
}

// Fungsi untuk memformat waktu menjadi "waktu yang lalu"
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);      // nilai 60 adalah detik dalam 1 menit
    $hours   = round($seconds / 3600);     // nilai 3600 adalah detik dalam 1 jam
    $days    = round($seconds / 86400);    // nilai 86400 adalah detik dalam 1 hari
    $weeks   = round($seconds / 604800);   // nilai 604800 adalah detik dalam 1 minggu
    $months  = round($seconds / 2629440);  // nilai 2629440 adalah detik dalam 1 bulan
    $years   = round($seconds / 31553280); // nilai 31553280 adalah detik dalam 1 tahun
    
    if ($seconds <= 60) {
        return "Baru saja";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 menit lalu" : "$minutes menit lalu";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 jam lalu" : "$hours jam lalu";
    } else if ($days <= 7) {
        return ($days == 1) ? "Kemarin" : "$days hari lalu";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 minggu lalu" : "$weeks minggu lalu";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 bulan lalu" : "$months bulan lalu";
    } else {
        return ($years == 1) ? "1 tahun lalu" : "$years tahun lalu";
    }
}

// Fungsi untuk logout
function logoutUser() {
    session_unset();
    session_destroy();
}

// Fungsi untuk mendapatkan URL avatar default
function getDefaultAvatar($use_relative_path = false) {
    return "https://ui-avatars.com/api/?name=User&background=random";
}

// Fungsi untuk mendapatkan URL gambar post default
function getDefaultPostImage($use_relative_path = false) {
    return "https://via.placeholder.com/800x600?text=No+Image";
}

// Fungsi untuk mengirim pesan
function sendMessage($sender_id, $receiver_id, $message) {
    global $conn;
    
    $sender_id = (int)$sender_id;
    $receiver_id = (int)$receiver_id;
    $message = clean_input($message);
    
    // Validasi input
    if (empty($message) || $sender_id <= 0 || $receiver_id <= 0) {
        return false;
    }
    
    $query = "INSERT INTO messages (sender_id, receiver_id, message, created_at) 
              VALUES ('$sender_id', '$receiver_id', '$message', NOW())";
    
    if (mysqli_query($conn, $query)) {
        return true;
    }
    
    return false;
}

// Fungsi untuk mendapatkan daftar kontak
function getUserContacts($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    
    // Mendapatkan pengguna yang pernah bertukar pesan
    $query = "SELECT DISTINCT 
              CASE 
                WHEN sender_id = '$user_id' THEN receiver_id 
                WHEN receiver_id = '$user_id' THEN sender_id 
              END as contact_id
              FROM messages 
              WHERE sender_id = '$user_id' OR receiver_id = '$user_id'
              ORDER BY (
                SELECT MAX(created_at) 
                FROM messages 
                WHERE (sender_id = '$user_id' AND receiver_id = contact_id) 
                OR (sender_id = contact_id AND receiver_id = '$user_id')
              ) DESC";
    
    $result = mysqli_query($conn, $query);
    $contacts = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['contact_id'])) {
                $contact_id = $row['contact_id'];
                $contact_info = getUserById($contact_id);
                
                if ($contact_info) {
                    // Mendapatkan pesan terakhir
                    $last_msg_query = "SELECT message, created_at, is_read, sender_id 
                                     FROM messages 
                                     WHERE (sender_id = '$user_id' AND receiver_id = '$contact_id') 
                                     OR (sender_id = '$contact_id' AND receiver_id = '$user_id') 
                                     ORDER BY created_at DESC 
                                     LIMIT 1";
                    
                    $last_msg_result = mysqli_query($conn, $last_msg_query);
                    $last_msg = mysqli_fetch_assoc($last_msg_result);
                    
                    // Mendapatkan jumlah pesan yang belum dibaca
                    $unread_query = "SELECT COUNT(*) as count 
                                    FROM messages 
                                    WHERE sender_id = '$contact_id' 
                                    AND receiver_id = '$user_id' 
                                    AND is_read = 0";
                    
                    $unread_result = mysqli_query($conn, $unread_query);
                    $unread = mysqli_fetch_assoc($unread_result);
                    
                    $contact_info['last_message'] = $last_msg;
                    $contact_info['unread_count'] = $unread['count'];
                    
                    $contacts[] = $contact_info;
                }
            }
        }
    }
    
    return $contacts;
}

// Fungsi untuk mendapatkan pesan antara dua pengguna
function getMessagesBetweenUsers($user1_id, $user2_id, $limit = 50) {
    global $conn;
    
    $user1_id = (int)$user1_id;
    $user2_id = (int)$user2_id;
    $limit = (int)$limit;
    
    $query = "SELECT * FROM messages 
              WHERE (sender_id = '$user1_id' AND receiver_id = '$user2_id') 
              OR (sender_id = '$user2_id' AND receiver_id = '$user1_id') 
              ORDER BY created_at DESC 
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $messages = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
        
        // Menandai pesan sebagai sudah dibaca
        $update_query = "UPDATE messages 
                        SET is_read = 1 
                        WHERE sender_id = '$user2_id' 
                        AND receiver_id = '$user1_id' 
                        AND is_read = 0";
        
        mysqli_query($conn, $update_query);
        
        // Mengembalikan pesan dalam urutan kronologis (lama ke baru)
        $messages = array_reverse($messages);
    }
    
    return $messages;
}

// Fungsi untuk menghitung jumlah pesan yang belum dibaca
function getUnreadMessagesCount($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    
    $query = "SELECT COUNT(*) as count FROM messages 
              WHERE receiver_id = '$user_id' AND is_read = 0";
    
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

// Fungsi untuk membuat hashtag menjadi klickable
function linkHashtags($text) {
    // Regex untuk mencocokkan hashtag (#text)
    $pattern = '/#(\w+)/';
    
    // Ganti hashtag dengan link
    $text = preg_replace(
        $pattern,
        '<a href="search.php?q=%23$1&type=posts" class="hashtag-link">#$1</a>',
        $text
    );
    
    return $text;
}

// Fungsi untuk membuat hashtag menjadi klickable dengan path relatif
function linkHashtagsWithRelativePath($text, $relative_path = '') {
    // Regex untuk mencocokkan hashtag (#text)
    $pattern = '/#(\w+)/';
    
    // Ganti hashtag dengan link
    $text = preg_replace(
        $pattern,
        '<a href="' . $relative_path . 'pages/search.php?q=%23$1&type=posts" class="hashtag-link">#$1</a>',
        $text
    );
    
    return $text;
}
?> 