<?php
require_once '../includes/functions.php';

// Cek ID dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_GET['id'];
$user = getUserById($user_id);

// Jika user tidak ditemukan
if (!$user) {
    header('Location: ../index.php');
    exit;
}

// Mendapatkan post user
$user_posts = getUserPosts($user_id);

// Mengecek apakah logged-in user sudah follow user ini
$isFollowing = false;
if (isLoggedIn()) {
    $isFollowing = isFollowing($_SESSION['user_id'], $user_id);
}

$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-4">
        <!-- Profile Card -->
        <div class="card mb-4">
            <div class="card-body profile-card">
                <img src="<?php echo $user['profile_pic'] ? '../assets/uploads/' . $user['profile_pic'] : getDefaultAvatar(true); ?>" 
                     class="profile-avatar" alt="<?php echo $user['username']; ?>">
                <h5 class="profile-username"><?php echo $user['username']; ?></h5>
                
                <?php if (!empty($user['bio'])): ?>
                    <p class="profile-bio"><?php echo $user['bio']; ?></p>
                <?php else: ?>
                    <p class="profile-bio text-muted">Belum ada bio.</p>
                <?php endif; ?>
                
                <div class="profile-stats">
                    <div class="profile-stat">
                        <span class="profile-stat-value"><?php echo getUserPostCount($user_id); ?></span>
                        <span class="profile-stat-label">Post</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-value" id="followers-count"><?php echo getUserFollowerCount($user_id); ?></span>
                        <span class="profile-stat-label">Pengikut</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-value"><?php echo getUserFollowingCount($user_id); ?></span>
                        <span class="profile-stat-label">Mengikuti</span>
                    </div>
                </div>
                
                <?php if (isLoggedIn() && $_SESSION['user_id'] != $user_id): ?>
                    <button class="btn <?php echo $isFollowing ? 'btn-outline-primary' : 'btn-primary'; ?> w-100 follow-btn mt-3" data-user-id="<?php echo $user_id; ?>">
                        <?php echo $isFollowing ? 'Mengikuti' : 'Ikuti'; ?>
                    </button>
                    <a href="messages.php?id=<?php echo $user_id; ?>" class="btn btn-outline-primary w-100 mt-2">
                        <i class="far fa-envelope me-1"></i> Kirim Pesan
                    </a>
                <?php elseif (isLoggedIn() && $_SESSION['user_id'] == $user_id): ?>
                    <a href="edit_profile.php" class="btn btn-outline-primary w-100 mt-3">
                        <i class="far fa-edit me-1"></i> Edit Profil
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- About Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Tentang</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <?php if (!empty($user['location'])): ?>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i> <?php echo $user['location']; ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($user['website'])): ?>
                        <li class="mb-2">
                            <i class="fas fa-globe me-2 text-primary"></i>
                            <a href="<?php echo $user['website']; ?>" target="_blank"><?php echo $user['website']; ?></a>
                        </li>
                    <?php endif; ?>
                    <li class="mb-2">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i> Bergabung <?php echo date('F Y', strtotime($user['created_at'])); ?>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Photos Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Foto</h6>
                <a href="#" class="text-decoration-none">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <?php $user_photos = getUserPhotos($user_id, 9); ?>
                    <?php foreach ($user_photos as $photo): ?>
                        <div class="col-4">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#photoModal<?php echo $photo['id']; ?>">
                                <img src="../assets/uploads/<?php echo $photo['image']; ?>" class="img-fluid rounded" alt="User photo">
                            </a>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($user_photos) == 0): ?>
                        <div class="text-center text-muted py-3">
                            <p class="mb-0">Belum ada foto.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="#posts" data-bs-toggle="tab">Post</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#media" data-bs-toggle="tab">Media</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#likes" data-bs-toggle="tab">Suka</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#following" data-bs-toggle="tab">Mengikuti</a>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Posts Tab -->
            <div class="tab-pane fade show active" id="posts">
                <?php if (count($user_posts) > 0): ?>
                    <?php foreach ($user_posts as $post): ?>
                        <div class="card post-card mb-4">
                            <div class="post-header">
                                <img src="<?php echo $user['profile_pic'] ? '../assets/uploads/' . $user['profile_pic'] : getDefaultAvatar(true); ?>" 
                                     class="post-avatar" alt="<?php echo $user['username']; ?>">
                                <div>
                                    <h6 class="post-username"><?php echo $user['username']; ?></h6>
                                    <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <p><?php echo nl2br(linkHashtags($post['content'])); ?></p>
                                <?php if (!empty($post['image'])): ?>
                                    <img src="../assets/uploads/<?php echo $post['image']; ?>" class="post-image img-fluid rounded" alt="Post image">
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
                                            <img src="<?php echo $comment['profile_pic'] ? '../assets/uploads/' . $comment['profile_pic'] : getDefaultAvatar(true); ?>" 
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
                                        <a href="login.php" class="text-decoration-none">Masuk untuk mengomentari</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <h5>Belum ada post</h5>
                            <p class="text-muted">User ini belum membuat post apapun.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Media Tab -->
            <div class="tab-pane fade" id="media">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <?php $media_posts = getUserMediaPosts($user_id); ?>
                            
                            <?php if (count($media_posts) > 0): ?>
                                <?php foreach ($media_posts as $media): ?>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="position-relative">
                                            <img src="../assets/uploads/<?php echo $media['image']; ?>" class="img-fluid rounded" alt="Media">
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <span class="badge bg-dark">
                                                    <i class="fas fa-heart me-1"></i> <?php echo getPostLikes($media['id']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <h5>Belum ada media</h5>
                                    <p class="text-muted">User ini belum membagikan media apapun.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Likes Tab -->
            <div class="tab-pane fade" id="likes">
                <div class="card">
                    <div class="card-body">
                        <?php $liked_posts = getUserLikedPosts($user_id); ?>
                        
                        <?php if (count($liked_posts) > 0): ?>
                            <?php foreach ($liked_posts as $post): ?>
                                <div class="card post-card mb-4">
                                    <div class="post-header">
                                        <img src="<?php echo $post['profile_pic'] ? '../assets/uploads/' . $post['profile_pic'] : getDefaultAvatar(true); ?>" 
                                             class="post-avatar" alt="<?php echo $post['username']; ?>">
                                        <div>
                                            <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="text-decoration-none">
                                                <h6 class="post-username"><?php echo $post['username']; ?></h6>
                                            </a>
                                            <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="post-content">
                                        <p><?php echo nl2br(linkHashtags($post['content'])); ?></p>
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="../assets/uploads/<?php echo $post['image']; ?>" class="post-image img-fluid rounded" alt="Post image">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="post-actions">
                                        <button class="post-action-btn like-btn liked" data-post-id="<?php echo $post['id']; ?>">
                                            <i class="fas fa-heart"></i>
                                            <span><?php echo getPostLikes($post['id']); ?></span>
                                        </button>
                                        <button class="post-action-btn comment-btn" data-post-id="<?php echo $post['id']; ?>">
                                            <i class="far fa-comment"></i>
                                            <span><?php echo getPostCommentCount($post['id']); ?></span>
                                        </button>
                                        <button class="post-action-btn">
                                            <i class="far fa-share-square"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <h5>Belum ada suka</h5>
                                <p class="text-muted">User ini belum menyukai post apapun.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Following Tab -->
            <div class="tab-pane fade" id="following">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <?php $following = getUserFollowing($user_id); ?>
                            
                            <?php if (count($following) > 0): ?>
                                <?php foreach ($following as $follow): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $follow['profile_pic'] ? '../assets/uploads/' . $follow['profile_pic'] : getDefaultAvatar(true); ?>" 
                                                 class="rounded-circle me-3" width="50" height="50" alt="<?php echo $follow['username']; ?>">
                                            <div>
                                                <a href="profile.php?id=<?php echo $follow['id']; ?>" class="text-decoration-none">
                                                    <h6 class="mb-0"><?php echo $follow['username']; ?></h6>
                                                </a>
                                                <p class="text-muted mb-0"><?php echo $follow['bio'] ? substr($follow['bio'], 0, 50) . '...' : 'Belum ada bio.'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <h5>Belum mengikuti siapapun</h5>
                                    <p class="text-muted">User ini belum mengikuti siapapun.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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

<?php include '../includes/footer.php'; ?> 