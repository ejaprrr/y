<?php
function render_left_sidebar($user) {
?>
<div class="left-sidebar">
    <!-- Logo -->
    <div class="p-3 text-center">
        <img src="../assets/logo.svg" alt="logo" class="logo-svg w-75 mb-3">
    </div>
    
    <!-- Navigation -->
    <nav class="mb-4 px-3">
        <div class="d-flex flex-column gap-3">
            <div>
                <a href="index.php" class="d-flex align-items-center text-decoration-none text-white fs-5 py-2 px-3 rounded-3 hover-highlight active">
                    <i class="bi bi-house-fill me-3"></i>
                    feed
                </a>
            </div>
            <div>
                <a href="#" class="d-flex align-items-center text-decoration-none text-white fs-5 py-2 px-3 rounded-3 hover-highlight">
                    <i class="bi bi-search me-3"></i>
                    explore
                </a>
            </div>
            <div>
                <a href="#" class="d-flex align-items-center text-decoration-none text-white fs-5 py-2 px-3 rounded-3 hover-highlight">
                    <i class="bi bi-person-fill me-3"></i>
                    profile
                </a>
            </div>
            <div>
                <a href="#" class="d-flex align-items-center text-decoration-none text-white fs-5 py-2 px-3 rounded-3 hover-highlight">
                    <i class="bi bi-bell-fill me-3"></i>
                    notifications
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Post Button -->
    <div class="mb-4 px-3">
        <button class="btn btn-primary rounded-3 w-100 py-2 fw-semibold text-lowercase">post</button>
    </div>
    
    <!-- User Profile -->
    <div class="mt-auto p-3">
        <div class="d-flex align-items-center p-3 rounded-3" style="background-color: var(--gray-800);">
            <div class="rounded-circle bg-light me-2" style="width: 40px; height: 40px; overflow: hidden;"></div>
            <div>
                <div class="fw-bold text-lowercase"><?= htmlspecialchars($user["display_name"] ?? $user["username"]) ?></div>
                <small>@<?= htmlspecialchars($user["username"]) ?></small>
            </div>
            <div class="ms-auto">
                <a href="../auth/log-out.php" class="text-decoration-none logout-link">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
}
?>
