<?php
function render_post($user_name, $content, $created_at) {
    ?>
    <div class="post">
        <p><strong><?= htmlspecialchars($user_name) ?></strong> (<?= $created_at ?>):</p>
        <p><?= nl2br(htmlspecialchars($content)) ?></p>
        <hr>
    </div>
    <?php
}
?>