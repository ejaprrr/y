<?php

require_once '../../src/functions/like.php';

function render_post($post, $current_user_id, $conn) {
    $user_name = $post['user_name'];
    $content = $post['content'];
    $created_at = $post['created_at'];
    $post_id = $post['id'];
    $like_count = get_like_count($conn, $post_id);
    $liked = has_liked($conn, $current_user_id, $post_id);
?>
<div class="post" data-post-id="<?= $post_id ?>">
    <p><strong><?= $user_name ?></strong> (<?= $created_at ?>):</p>
    <p><?= nl2br(htmlspecialchars($content)) ?></p>
    <button class="like-btn" data-liked="<?= $liked ? '1' : '0' ?>">
        <?= $liked ? 'Unlike' : 'Like' ?> (<?= $like_count ?>)
    </button>
    <hr>
</div>
<?php
}

?>