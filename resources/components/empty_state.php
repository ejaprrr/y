<?php
/**
 * Reusable empty state component
 * @param string $icon Bootstrap icon class without the 'bi-' prefix
 * @param string $title Title text
 * @param string $message Message text
 * @param string $icon_size Size of the icon (e.g., '3rem')
 */

// Default values
$icon = $icon ?? 'chat-square-text';
$title = $title ?? 'No content';
$message = $message ?? 'Nothing to display at this time.';
$icon_size = $icon_size ?? '3rem';
?>

<div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
    <div class="mb-4">
        <i class="bi bi-<?php echo $icon; ?>" style="font-size: <?php echo $icon_size; ?>;"></i>
    </div>
    <h5><?php echo htmlspecialchars($title); ?></h5>
    <p class="text-muted">
        <?php echo htmlspecialchars($message); ?>
    </p>
    <?php if (isset($secondary_message)): ?>
        <p class="small">
            <?php echo htmlspecialchars($secondary_message); ?>
        </p>
    <?php endif; ?>
</div>