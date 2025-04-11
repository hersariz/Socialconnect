<?php
require_once '../includes/functions.php';

// Fungsi untuk mendapatkan random posts
function getExplorePosts($limit = 50) {
    global $conn;
    
    $limit = (int)$limit;
    $query = "SELECT posts.*, users.username, users.profile_pic
              FROM posts 
              JOIN users ON posts.user_id = users.id
              WHERE posts.image IS NOT NULL
              ORDER BY RAND()
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    $posts = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

// Ambil post acak untuk eksplorasi
$explore_posts = getExplorePosts();

$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Jelajah</h5>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Filter
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?filter=trending">Trending</a></li>
                        <li><a class="dropdown-item" href="?filter=recent">Terbaru</a></li>
                        <li><a class="dropdown-item" href="?filter=popular">Populer</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <?php if (count($explore_posts) > 0): ?>
                    <div class="row g-3">
                        <?php foreach ($explore_posts as $post): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="card position-relative h-100">
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="text-decoration-none">
                                        <img src="<?php echo $post['image'] ? '../assets/uploads/' . $post['image'] : '../assets/img/default-post.png'; ?>" 
                                             class="card-img-top" style="height: 200px; object-fit: cover;" alt="Post image">
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <span class="badge bg-dark">
                                                <i class="fas fa-heart me-1"></i> <?php echo getPostLikes($post['id']); ?>
                                            </span>
                                        </div>
                                        <div class="position-absolute top-0 start-0 p-2">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $post['profile_pic'] ? '../assets/uploads/' . $post['profile_pic'] : getDefaultAvatar(); ?>" 
                                                     class="rounded-circle me-1" width="25" height="25" alt="<?php echo $post['username']; ?>">
                                                <span class="badge bg-dark opacity-75"><?php echo $post['username']; ?></span>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="card-body">
                                        <p class="card-text text-truncate small"><?php echo $post['content']; ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><?php echo timeAgo($post['created_at']); ?></small>
                                            <div>
                                                <i class="far fa-comment me-1"></i> <?php echo getPostCommentCount($post['id']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <h5>Tidak ada post untuk dijelajahi</h5>
                        <p class="text-muted">Silakan coba lagi nanti.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Trending Users -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pengguna Populer</h5>
            </div>
            <div class="card-body p-0">
                <?php
                // Get users with most followers
                $query = "SELECT users.*, COUNT(follows.follower_id) as follower_count
                          FROM users
                          LEFT JOIN follows ON users.id = follows.following_id
                          GROUP BY users.id
                          ORDER BY follower_count DESC
                          LIMIT 10";
                $result = mysqli_query($conn, $query);
                $popular_users = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $popular_users[] = $row;
                }
                ?>
                
                <div class="row g-0">
                    <?php foreach ($popular_users as $user): ?>
                        <div class="col-md-6 border-bottom border-end p-3">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $user['profile_pic'] ? '../assets/uploads/' . $user['profile_pic'] : getDefaultAvatar(); ?>" 
                                     class="rounded-circle me-3" width="50" height="50" alt="<?php echo $user['username']; ?>">
                                <div>
                                    <a href="profile.php?id=<?php echo $user['id']; ?>" class="text-decoration-none">
                                        <h6 class="mb-0"><?php echo $user['username']; ?></h6>
                                    </a>
                                    <small class="text-muted"><?php echo $user['follower_count']; ?> pengikut</small>
                                    <p class="mb-0 small text-truncate" style="max-width: 250px;">
                                        <?php echo $user['bio'] ? $user['bio'] : 'Belum ada bio.'; ?>
                                    </p>
                                </div>
                                <?php if (isLoggedIn() && $_SESSION['user_id'] != $user['id']): ?>
                                    <?php $is_following = isFollowing($_SESSION['user_id'], $user['id']); ?>
                                    <button class="btn btn-sm <?php echo $is_following ? 'btn-outline-primary' : 'btn-primary'; ?> ms-auto follow-btn" data-user-id="<?php echo $user['id']; ?>">
                                        <?php echo $is_following ? 'Mengikuti' : 'Ikuti'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Trending Hashtags -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Hashtag Trending</h5>
            </div>
            <div class="card-body p-0">
                <?php
                // Get trending hashtags
                $query = "SELECT * FROM trending_topics LIMIT 10";
                $result = mysqli_query($conn, $query);
                $hashtags = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $hashtags[] = $row;
                }
                ?>
                
                <div class="row g-0">
                    <?php if (count($hashtags) > 0): ?>
                        <?php foreach ($hashtags as $tag): ?>
                            <div class="col-md-6 border-bottom border-end p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">#<?php echo $tag['hashtag']; ?></h6>
                                        <small class="text-muted"><?php echo $tag['post_count']; ?> post</small>
                                    </div>
                                    <a href="search.php?q=%23<?php echo $tag['hashtag']; ?>" class="btn btn-sm btn-outline-primary">Lihat</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 text-center">
                            <p class="mb-0 text-muted">Belum ada hashtag trending.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 