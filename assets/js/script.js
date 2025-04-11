// Share Post
let currentSharePostId = null;

$('.share-btn').on('click', function() {
    currentSharePostId = $(this).data('post-id');
    $('#shareModal').modal('show');
});

$('.share-option').on('click', function(e) {
    e.preventDefault();
    
    const shareType = $(this).data('type');
    const postUrl = window.location.origin + '/social_media/pages/post.php?id=' + currentSharePostId;
    
    if (shareType === 'whatsapp') {
        window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(postUrl), '_blank');
    } else if (shareType === 'facebook') {
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(postUrl), '_blank');
    } else if (shareType === 'twitter') {
        window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(postUrl), '_blank');
    } else if (shareType === 'copy') {
        navigator.clipboard.writeText(postUrl).then(function() {
            alert('Tautan berhasil disalin!');
        });
    } else if (shareType === 'internal') {
        $.ajax({
            url: 'includes/share_post.php',
            type: 'POST',
            data: {
                post_id: currentSharePostId
            },
            success: function(response) {
                if (response.success) {
                    $('#shareModal').modal('hide');
                    alert('Post berhasil dibagikan ke timeline Anda!');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Gagal membagikan post. Silakan coba lagi.');
                }
            },
            error: function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            }
        });
    }
}); 