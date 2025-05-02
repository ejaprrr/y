<?php
// Determine current page
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<div class="col-md-3 d-flex flex-column p-3 bg-light border-end position-sticky vh-100" style="top: 0;">
    <h1 class="fs-2 mb-4">Y</h1>
    <nav class="mb-auto">
        <ul class="nav nav-pills flex-column gap-2">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'feed.php') ? 'active bg-primary' : 'text-dark'; ?> d-flex align-items-center gap-2 fs-5" href="/y/public/app/feed.php">
                    <?php if ($current_page === 'feed.php'): ?>
                        <i class="bi bi-house-fill"></i>
                    <?php else: ?>
                        <i class="bi bi-house"></i>
                    <?php endif; ?>
                    Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'profile.php') ? 'active bg-primary' : 'text-dark'; ?> d-flex align-items-center gap-2 fs-5" href="/y/public/app/profile.php?username=<?php echo $user['user_name']; ?>">
                    <?php if ($current_page === 'profile.php'): ?>
                        <i class="bi bi-person-fill"></i>
                    <?php else: ?>
                        <i class="bi bi-person"></i>
                    <?php endif; ?>
                    Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark d-flex align-items-center gap-2 fs-5" href="/y/public/auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="mt-4 pt-3 border-top">
        <div class="d-flex align-items-center gap-2">
            <div class="fs-3"><i class="bi bi-person-circle"></i></div>
            <div>
                <div class="fw-bold"><?php echo htmlspecialchars($user['user_name']); ?></div>
                <div class="text-muted">@<?php echo htmlspecialchars($user['user_name']); ?></div>
            </div>
        </div>
    </div>
</div>