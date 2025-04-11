<?php
require_once '../includes/functions.php';

// Pastikan user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Tandai semua notifikasi sebagai sudah dibaca
markNotificationsAsRead($user_id);

// Ambil notifikasi
$notifications = getUserNotifications($user_id);

$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Notifikasi</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($notifications) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <img src="<?php echo $notification['profile_pic'] ? '../assets/uploads/' . $notification['profile_pic'] : getDefaultAvatar(); ?>" 
                                         class="rounded-circle me-3" width="50" height="50" alt="<?php echo $notification['username']; ?>">
                                    <div>
                                        <p class="mb-1">
                                            <a href="profile.php?id=<?php echo $notification['from_user_id']; ?>" class="text-decoration-none fw-bold">
                                                <?php echo $notification['username']; ?>
                                            </a> 
                                            <?php 
                                            switch ($notification['type']) {
                                                case 'like':
                                                    echo 'menyukai post Anda.';
                                                    break;
                                                case 'comment':
                                                    echo 'mengomentari post Anda.';
                                                    break;
                                                case 'follow':
                                                    echo 'mulai mengikuti Anda.';
                                                    break;
                                                case 'mention':
                                                    echo 'menyebut Anda dalam post.';
                                                    break;
                                            }
                                            ?>
                                        </p>
                                        <?php if ($notification['type'] == 'like' || $notification['type'] == 'comment'): ?>
                                            <a href="post.php?id=<?php echo $notification['post_id']; ?>" class="btn btn-sm btn-outline-primary">Lihat Post</a>
                                        <?php endif; ?>
                                        <small class="text-muted d-block mt-1"><?php echo timeAgo($notification['created_at']); ?></small>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-5">
                        <h5>Tidak ada notifikasi</h5>
                        <p class="text-muted">Anda belum memiliki notifikasi baru.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 