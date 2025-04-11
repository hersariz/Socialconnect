<?php
require_once '../includes/functions.php';

// Mengambil query pencarian dari URL
$search_query = isset($_GET['q']) ? clean_input($_GET['q']) : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'all';

$users = [];
$posts = [];
$hashtags = [];

if (!empty($search_query)) {
    global $conn;
    
    // Pencarian pengguna
    if ($search_type == 'all' || $search_type == 'users') {
        $user_query = "SELECT * FROM users 
                     WHERE username LIKE '%$search_query%' 
                     OR bio LIKE '%$search_query%'
                     OR location LIKE '%$search_query%'
                     LIMIT 20";
        
        $user_result = mysqli_query($conn, $user_query);
        
        if ($user_result) {
            while ($row = mysqli_fetch_assoc($user_result)) {
                $users[] = $row;
            }
        }
    }
    
    // Pencarian post
    if ($search_type == 'all' || $search_type == 'posts') {
        $post_query = "SELECT posts.*, users.username, users.profile_pic
                     FROM posts 
                     JOIN users ON posts.user_id = users.id
                     WHERE posts.content LIKE '%$search_query%'
                     ORDER BY posts.created_at DESC
                     LIMIT 30";
        
        $post_result = mysqli_query($conn, $post_query);
        
        if ($post_result) {
            while ($row = mysqli_fetch_assoc($post_result)) {
                $posts[] = $row;
            }
        }
    }
    
    // Pencarian hashtag
    if ($search_type == 'all' || $search_type == 'hashtags') {
        // Jika pencarian dimulai dengan #, hapus
        $hashtag_search = $search_query;
        if (substr($hashtag_search, 0, 1) === '#') {
            $hashtag_search = substr($hashtag_search, 1);
        }
        
        $hashtag_query = "SELECT DISTINCT
                          SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(content, '#', n.n), ' ', 1), '\n', 1) AS hashtag,
                          COUNT(*) AS post_count
                          FROM posts p
                          JOIN (
                              SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
                          ) n ON CHAR_LENGTH(content) - CHAR_LENGTH(REPLACE(content, '#', '')) >= n.n - 1
                          WHERE content REGEXP '#[a-zA-Z0-9]+'
                          AND SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(content, '#', n.n), ' ', 1), '\n', 1) LIKE '%$hashtag_search%'
                          GROUP BY hashtag
                          ORDER BY post_count DESC
                          LIMIT 10";
        
        $hashtag_result = mysqli_query($conn, $hashtag_query);
        
        if ($hashtag_result) {
            while ($row = mysqli_fetch_assoc($hashtag_result)) {
                if (!empty($row['hashtag'])) {
                    $hashtags[] = $row;
                }
            }
        }
    }
}

