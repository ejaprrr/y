<?php

require_once '../../src/functions/like.php';
require_once '../../src/functions/user.php';
require_once '../../src/functions/helpers.php';
require_once '../../src/functions/hashtag.php';
require_once 'profile-picture.php';

function render_post($post, $conn) {
    $post_user = get_user_by_username($conn, $post['username']);
?>
<div class="card mb-3 rounded-4" data-post-id="<?= $post['id'] ?>">
<div class="p-3">
    <div class="d-flex mb-2">
        <!-- profile information + clickable link -->
        <!-- clickable profile picture -->
        <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>" class="text-decoration-none">
            <?php render_profile_picture($post_user); ?>
        </a>
        <div>
            <!-- clickable display name -->
            <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>" class="text-decoration-none">
                <div class="user-link fw-bold"><?= htmlspecialchars($post_user['display_name'] ?? $post['username']) ?></div>
            </a>
            <!-- clickable user name -->
            <small class="user-handle">
                @<?= htmlspecialchars($post['username']) ?> · <span><?= format_time_ago($post['created_at']) ?></span>
            </small>
        </div>
    </div>

    <!-- post content with hashtagsighlighted -->
    <div class="mb-3">
        <?= format_content_with_hashtags(nl2br(htmlspecialchars_decode($post['content']))) ?>
    </div>

    <!-- actions and stats -->
    <div class="d-flex">
        <button class="action-btn like-btn hover-highlight d-flex align-items-center" data-liked="<?= has_liked($conn, $_SESSION['user_id'], $post['id']) ? '1' : '0' ?>">
            <i class="bi <?= has_liked($conn, $_SESSION['user_id'], $post['id']) ? 'bi-heart-fill liked' : 'bi-heart' ?>"></i>
            <span class="like-count"><?= get_like_count($conn, $post['id']) ?></span>
        </button>
        <?php if ($post_user["id"] == $_SESSION["user_id"]): ?>
            <button class="action-btn delete-btn hover-highlight d-flex align-items-center ms-3" data-post-id="<?= $post['id'] ?>">
                <i class="bi bi-trash"></i>
            </button>
        <?php endif; ?>
    </div>
</div>
</div>
<?php
}

?>