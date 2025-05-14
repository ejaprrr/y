<?php

function render_empty_state($icon, $message, $description = "") {
?>
<div class="text-center my-5 py-5 empty-state">
    <i class="bi bi-<?= htmlspecialchars($icon) ?> fs-1"></i>
    <p class="fw-medium mb-1"><?= htmlspecialchars($message) ?></p>
    
    <?php if (!empty($description)): ?>
        <p class="small mb-3"><?= htmlspecialchars($description) ?></p>
    <?php endif; ?>
</div>
<?php
}
?>