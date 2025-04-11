<?php
require_once 'includes/functions.php';

// Function untuk mendapatkan URL avatar dari UI Avatars service
function getUIAvatar($name) {
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=random";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Avatar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Test Avatar</h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 text-center">
                <h5>Default Avatar Function</h5>
                <img src="<?php echo getDefaultAvatar(); ?>" class="avatar" alt="Default Avatar">
                <p class="small text-muted">getDefaultAvatar()</p>
            </div>
            
            <div class="col-md-3 text-center">
                <h5>UI Avatars - John</h5>
                <img src="<?php echo getUIAvatar('John Doe'); ?>" class="avatar" alt="John Doe">
                <p class="small text-muted">getUIAvatar('John Doe')</p>
            </div>
            
            <div class="col-md-3 text-center">
                <h5>UI Avatars - Jane</h5>
                <img src="<?php echo getUIAvatar('Jane Smith'); ?>" class="avatar" alt="Jane Smith">
                <p class="small text-muted">getUIAvatar('Jane Smith')</p>
            </div>
            
            <div class="col-md-3 text-center">
                <h5>Existing Profile Image</h5>
                <img src="assets/uploads/profile_4_1744407164_IMG_20230121_143532.png" class="avatar" alt="Existing Profile">
                <p class="small text-muted">Direct path to existing file</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Avatar Service Info</h5>
                    </div>
                    <div class="card-body">
                        <p>Kita menggunakan <a href="https://ui-avatars.com/" target="_blank">UI Avatars</a> sebagai avatar default.</p>
                        <p>Format URL: <code>https://ui-avatars.com/api/?name=USER&background=random</code></p>
                        <p>Kelebihan:</p>
                        <ul>
                            <li>Tidak perlu menyimpan file gambar lokal</li>
                            <li>Gambar dibuat dinamis dari nama pengguna</li>
                            <li>Warna latar belakang acak untuk membedakan pengguna</li>
                        </ul>
                        
                        <p>Cara lain yang bisa digunakan:</p>
                        <ul>
                            <li><a href="https://gravatar.com/" target="_blank">Gravatar</a> - Avatar berdasarkan email</li>
                            <li><a href="https://boringavatars.com/" target="_blank">Boring Avatars</a> - Avatar bergaya flat design</li>
                            <li><a href="https://avatars.dicebear.com/" target="_blank">DiceBear Avatars</a> - Avatar kartun dengan berbagai gaya</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 