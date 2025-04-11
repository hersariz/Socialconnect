<?php
require_once '../includes/functions.php';

// Pastikan user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$contacts = getUserContacts($user_id);

// Cek apakah ada ID kontak yang dipilih
$selected_contact_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selected_contact = null;
$messages = [];

if ($selected_contact_id > 0) {
    $selected_contact = getUserById($selected_contact_id);
    
    if ($selected_contact) {
        $messages = getMessagesBetweenUsers($user_id, $selected_contact_id);
    }
}

// Proses pengiriman pesan baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message']) && $selected_contact_id > 0) {
    $message = clean_input($_POST['message']);
    
    if (!empty($message)) {
        if (sendMessage($user_id, $selected_contact_id, $message)) {
            // Refresh pesan setelah berhasil mengirim
            $messages = getMessagesBetweenUsers($user_id, $selected_contact_id);
        }
    }
}

$relative_path = '../';
require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pesan</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (count($contacts) > 0): ?>
                            <?php foreach ($contacts as $contact): ?>
                                <a href="messages.php?id=<?php echo $contact['id']; ?>" 
                                   class="list-group-item list-group-item-action d-flex align-items-center <?php echo ($selected_contact_id == $contact['id']) ? 'active' : ''; ?>">
                                    <div class="position-relative me-3">
                                        <img src="<?php echo $contact['profile_pic'] ? '../assets/uploads/' . $contact['profile_pic'] : getDefaultAvatar(); ?>" 
                                             class="rounded-circle" width="50" height="50" alt="<?php echo $contact['username']; ?>">
                                        
                                        <?php if ($contact['unread_count'] > 0): ?>
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                <?php echo $contact['unread_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?php echo $contact['username']; ?></h6>
                                        <?php if (!empty($contact['last_message'])): ?>
                                            <p class="text-truncate mb-0 small <?php echo ($contact['last_message']['sender_id'] == $user_id) ? 'text-muted' : ''; ?>">
                                                <?php if ($contact['last_message']['sender_id'] == $user_id): ?>
                                                    <i class="fas fa-check-double me-1 <?php echo $contact['last_message']['is_read'] ? 'text-primary' : ''; ?>"></i>
                                                <?php endif; ?>
                                                <?php echo $contact['last_message']['message']; ?>
                                            </p>
                                            <small class="text-muted"><?php echo timeAgo($contact['last_message']['created_at']); ?></small>
                                        <?php else: ?>
                                            <p class="text-muted mb-0 small">Belum ada pesan</p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center py-5">
                                <p class="mb-0 text-muted">Belum ada percakapan</p>
                                <small class="text-muted">Pilih teman di halaman profile untuk memulai percakapan</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <?php if ($selected_contact): ?>
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-center">
                            <a href="profile.php?id=<?php echo $selected_contact['id']; ?>" class="text-decoration-none">
                                <img src="<?php echo $selected_contact['profile_pic'] ? '../assets/uploads/' . $selected_contact['profile_pic'] : getDefaultAvatar(); ?>" 
                                     class="rounded-circle me-2" width="40" height="40" alt="<?php echo $selected_contact['username']; ?>">
                            </a>
                            <div>
                                <a href="profile.php?id=<?php echo $selected_contact['id']; ?>" class="text-decoration-none">
                                    <h6 class="mb-0"><?php echo $selected_contact['username']; ?></h6>
                                </a>
                                <?php if (isset($selected_contact['last_activity'])): ?>
                                    <small class="text-muted">
                                        <?php
                                        $last_active = strtotime($selected_contact['last_activity']);
                                        $now = time();
                                        $diff = $now - $last_active;
                                        
                                        if ($diff < 300) { // 5 menit
                                            echo 'Online';
                                        } else {
                                            echo 'Terakhir aktif ' . timeAgo($selected_contact['last_activity']);
                                        }
                                        ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body chat-body" style="height: 400px; overflow-y: auto;">
                        <?php if (count($messages) > 0): ?>
                            <div class="chat-messages">
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message-item mb-3 <?php echo ($msg['sender_id'] == $user_id) ? 'text-end' : ''; ?>">
                                        <?php if ($msg['sender_id'] != $user_id): ?>
                                            <div class="d-flex align-items-start">
                                                <img src="<?php echo $selected_contact['profile_pic'] ? '../assets/uploads/' . $selected_contact['profile_pic'] : getDefaultAvatar(); ?>" 
                                                     class="rounded-circle me-2" width="30" height="30" alt="<?php echo $selected_contact['username']; ?>">
                                                <div>
                                                    <div class="message-bubble other-message">
                                                        <?php echo nl2br($msg['message']); ?>
                                                    </div>
                                                    <small class="text-muted"><?php echo timeAgo($msg['created_at']); ?></small>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <div class="message-bubble my-message">
                                                    <?php echo nl2br($msg['message']); ?>
                                                </div>
                                                <div>
                                                    <small class="text-muted me-2"><?php echo timeAgo($msg['created_at']); ?></small>
                                                    <small class="<?php echo $msg['is_read'] ? 'text-primary' : 'text-muted'; ?>">
                                                        <i class="fas fa-check-double"></i>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="far fa-comments fa-3x text-muted mb-3"></i>
                                <h5>Belum ada pesan</h5>
                                <p class="text-muted">Mulai percakapan dengan <?php echo $selected_contact['username']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <form method="POST" action="messages.php?id=<?php echo $selected_contact_id; ?>">
                            <div class="input-group">
                                <input type="text" class="form-control" name="message" placeholder="Ketik pesan..." required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane"></i> Kirim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="far fa-comments fa-4x text-muted mb-3"></i>
                        <h4>Selamat datang di Pesan</h4>
                        <p class="text-muted">Pilih percakapan atau mulai pesan baru dengan teman</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.message-bubble {
    max-width: 75%;
    padding: 10px 15px;
    border-radius: 18px;
    display: inline-block;
    margin-bottom: 2px;
    word-wrap: break-word;
}

.my-message {
    background-color: #dcf8c6;
    border-top-right-radius: 5px;
    text-align: left;
}

.other-message {
    background-color: #f1f0f0;
    border-top-left-radius: 5px;
}

.chat-body {
    display: flex;
    flex-direction: column-reverse;
}

.chat-messages {
    display: flex;
    flex-direction: column;
}
</style>

<script>
// Scroll to bottom of chat on page load
document.addEventListener('DOMContentLoaded', function() {
    const chatBody = document.querySelector('.chat-body');
    if (chatBody) {
        chatBody.scrollTop = chatBody.scrollHeight;
    }
});
</script>

<?php include '../includes/footer.php'; ?> 