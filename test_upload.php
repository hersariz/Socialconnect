<?php
// Pengujian sederhana untuk upload file
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cek apakah ada file yang diupload
    if (isset($_FILES['test_image']) && $_FILES['test_image']['size'] > 0) {
        $upload_dir = 'assets/uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($_FILES['test_image']['type'], $allowed_types)) {
            $error = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        } else if ($_FILES['test_image']['size'] > 2 * 1024 * 1024) { // 2MB
            $error = "Ukuran file terlalu besar. Maksimal 2MB.";
        } else {
            $file_name = 'test_' . time() . '_' . $_FILES['test_image']['name'];
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['test_image']['tmp_name'], $file_path)) {
                $success = "File berhasil diupload: " . $file_path;
            } else {
                $error = "Gagal mengupload file. Error: " . error_get_last()['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Upload Gambar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Test Upload Gambar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form action="test_upload.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="test_image" class="form-label">Pilih Gambar</label>
                                <input type="file" class="form-control" id="test_image" name="test_image" accept="image/*" required>
                                <small class="text-muted">Format: JPG, PNG, GIF (Maks 2MB)</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Info Server</h5>
                    </div>
                    <div class="card-body">
                        <pre><?php
                        echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
                        echo "post_max_size: " . ini_get('post_max_size') . "\n";
                        echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
                        echo "memory_limit: " . ini_get('memory_limit') . "\n";
                        echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
                        
                        // Cek apakah direktori uploads dapat ditulis
                        echo "\nDirectory Permissions:\n";
                        echo "assets/uploads/ is writable: " . (is_writable('assets/uploads/') ? 'Yes' : 'No') . "\n";
                        
                        // PHP Version
                        echo "\nPHP Version: " . phpversion() . "\n";
                        ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 