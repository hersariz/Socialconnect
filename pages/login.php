<?php
require_once '../includes/functions.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

// Proses login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = "Semua field harus diisi.";
    } else {
        // Coba login
        if (loginUser($username, $password)) {
            header('Location: ../index.php');
            exit;
        } else {
            $error_message = "Username/email atau password salah.";
        }
    }
}

// Path relatif berbeda untuk halaman di folder pages
$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Masuk ke SocialConnect</h3>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username atau Email</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $_POST['username'] ?? ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Masuk</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="text-decoration-none">Lupa password?</a>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <p>Belum punya akun? <a href="register.php" class="text-decoration-none">Daftar sekarang</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 