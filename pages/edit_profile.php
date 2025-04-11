<?php
require_once '../includes/functions.php';

// Pastikan user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Proses form update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = clean_input($_POST['bio']);
    $location = clean_input($_POST['location']);
    $website = clean_input($_POST['website']);
    
    // Validasi website URL jika tidak kosong
    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $error_message = "Format URL website tidak valid.";
    } else {
        // Upload foto profil baru jika ada
        $profile_pic = $user['profile_pic']; // Default ke foto profil lama
        
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['size'] > 0) {
            $upload_dir = '../assets/uploads/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($_FILES['profile_pic']['type'], $allowed_types)) {
                $error_message = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
            } else if ($_FILES['profile_pic']['size'] > 2 * 1024 * 1024) { // 2MB
                $error_message = "Ukuran file terlalu besar. Maksimal 2MB.";
            } else {
                $file_name = 'profile_' . $user_id . '_' . time() . '_' . $_FILES['profile_pic']['name'];
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file_path)) {
                    $profile_pic = $file_name;
                } else {
                    $error_message = "Gagal mengupload file. Silakan coba lagi.";
                }
            }
        }
        
        if (!isset($error_message)) {
            // Update profil di database
            global $conn;
            $query = "UPDATE users SET 
                      bio = '" . mysqli_real_escape_string($conn, $bio) . "', 
                      location = '" . mysqli_real_escape_string($conn, $location) . "', 
                      website = '" . mysqli_real_escape_string($conn, $website) . "', 
                      profile_pic = '" . mysqli_real_escape_string($conn, $profile_pic) . "'
                      WHERE id = '$user_id'";
            
            if (mysqli_query($conn, $query)) {
                $success_message = "Profil berhasil diperbarui!";
                
                // Refresh data user
                $user = getUserById($user_id);
            } else {
                $error_message = "Gagal memperbarui profil. Silakan coba lagi.";
            }
        }
    }
}

// Jika ada form untuk ganti password
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = "Semua field password harus diisi.";
    } else if (strlen($new_password) < 6) {
        $password_error = "Password baru minimal 6 karakter.";
    } else if ($new_password !== $confirm_password) {
        $password_error = "Password baru dan konfirmasi tidak cocok.";
    } else {
        // Verifikasi password lama
        global $conn;
        $query = "SELECT password FROM users WHERE id = '$user_id'";
        $result = mysqli_query($conn, $query);
        $user_data = mysqli_fetch_assoc($result);
        
        if (password_verify($current_password, $user_data['password'])) {
            // Update password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
            
            if (mysqli_query($conn, $update_query)) {
                $password_success = "Password berhasil diperbarui!";
            } else {
                $password_error = "Gagal memperbarui password. Silakan coba lagi.";
            }
        } else {
            $password_error = "Password saat ini tidak valid.";
        }
    }
}

$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Edit Profil</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <img src="<?php echo $user['profile_pic'] ? '../assets/uploads/' . $user['profile_pic'] : getDefaultAvatar(); ?>" 
                                 class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" alt="Profile Picture">
                            <div class="form-group">
                                <label for="profile_pic" class="form-label">Ubah Foto Profil</label>
                                <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max 2MB)</small>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah.</small>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" disabled>
                                <small class="text-muted">Email tidak dapat diubah.</small>
                            </div>
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo $user['bio']; ?></textarea>
                                <small class="text-muted">Deskripsikan diri Anda dalam beberapa kalimat.</small>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Lokasi</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo $user['location']; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website" value="<?php echo $user['website']; ?>" placeholder="https://">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ganti Password</h5>
            </div>
            <div class="card-body">
                <?php if (isset($password_error)): ?>
                    <div class="alert alert-danger"><?php echo $password_error; ?></div>
                <?php endif; ?>
                <?php if (isset($password_success)): ?>
                    <div class="alert alert-success"><?php echo $password_success; ?></div>
                <?php endif; ?>
                
                <form action="edit_profile.php" method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="text-muted">Password minimal 6 karakter.</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" name="change_password" class="btn btn-warning">Ganti Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 