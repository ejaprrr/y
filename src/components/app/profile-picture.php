<?php
function render_profile_picture($user) {
?>
<div class="profile-picture overflow-hidden rounded-circle me-3">
    <?php if ($user["profile_picture"]): ?>
        <img src="<?= htmlspecialchars($user["profile_picture"]) ?>" alt="profile picture" class="w-100 h-100 object-fit-cover">
    <?php else: ?>
        <div class="bg-light w-100 h-100 d-flex align-items-center justify-content-center">
            <i class="default-profile-picture bi bi-person-fill"></i>
        </div>
    <?php endif; ?>
</div>

<?php
}
?>