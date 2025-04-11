<?php
require_once '../includes/functions.php';

// Cek ID post dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$post_id = (int)$_GET['id'];

// Mengambil data post
global $conn;
$query = "SELECT posts.*, users.username, users.profile_pic
          FROM posts 
          JOIN users ON posts.user_id = users.id
          WHERE posts.id = '$post_id'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ../index.php');
    exit;
}

$post = mysqli_fetch_assoc($result);

// Jika post memiliki original_post_id (share), ambil data post asli
if (!empty($post['original_post_id'])) {
    $original_post_id = $post['original_post_id'];
    $original_query = "SELECT posts.*, users.username, users.profile_pic
                     FROM posts 
                     JOIN users ON posts.user_id = users.id
                     WHERE posts.id = '$original_post_id'";
    
    $original_result = mysqli_query($conn, $original_query);
    if (mysqli_num_rows($original_result) > 0) {
        $original_post = mysqli_fetch_assoc($original_result);
    }
}

// Ambil komentar untuk post ini
$comments = getPostComments($post_id);

$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <a href="javascript:history.back()" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
            
            <div class="card post-card mb-4">
                <div class="post-header">
                    <img src="<?php echo $post['profile_pic'] ? '../assets/uploads/' . $post['profile_pic'] : getDefaultAvatar(); ?>" 
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
                    
                    <?php if (isset($original_post)): ?>
                        <div class="card mt-3 original-post">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="<?php echo $original_post['profile_pic'] ? '../assets/uploads/' . $original_post['profile_pic'] : getDefaultAvatar(); ?>" 
                                         class="rounded-circle me-2" width="30" height="30" alt="<?php echo $original_post['username']; ?>">
                                    <div>
                                        <a href="profile.php?id=<?php echo $original_post['user_id']; ?>" class="text-decoration-none">
                                            <h6 class="mb-0"><?php echo $original_post['username']; ?></h6>
                                        </a>
                                        <small class="text-muted"><?php echo timeAgo($original_post['created_at']); ?></small>
                                    </div>
                                </div>
                                <p><?php echo nl2br(linkHashtags($original_post['content'])); ?></p>
                                
                                <?php if (!empty($original_post['image'])): ?>
                                    <img src="../assets/uploads/<?php echo $original_post['image']; ?>" class="img-fluid rounded" alt="Original post image" style="max-height: 300px;">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="post-actions">
                    <?php $is_liked = isLoggedIn() ? isPostLikedByUser($post['id'], $_SESSION['user_id']) : false; ?>
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
                
                <div class="comment-section" id="comments-<?php echo $post['id']; ?>">
                    <h6 class="mt-3 mb-3">Komentar</h6>
                    <div class="comments-container">
                        <?php if (count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <img src="<?php echo $comment['profile_pic'] ? '../assets/uploads/' . $comment['profile_pic'] : getDefaultAvatar(); ?>" 
                                         class="comment-avatar" alt="<?php echo $comment['username']; ?>">
                                    <div class="comment-content">
                                        <h6 class="comment-username">
                                            <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="text-decoration-none">
                                                <?php echo $comment['username']; ?>
                                            </a>
                                        </h6>
                                        <p class="comment-text"><?php echo $comment['comment']; ?></p>
                                        <small class="comment-time"><?php echo timeAgo($comment['created_at']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted mb-3">
                                <p>Belum ada komentar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <form class="comment-form mt-3" data-post-id="<?php echo $post['id']; ?>">
                            <div class="d-flex">
                                <input type="text" class="form-control comment-input" placeholder="Tulis komentar...">
                                <button type="submit" class="btn btn-primary ms-2">Kirim</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="mt-3 text-center">
                            <a href="login.php" class="text-decoration-none">Masuk untuk mengomentari</a>
                        </div>
                    <?php endif; ?>
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

<script>
    // Variabel untuk menyimpan ID post yang akan dibagikan
    let currentSharePostId = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Tangani klik tombol bagikan
        document.querySelectorAll('.share-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                currentSharePostId = this.getAttribute('data-post-id');
                var shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
                shareModal.show();
            });
        });
        
        // Tangani klik opsi berbagi
        document.querySelectorAll('.share-option').forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!currentSharePostId) return;
                
                const shareType = this.getAttribute('data-type');
                
                // Kirim request AJAX ke server
                const formData = new FormData();
                formData.append('post_id', currentSharePostId);
                formData.append('share_type', shareType);
                
                fetch('../includes/share_post.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (shareType === 'internal') {
                            alert(data.message);
                            window.location.reload();
                        } else if (shareType === 'copy') {
                            // Salin URL ke clipboard
                            navigator.clipboard.writeText(data.url)
                                .then(() => alert('URL telah disalin ke clipboard'))
                                .catch(err => console.error('Gagal menyalin URL: ', err));
                        } else {
                            // Buka link share di jendela baru
                            window.open(data.url, '_blank');
                        }
                        
                        // Tutup modal
                        var shareModal = bootstrap.Modal.getInstance(document.getElementById('shareModal'));
                        shareModal.hide();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat berbagi post');
                });
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?> 