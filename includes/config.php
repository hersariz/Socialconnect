<?php
// Konfigurasi Database
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "social_media_db";

// Membuat koneksi
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Mengecek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Set zona waktu
date_default_timezone_set('Asia/Jakarta');
?> 