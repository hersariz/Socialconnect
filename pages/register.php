<?php
require_once '../includes/functions.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Proses registrasi form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua field harus diisi.";
    } else if (strlen($username) < 3 || strlen($username) > 30) {
        $error_message = "Username harus antara 3-30 karakter.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } else if (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter.";
    } else if ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak cocok.";
    } else {
        // Coba registrasi
        if (registerUser($username, $email, $password)) {
            // Auto login setelah registrasi
            loginUser($username, $password);
            header('Location: ../index.php');
            exit;
        } else {
            $error_message = "Username atau email sudah digunakan.";
        }
    }
}

$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Daftar Akun Baru</h3>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $_POST['username'] ?? ''; ?>" required>
                        <small class="text-muted">Username harus antara 3-30 karakter dan hanya boleh mengandung huruf, angka, dan underscore.</small>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Password minimal 6 karakter.</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                        <label class="form-check-label" for="agree_terms">
                            Saya setuju dengan <a href="terms.php" class="text-decoration-none">Ketentuan Layanan</a> dan <a href="privacy.php" class="text-decoration-none">Kebijakan Privasi</a>
                        </label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Daftar</button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p>Sudah punya akun? <a href="login.php" class="text-decoration-none">Masuk</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 