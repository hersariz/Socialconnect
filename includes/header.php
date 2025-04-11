<?php
// Gunakan path relatif yang diatur sebelumnya, atau default ke direktori root
$relative_path = isset($relative_path) ? $relative_path : '';
require_once $relative_path . 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialConnect - Platform Media Sosial Modern</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $relative_path; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $relative_path; ?>index.php">
                <i class="fas fa-share-nodes me-2"></i>SocialConnect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $relative_path; ?>index.php"><i class="fas fa-home me-1"></i> Beranda</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $relative_path; ?>pages/profile.php?id=<?php echo $_SESSION['user_id']; ?>">
                            <i class="fas fa-user me-1"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $relative_path; ?>pages/explore.php">
                            <i class="fas fa-compass me-1"></i> Jelajah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $relative_path; ?>pages/notifications.php">
                            <i class="fas fa-bell me-1"></i> Notifikasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $relative_path; ?>pages/messages.php">
                            <i class="fas fa-envelope me-1"></i> Pesan
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <form class="d-flex me-2" action="<?php echo $relative_path; ?>pages/search.php" method="GET">
                    <div class="input-group">
                        <input class="form-control form-control-sm" type="search" name="q" placeholder="Cari..." aria-label="Search">
                        <button class="btn btn-light btn-sm" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <div class="d-flex">
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo $relative_path; ?>pages/profile.php?id=<?php echo $_SESSION['user_id']; ?>">Profil Saya</a></li>
                                <li><a class="dropdown-item" href="<?php echo $relative_path; ?>pages/settings.php">Pengaturan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $relative_path; ?>pages/logout.php">Keluar</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $relative_path; ?>pages/login.php" class="btn btn-outline-light me-2">Masuk</a>
                        <a href="<?php echo $relative_path; ?>pages/register.php" class="btn btn-light">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="container py-4"><?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?> 