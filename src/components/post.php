<?php

require_once '../../src/functions/like.php';
require_once '../../src/functions/user.php';

function render_post($post, $conn) {
    $post_user = get_user_by_username($conn, $post['username']);
?>
<div class="card mb-3 rounded-4" data-post-id="<?= $post['id'] ?>">
<div class="card-body p-3">
    <div class="d-flex mb-2">
        <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>" class="text-decoration-none">
            <div class="rounded-circle me-2" style="width: 40px; height: 40px; overflow: hidden;">
                <?php if ($post_user && $post_user['profile_picture']): ?>
                    <img src="<?= htmlspecialchars($post_user['profile_picture']) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-light w-100 h-100 d-flex align-items-center justify-content-center">
                        <i class="bi bi-person-fill text-dark"></i>
                    </div>
                <?php endif; ?>
            </div>
        </a>
        <div>
            <a href="profile.php?username=<?= htmlspecialchars($post['username']) ?>" class="text-decoration-none text-white">
                <div class="fw-bold text-lowercase"><?= htmlspecialchars($post_user['display_name'] ?? $post['username']) ?></div>
            </a>
            <small>
                @<?= htmlspecialchars($post['username']) ?> · <span class="date-text"><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
            </small>
        </div>
    </div>
    <div class="mb-3">
        <?= nl2br(htmlspecialchars($post['content'])) ?>
    </div>
    <div class="d-flex align-items-center">
        <button class="like-btn hover-highlight d-flex align-items-center" data-liked="<?= has_liked($conn, $_SESSION['user_id'], $post['id']) ? '1' : '0' ?>">
            <i class="bi <?= has_liked($conn, $_SESSION['user_id'], $post['id']) ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i>
            <span class="like-count"><?= get_like_count($conn, $post['id']) ?></span>
        </button>
    </div>
</div>
</div>
<?php
}

?>