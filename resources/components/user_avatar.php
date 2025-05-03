<?php
/**
 * Reusable user avatar component
 * @param string $profile_picture_url URL to user's profile picture
 * @param string $username Username for alt text
 * @param string $size Size in pixels (default: 48)
 * @param string $icon_size Size of fallback icon (default: 2rem)
 */

// Initialize defaults
$size = $size ?? '48';
$icon_size = $icon_size ?? '2rem';
?>

<div class="rounded-circle overflow-hidden" style="width: <?php echo $size; ?>px; height: <?php echo $size; ?>px; background-color: #f8f9fa;">
    <?php if (!empty($profile_picture_url)): ?>
        <img src="<?php echo htmlspecialchars($profile_picture_url); ?>" 
             alt="<?php echo htmlspecialchars($username); ?>" 
             class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
    <?php else: ?>
        <div class="d-flex justify-content-center align-items-center h-100">
            <i class="bi bi-person-circle text-secondary" style="font-size: <?php echo $icon_size; ?>;"></i>
        </div>
    <?php endif; ?>
</div>