$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form action="search.php" method="GET" class="mb-0">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari pengguna, postingan, atau #hashtag..." required>
                            <select name="type" class="form-select" style="max-width: 150px;">
                                <option value="all" <?php echo $search_type == 'all' ? 'selected' : ''; ?>>Semua</option>
                                <option value="users" <?php echo $search_type == 'users' ? 'selected' : ''; ?>>Pengguna</option>
                                <option value="posts" <?php echo $search_type == 'posts' ? 'selected' : ''; ?>>Postingan</option>
                                <option value="hashtags" <?php echo $search_type == 'hashtags' ? 'selected' : ''; ?>>Hashtag</option>
                            </select>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-1"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($search_query)): ?>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($search_type == 'all' || $search_type == 'users') ? 'active' : ''; ?>" 
                           href="<?php echo ($search_type == 'all') ? '#users' : 'search.php?q=' . urlencode($search_query) . '&type=users'; ?>"
                           <?php echo ($search_type == 'all') ? 'data-bs-toggle="tab"' : ''; ?>>
                            Pengguna (<?php echo count($users); ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($search_type == 'posts') ? 'active' : ''; ?>"
                           href="<?php echo ($search_type == 'all') ? '#posts' : 'search.php?q=' . urlencode($search_query) . '&type=posts'; ?>"
                           <?php echo ($search_type == 'all') ? 'data-bs-toggle="tab"' : ''; ?>>
                            Postingan (<?php echo count($posts); ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($search_type == 'hashtags') ? 'active' : ''; ?>"
                           href="<?php echo ($search_type == 'all') ? '#hashtags' : 'search.php?q=' . urlencode($search_query) . '&type=hashtags'; ?>"
                           <?php echo ($search_type == 'all') ? 'data-bs-toggle="tab"' : ''; ?>>
                            Hashtag (<?php echo count($hashtags); ?>)
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Users Tab -->
                    <div class="tab-pane fade <?php echo ($search_type == 'all' || $search_type == 'users') ? 'show active' : ''; ?>" id="users">
                        <?php if (count($users) > 0): ?>
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($users as $user): ?>
                                            <div class="list-group-item d-flex align-items-center">
                                                <a href="profile.php?id=<?php echo $user['id']; ?>" class="d-flex align-items-center text-decoration-none flex-grow-1">
                                                    <img src="<?php echo $user['profile_pic'] ? '../assets/uploads/' . $user['profile_pic'] : getDefaultAvatar(); ?>" 
                                                         class="rounded-circle me-3" width="50" height="50" alt="<?php echo $user['username']; ?>">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $user['username']; ?></h6>
                                                        <?php if (!empty($user['bio'])): ?>
                                                            <p class="text-muted small mb-0"><?php echo substr($user['bio'], 0, 100); ?><?php echo (strlen($user['bio']) > 100) ? '...' : ''; ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($user['location'])): ?>
                                                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $user['location']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                                
                                                <?php if (isLoggedIn() && $_SESSION['user_id'] != $user['id']): ?>
                                                    <?php $is_following = isFollowing($_SESSION['user_id'], $user['id']); ?>
                                                    <button class="btn <?php echo $is_following ? 'btn-outline-primary' : 'btn-primary'; ?> btn-sm follow-btn" data-user-id="<?php echo $user['id']; ?>">
                                                        <?php echo $is_following ? 'Mengikuti' : 'Ikuti'; ?>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="far fa-user fa-3x text-muted mb-3"></i>
                                    <h5>Tidak ada pengguna ditemukan</h5>
                                    <p class="text-muted">Coba gunakan kata kunci yang berbeda</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Posts Tab -->
                    <div class="tab-pane fade <?php echo ($search_type == 'posts') ? 'show active' : ''; ?>" id="posts">
                        <?php if (count($posts) > 0): ?>
                            <?php foreach ($posts as $post): ?>
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
                                        <p><?php 
                                            // Highlight search term dan buat hashtag klickable
                                            $highlighted_content = highlightSearchTerm($post['content'], $search_query);
                                            echo nl2br(linkHashtags($highlighted_content)); 
                                        ?></p>
                                        
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="../assets/uploads/<?php echo $post['image']; ?>" class="post-image img-fluid rounded" alt="Post image">
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
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="far fa-newspaper fa-3x text-muted mb-3"></i>
                                    <h5>Tidak ada postingan ditemukan</h5>
                                    <p class="text-muted">Coba gunakan kata kunci yang berbeda</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Hashtags Tab -->
                    <div class="tab-pane fade <?php echo ($search_type == 'hashtags') ? 'show active' : ''; ?>" id="hashtags">
                        <?php if (count($hashtags) > 0): ?>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($hashtags as $hashtag): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-3 me-3">
                                                        <i class="fas fa-hashtag text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <a href="search.php?q=%23<?php echo $hashtag['hashtag']; ?>&type=posts" class="text-decoration-none">
                                                            <h6 class="mb-0">#<?php echo $hashtag['hashtag']; ?></h6>
                                                        </a>
                                                        <small class="text-muted"><?php echo $hashtag['post_count']; ?> postingan</small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-hashtag fa-3x text-muted mb-3"></i>
                                    <h5>Tidak ada hashtag ditemukan</h5>
                                    <p class="text-muted">Coba gunakan kata kunci yang berbeda</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>Cari di SocialConnect</h4>
                        <p class="text-muted">Temukan pengguna, postingan, dan hashtag yang Anda cari</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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

<?php
// Fungsi untuk menyoroti kata pencarian dalam teks
function highlightSearchTerm($text, $term) {
    if (empty($term)) return $text;
    
    return preg_replace('/(' . preg_quote($term, '/') . ')/i', '<span class="highlight bg-warning">$1</span>', $text);
}
?>

<?php include '../includes/footer.php'; ?> 