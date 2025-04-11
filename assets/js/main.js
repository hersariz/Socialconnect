$(document).ready(function() {
    // Deteksi apakah kita berada di direktori root atau subdirektori
    const basePath = window.location.pathname.includes('/pages/') ? '../' : '';
    
    // Like post handling
    $('.like-btn').on('click', function() {
        const postId = $(this).data('post-id');
        const likeCount = $(this).find('span');
        const likeIcon = $(this).find('i');
        
        $.ajax({
            url: basePath + 'includes/like_post.php',
            type: 'POST',
            data: {post_id: postId},
            success: function(response) {
                const data = JSON.parse(response);
                
                if (data.success) {
                    // Update like count and toggle liked class
                    likeCount.text(data.likes);
                    
                    if (data.liked) {
                        likeIcon.removeClass('far').addClass('fas');
                        $(this).addClass('liked');
                    } else {
                        likeIcon.removeClass('fas').addClass('far');
                        $(this).removeClass('liked');
                    }
                }
            }.bind(this),
            error: function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });
    
    // Comment form submission
    $('.comment-form').on('submit', function(e) {
        e.preventDefault();
        
        const postId = $(this).data('post-id');
        const commentInput = $(this).find('.comment-input');
        const commentSection = $(this).siblings('.comments-container');
        
        if (commentInput.val().trim() === '') {
            return;
        }
        
        $.ajax({
            url: basePath + 'includes/add_comment.php',
            type: 'POST',
            data: {
                post_id: postId,
                comment: commentInput.val()
            },
            success: function(response) {
                const data = JSON.parse(response);
                
                if (data.success) {
                    // Append new comment to comments container
                    const newComment = `
                        <div class="comment fade-in">
                            <img src="${data.profile_pic || 'https://ui-avatars.com/api/?name=User&background=random'}" class="comment-avatar" alt="${data.username}">
                            <div class="comment-content">
                                <h6 class="comment-username">${data.username}</h6>
                                <p class="comment-text">${data.comment}</p>
                                <small class="comment-time">Baru saja</small>
                            </div>
                        </div>
                    `;
                    
                    commentSection.append(newComment);
                    commentInput.val('');
                    
                    // Update comment count
                    const commentCount = $(`.comment-btn[data-post-id="${postId}"] span`);
                    commentCount.text(parseInt(commentCount.text()) + 1);
                }
            },
            error: function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });
    
    // Follow/Unfollow user
    $('.follow-btn').on('click', function() {
        const userId = $(this).data('user-id');
        const followBtn = $(this);
        
        $.ajax({
            url: basePath + 'includes/follow_user.php',
            type: 'POST',
            data: {user_id: userId},
            success: function(response) {
                const data = JSON.parse(response);
                
                if (data.success) {
                    if (data.following) {
                        followBtn.removeClass('btn-primary').addClass('btn-outline-primary');
                        followBtn.text('Mengikuti');
                    } else {
                        followBtn.removeClass('btn-outline-primary').addClass('btn-primary');
                        followBtn.text('Ikuti');
                    }
                    
                    // Update follower count
                    $('#followers-count').text(data.followers);
                }
            },
            error: function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    });
    
    // Toggle comment section
    $('.comment-btn').on('click', function() {
        const postId = $(this).data('post-id');
        $(`#comments-${postId}`).slideToggle();
    });
    
    // Post form character counter
    $('#post-content').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        const remainingChars = maxLength - currentLength;
        
        $('#char-count').text(remainingChars);
        
        if (remainingChars < 20) {
            $('#char-count').addClass('text-danger');
        } else {
            $('#char-count').removeClass('text-danger');
        }
    });
    
    // Image preview on post create
    $('#post-image').on('change', function() {
        const file = this.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result).show();
                $('.remove-image').show();
            }
            
            reader.readAsDataURL(file);
        }
    });
    
    // Remove image from post create
    $('.remove-image').on('click', function() {
        $('#post-image').val('');
        $('#image-preview').attr('src', '').hide();
        $(this).hide();
    });
    
    // Notification system
    function checkNotifications() {
        $.ajax({
            url: basePath + 'includes/check_notifications.php',
            type: 'GET',
            success: function(response) {
                const data = JSON.parse(response);
                
                if (data.new_notifications > 0) {
                    $('.notification-badge').text(data.new_notifications).show();
                } else {
                    $('.notification-badge').hide();
                }
            }
        });
    }
    
    // Check for new notifications every 30 seconds
    if ($('.notification-badge').length) {
        checkNotifications();
        setInterval(checkNotifications, 30000);
    }
    
    // Pengaturan share post
    let currentSharePostId = null;
    
    $('.share-btn').on('click', function() {
        currentSharePostId = $(this).data('post-id');
        $('#shareModal').modal('show');
    });
    
    // Tangani klik opsi berbagi
    $('.share-option').on('click', function(e) {
        e.preventDefault();
        
        if (!currentSharePostId) return;
        
        const shareType = $(this).data('type');
        
        $.ajax({
            url: basePath + 'includes/share_post.php',
            type: 'POST',
            data: {
                post_id: currentSharePostId,
                share_type: shareType
            },
            success: function(response) {
                const data = JSON.parse(response);
                
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
                    $('#shareModal').modal('hide');
                } else {
                    alert(data.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat berbagi post');
            }
        });
    });
}); 