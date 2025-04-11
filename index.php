<?php
require_once 'includes/functions.php';

// Jika user sudah login dan mengirim post baru
if (isLoggedIn() && isset($_POST['post_content'])) {
    $content = $_POST['post_content'];
    $user_id = $_SESSION['user_id'];
    
    // Upload gambar jika ada
    $image_name = null;
    if (isset($_FILES['post_image']) && $_FILES['post_image']['size'] > 0) {
        $upload_dir = 'assets/uploads/';
        $image_name = time() . '_' . $_FILES['post_image']['name'];
        move_uploaded_file($_FILES['post_image']['tmp_name'], $upload_dir . $image_name);
    }
    
    // Simpan post ke database
    if (createPost($user_id, $content, $image_name)) {
        $success_message = "Post berhasil dibuat!";
    } else {
        $error_message = "Post gagal dibuat. Silakan coba lagi.";
    }
}

// Mendapatkan semua post
$posts = getAllPosts();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-3">
        <!-- Sidebar / Profile Card -->
        <?php if (isLoggedIn()): ?>
            <?php $user = getUserById($_SESSION['user_id']); ?>
            <div class="card profile-card mb-4">
                <div class="card-body">
                    <img src="<?php echo $user['profile_pic'] ? 'assets/uploads/' . $user['profile_pic'] : getDefaultAvatar(); ?>" 
                         class="profile-avatar" alt="<?php echo $user['username']; ?>">
                    <h5 class="profile-username"><?php echo $user['username']; ?></h5>
                    <p class="profile-bio"><?php echo $user['bio'] ?? 'Belum ada bio.'; ?></p>
                    
                    <div class="profile-stats">
                        <div class="profile-stat">
                            <span class="profile-stat-value"><?php echo getUserPostCount($user['id']); ?></span>
                            <span class="profile-stat-label">Post</span>
                        </div>
                        <div class="profile-stat">
                            <span class="profile-stat-value"><?php echo getUserFollowerCount($user['id']); ?></span>
                            <span class="profile-stat-label">Pengikut</span>
                        </div>
                        <div class="profile-stat">
                            <span class="profile-stat-value"><?php echo getUserFollowingCount($user['id']); ?></span>
                            <span class="profile-stat-label">Mengikuti</span>
                        </div>
                    </div>
                    
                    <a href="pages/profile.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm w-100">Lihat Profil</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Selamat Datang!</h5>
                    <p class="card-text">Masuk atau daftar untuk mulai terhubung dengan teman-teman Anda.</p>
                    <a href="pages/login.php" class="btn btn-primary me-2">Masuk</a>
                    <a href="pages/register.php" class="btn btn-outline-primary">Daftar</a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Suggested Friends -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Saran Teman</h6>
            </div>
            <div class="card-body p-0">
                <?php $suggested_users = getSuggestedUsers(); ?>
                <?php if (count($suggested_users) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($suggested_users as $user): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $user['profile_pic'] ? 'assets/uploads/' . $user['profile_pic'] : getDefaultAvatar(); ?>" 
                                         class="rounded-circle me-2" width="32" height="32" alt="<?php echo $user['username']; ?>">
                                    <a href="pages/profile.php?id=<?php echo $user['id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo $user['username']; ?>
                                    </a>
                                </div>
                                <?php if (isLoggedIn()): ?>
                                    <button class="btn btn-sm btn-primary follow-btn" data-user-id="<?php echo $user['id']; ?>">
                                        Ikuti
                                    </button>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">
                        <p>Tidak ada saran teman saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Categories/Trending -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Trending Topics</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">#TrendingTopik1</h6>
                                <small class="text-muted">1.2k posts</small>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">#TrendingTopik2</h6>
                                <small class="text-muted">985 posts</small>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">#TrendingTopik3</h6>
                                <small class="text-muted">752 posts</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <!-- Create Post Form -->
        <?php if (isLoggedIn()): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Buat Post Baru</h5>
                    <form action="index.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <textarea class="form-control" id="post-content" name="post_content" rows="3" placeholder="Apa yang Anda pikirkan?" maxlength="500" required></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Karakter tersisa: <span id="char-count">500</span></small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="post-image" class="form-label">Tambahkan Gambar (Opsional)</label>
                            <input class="form-control" type="file" id="post-image" name="post_image" accept="image/*">
                            <div class="position-relative mt-2">
                                <img id="image-preview" src="" class="img-fluid rounded mt-2" style="max-height: 200px; display: none;">
                                <button type="button" class="btn-close position-absolute top-0 end-0 bg-white rounded-circle p-2 shadow-sm remove-image" style="display: none;"></button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Posting</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Posts Feed -->
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card post-card mb-4">
                    <div class="post-header">
                        <img src="<?php echo $post['profile_pic'] ? 'assets/uploads/' . $post['profile_pic'] : getDefaultAvatar(); ?>" 
                             class="post-avatar" alt="<?php echo $post['username']; ?>">
                        <div>
                            <a href="pages/profile.php?id=<?php echo $post['user_id']; ?>" class="text-decoration-none">
                                <h6 class="post-username"><?php echo $post['username']; ?></h6>
                            </a>
                            <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                        </div>
                    </div>
                    
                    <div class="post-content">
                        <p><?php echo nl2br(linkHashtagsWithRelativePath($post['content'])); ?></p>
                        <?php if (!empty($post['image'])): ?>
                            <img src="assets/uploads/<?php echo $post['image']; ?>" class="post-image img-fluid rounded" alt="Post image">
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-actions">
                        <?php $is_liked = isPostLikedByUser($post['id'], $_SESSION['user_id'] ?? 0); ?>
                        <button class="post-action-btn like-btn <?php echo $is_liked ? 'liked' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                            <i class="<?php echo $is_liked ? 'fas' : 'far'; ?> fa-heart"></i>
                            <span><?php echo getPostLikes($post['id']); ?></span>
                        </button>
                        <button class="post-action-btn comment-btn" data-post-id="<?php echo $post['id']; ?>">
                            <i class="far fa-comment"></i>
                            <span><?php echo getPostCommentCount($post['id']); ?></span>
                        </button>
                        <button class="post-action-btn share-btn" data-post-id="<?php echo $post['id']; ?>">
                            <i class="far fa-share-square"></i>
                        </button>
                    </div>
                    
                    <div class="comment-section" id="comments-<?php echo $post['id']; ?>" style="display: none;">
                        <h6>Komentar</h6>
                        <div class="comments-container">
                            <?php $comments = getPostComments($post['id']); ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <img src="<?php echo $comment['profile_pic'] ? 'assets/uploads/' . $comment['profile_pic'] : getDefaultAvatar(); ?>" 
                                         class="comment-avatar" alt="<?php echo $comment['username']; ?>">
                                    <div class="comment-content">
                                        <h6 class="comment-username"><?php echo $comment['username']; ?></h6>
                                        <p class="comment-text"><?php echo $comment['comment']; ?></p>
                                        <small class="comment-time"><?php echo timeAgo($comment['created_at']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (isLoggedIn()): ?>
                            <form class="comment-form mt-3" data-post-id="<?php echo $post['id']; ?>">
                                <input type="text" class="form-control comment-input" placeholder="Tulis komentar...">
                                <button type="submit" class="btn btn-primary ms-2">Kirim</button>
                            </form>
                        <?php else: ?>
                            <div class="mt-3 text-center">
                                <a href="pages/login.php" class="text-decoration-none">Masuk untuk mengomentari</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <h5>Belum ada post</h5>
                    <p class="text-muted">Jadilah yang pertama membuat post!</p>
                    <?php if (!isLoggedIn()): ?>
                        <a href="pages/register.php" class="btn btn-primary">Daftar Sekarang</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-3">
        <!-- Online Users -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Teman Online</h6>
            </div>
            <div class="card-body p-0">
                <?php $online_users = getOnlineUsers(); ?>
                <?php if (count($online_users) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($online_users as $user): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="position-relative">
                                    <img src="<?php echo $user['profile_pic'] ? 'assets/uploads/' . $user['profile_pic'] : getDefaultAvatar(); ?>" 
                                         class="rounded-circle me-2" width="32" height="32" alt="<?php echo $user['username']; ?>">
                                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle p-1" style="width: 10px; height: 10px;"></span>
                                </div>
                                <a href="pages/profile.php?id=<?php echo $user['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo $user['username']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">
                        <p>Tidak ada teman yang online saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Aktivitas Terbaru</h6>
            </div>
            <div class="card-body p-0">
                <?php $recent_activities = getRecentActivities(); ?>
                <?php if (count($recent_activities) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recent_activities as $activity): ?>
                            <li class="list-group-item">
                                <div class="d-flex">
                                    <img src="<?php echo $activity['profile_pic'] ? 'assets/uploads/' . $activity['profile_pic'] : getDefaultAvatar(); ?>" 
                                         class="rounded-circle me-2" width="32" height="32" alt="<?php echo $activity['username']; ?>">
                                    <div>
                                        <p class="mb-0">
                                            <a href="pages/profile.php?id=<?php echo $activity['user_id']; ?>" class="text-decoration-none fw-bold">
                                                <?php echo $activity['username']; ?>
                                            </a> 
                                            <?php echo $activity['message']; ?>
                                        </p>
                                        <small class="text-muted"><?php echo timeAgo($activity['created_at']); ?></small>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="p-3 text-center text-muted">
                        <p>Belum ada aktivitas terbaru.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer Links -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap">
                    <a href="pages/about.php" class="text-decoration-none text-muted me-3 mb-2">Tentang</a>
                    <a href="pages/privacy.php" class="text-decoration-none text-muted me-3 mb-2">Privasi</a>
                    <a href="pages/terms.php" class="text-decoration-none text-muted me-3 mb-2">Ketentuan</a>
                    <a href="pages/contact.php" class="text-decoration-none text-muted me-3 mb-2">Hubungi Kami</a>
                    <a href="pages/help.php" class="text-decoration-none text-muted me-3 mb-2">Bantuan</a>
                </div>
                <p class="text-muted mb-0 mt-2">&copy; <?php echo date('Y'); ?> SocialConnect</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Share -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Bagikan Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-around mb-4">
                    <a href="#" class="text-decoration-none text-center share-option" data-type="whatsapp">
                        <div class="btn btn-light rounded-circle mb-2" style="width: 60px; height: 60px;">
                            <i class="fab fa-whatsapp fa-2x text-success" style="line-height: 60px;"></i>
                        </div>
                        <p class="mb-0">WhatsApp</p>
                    </a>
                    <a href="#" class="text-decoration-none text-center share-option" data-type="facebook">
                        <div class="btn btn-light rounded-circle mb-2" style="width: 60px; height: 60px;">
                            <i class="fab fa-facebook fa-2x text-primary" style="line-height: 60px;"></i>
                        </div>
                        <p class="mb-0">Facebook</p>
                    </a>
                    <a href="#" class="text-decoration-none text-center share-option" data-type="twitter">
                        <div class="btn btn-light rounded-circle mb-2" style="width: 60px; height: 60px;">
                            <i class="fab fa-twitter fa-2x text-info" style="line-height: 60px;"></i>
                        </div>
                        <p class="mb-0">Twitter</p>
                    </a>
                </div>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary share-option" data-type="copy">
                        <i class="far fa-copy me-2"></i> Salin Tautan
                    </button>
                    
                    <?php if (isLoggedIn()): ?>
                        <button class="btn btn-primary share-option" data-type="internal">
                            <i class="fas fa-retweet me-2"></i> Bagikan di Timeline
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 