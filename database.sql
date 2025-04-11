-- Membuat database
CREATE DATABASE IF NOT EXISTS social_media_db;
USE social_media_db;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    location VARCHAR(100) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel posts
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel likes
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel comments
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel follows
CREATE TABLE IF NOT EXISTS follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel notifikasi
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    from_user_id INT NOT NULL,
    type ENUM('like', 'comment', 'follow', 'mention') NOT NULL,
    post_id INT DEFAULT NULL,
    comment_id INT DEFAULT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Tabel pesan
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel aktivitas
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('post', 'like', 'comment', 'follow') NOT NULL,
    post_id INT DEFAULT NULL,
    target_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- View untuk trending topics (hashtags)
CREATE VIEW trending_topics AS
SELECT 
    SUBSTRING_INDEX(SUBSTRING_INDEX(content, '#', -1), ' ', 1) AS hashtag,
    COUNT(*) AS post_count
FROM posts
WHERE content LIKE '%#%'
GROUP BY hashtag
ORDER BY post_count DESC
LIMIT 10;

-- Indeks untuk meningkatkan kinerja
CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_likes_post_id ON likes(post_id);
CREATE INDEX idx_likes_user_id ON likes(user_id);
CREATE INDEX idx_comments_post_id ON comments(post_id);
CREATE INDEX idx_follows_follower_id ON follows(follower_id);
CREATE INDEX idx_follows_following_id ON follows(following_id);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_messages_sender_receiver ON messages(sender_id, receiver_id);

-- Insert data pengguna contoh (password: password123)
INSERT INTO users (username, email, password, bio) VALUES
('johndoe', 'john@example.com', '$2y$10$XQVmb4IvGmC0yT5o0KwbxO.g1yVpxBhH3eB.qtYAQ1bYI.5bwT.Cq', 'Seorang pengguna aktif media sosial'),
('janedoe', 'jane@example.com', '$2y$10$XQVmb4IvGmC0yT5o0KwbxO.g1yVpxBhH3eB.qtYAQ1bYI.5bwT.Cq', 'Suka berbagi cerita dan pengalaman'),
('bobsmith', 'bob@example.com', '$2y$10$XQVmb4IvGmC0yT5o0KwbxO.g1yVpxBhH3eB.qtYAQ1bYI.5bwT.Cq', 'Fotografer amatir dan pecinta alam');

-- Insert post contoh
INSERT INTO posts (user_id, content) VALUES
(1, 'Halo semua! Ini adalah post pertama saya di SocialConnect.'),
(2, 'Hari ini cuaca cerah sekali. Sangat cocok untuk berjalan-jalan.'),
(3, 'Bagikan pengalaman menarik Anda hari ini!'),
(1, 'Senang bisa bergabung dengan komunitas yang luar biasa ini!'),
(2, 'Siapa yang suka baca buku? Saya baru saja menyelesaikan novel yang bagus.');

-- Insert likes contoh
INSERT INTO likes (post_id, user_id) VALUES
(1, 2), (1, 3), (2, 1), (2, 3), (3, 1), (3, 2), (4, 2), (4, 3), (5, 1), (5, 3);

-- Insert comments contoh
INSERT INTO comments (post_id, user_id, comment) VALUES
(1, 2, 'Selamat datang, John!'),
(1, 3, 'Senang bertemu dengan Anda!'),
(2, 1, 'Setuju, cuaca hari ini memang indah.'),
(3, 2, 'Saya baru saja mencoba restoran baru yang enak di pusat kota.'),
(4, 3, 'Kami juga senang Anda bergabung!'),
(5, 1, 'Saya juga suka membaca. Apa judul novelnya?');

-- Insert follows contoh
INSERT INTO follows (follower_id, following_id) VALUES
(1, 2), (1, 3), (2, 1), (2, 3), (3, 1);

-- Insert activities contoh
INSERT INTO activities (user_id, activity_type, post_id, message) VALUES
(1, 'post', 1, 'membuat post baru'),
(2, 'post', 2, 'membuat post baru'),
(3, 'post', 3, 'membuat post baru'),
(1, 'post', 4, 'membuat post baru'),
(2, 'post', 5, 'membuat post baru'),
(1, 'like', 2, 'menyukai post dari janedoe'),
(2, 'comment', 1, 'mengomentari post dari johndoe'),
(3, 'follow', NULL, 'mulai mengikuti johndoe');

-- Tambahkan tabel trending_topics untuk fitur hashtag

CREATE TABLE IF NOT EXISTS `trending_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hashtag` varchar(255) NOT NULL,
  `post_count` int(11) NOT NULL DEFAULT 1,
  `last_used` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hashtag` (`hashtag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 