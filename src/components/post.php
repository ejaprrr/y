<?php

require_once '../../src/functions/like.php';

function render_post($post, $conn) {
?>
<div class="card mb-3 rounded-4" data-post-id="<?= $post['id'] ?>">
<div class="card-body p-3">
    <div class="d-flex mb-2">
        <div class="rounded-circle bg-light me-2" style="width: 40px; height: 40px; overflow: hidden;"></div>
        <div>
            <div class="fw-bold text-lowercase"><?= htmlspecialchars($post['username']) ?></div>
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