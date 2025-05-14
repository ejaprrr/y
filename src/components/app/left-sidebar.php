<?php
include_once "profile-picture.php";
function render_left_sidebar($user) {
?>
<div class="left-sidebar">
    <!-- logo -->
    <div class="p-4">
        <img src="../assets/logo.svg" alt="logo" class="logo-svg">
    </div>
    
    <!-- navigation -->
    <nav class="px-3">
        <div class="d-flex flex-column gap-3">
            <div>
                <a href="feed.php" class="d-flex align-items-center text-decoration-none fs-5 py-2 px-3 rounded-3 hover-highlight <?= basename($_SERVER['PHP_SELF']) === 'feed.php' ? 'active' : '' ?>">
                    <i class="bi bi-house-fill me-3"></i>
                    feed
                </a>
            </div>
            <div>
                <a href="profile.php" class="d-flex align-items-center text-decoration-none fs-5 py-2 px-3 rounded-3 hover-highlight <?= basename($_SERVER['PHP_SELF']) === 'profile.php' || basename($_SERVER['PHP_SELF']) === 'edit-profile.php' ? 'active' : '' ?>">
                    <i class="bi bi-person-fill me-3"></i>
                    profile
                </a>
            </div>
            <div>
                <a href="search.php" class="d-flex align-items-center text-decoration-none fs-5 py-2 px-3 rounded-3 hover-highlight <?= basename($_SERVER['PHP_SELF']) === 'search.php' ? 'active' : '' ?>">
                    <i class="bi bi-search me-3"></i>
                    search
                </a>
            </div>
        </div>
    </nav>
    
    <!-- user profile -->
    <div class="mt-auto p-3">
        <div class="d-flex align-items-center p-3 rounded-3">
            <!-- profile picture -->
            <?php render_profile_picture($user); ?>
            <div>
                <div class="fw-bold"><?= htmlspecialchars($user["display_name"] ?? $user["username"]) ?></div>
                <small>@<?= htmlspecialchars($user["username"]) ?></small>
            </div>
            <div class="ms-auto">
                <a href="../auth/log-out.php" class="text-decoration-none logout-link">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
}
?>
