<?php
// Determine current page
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<div class="col-md-3 d-none d-md-flex flex-column p-0 position-sticky vh-100" style="top: 0;">
    <div class="sidebar-content p-4 d-flex flex-column h-100">
        <!-- App logo -->
        <div class="logo-container mb-4">
            <a href="/y/public/app/feed.php" class="text-decoration-none">
                <div class="logo fs-1 fw-bold text-primary">Y</div>
            </a>
        </div>
        
        <!-- Navigation - SIMPLIFIED, no cards -->
        <nav class="mb-4">
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a class="nav-link rounded-3 px-4 py-3 <?php echo ($current_page === 'feed.php') ? 'active fw-semibold' : 'text-dark'; ?> d-flex align-items-center gap-3 fs-5 hover-bg-light" href="/y/public/app/feed.php">
                        <?php if ($current_page === 'feed.php'): ?>
                            <i class="bi bi-house-fill"></i>
                        <?php else: ?>
                            <i class="bi bi-house"></i>
                        <?php endif; ?>
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-3 px-4 py-3 <?php echo ($current_page === 'profile.php') ? 'active fw-semibold' : 'text-dark'; ?> d-flex align-items-center gap-3 fs-5 hover-bg-light" href="/y/public/app/profile.php?username=<?php echo $user['user_name']; ?>">
                        <?php if ($current_page === 'profile.php'): ?>
                            <i class="bi bi-person-fill"></i>
                        <?php else: ?>
                            <i class="bi bi-person"></i>
                        <?php endif; ?>
                        Profile
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Post button - SIMPLIFIED, no card -->
        <div class="mb-4">
            <button class="btn btn-primary rounded-pill w-100 py-3 fw-semibold" 
                    onclick="window.location.href='/y/public/app/feed.php'">
                Post
            </button>
        </div>
        
        <!-- Spacer to push user profile to bottom -->
        <div class="flex-grow-1"></div>
        
        <!-- User profile with logout - KEEP the card as it's a content container -->
        <div class="card border-0 shadow-sm rounded-4 mt-auto">
            <div class="card-body p-0">
                <div class="user-profile p-3 rounded-4 d-flex align-items-center gap-3">
                    <div class="rounded-circle overflow-hidden flex-shrink-0" style="width: 48px; height: 48px;">
                        <?php if (!empty($user['profile_picture_url'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture_url']); ?>" 
                                alt="<?php echo htmlspecialchars($user['user_name']); ?>" 
                                class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex justify-content-center align-items-center h-100 bg-light rounded-circle">
                                <i class="bi bi-person-circle text-secondary" style="font-size: 1.8rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="user-info overflow-hidden">
                        <div class="fw-bold text-truncate"><?php echo htmlspecialchars($user['display_name'] ?? $user['user_name']); ?></div>
                        <div class="text-muted text-truncate">@<?php echo htmlspecialchars($user['user_name']); ?></div>
                    </div>
                    <div class="dropdown ms-auto">
                        <button class="btn btn-sm btn-link text-dark p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/y/public/app/edit_profile.php">Edit profile</a></li>
                        </ul>
                    </div>
                </div>
                <!-- Sign out link now at the bottom of the card -->
                <a href="/y/public/auth/logout.php" class="nav-link border-top rounded-0 rounded-bottom-4 text-danger py-3 px-3 d-flex align-items-center gap-2 hover-bg-light">
                    <i class="bi bi-box-arrow-right"></i> Sign out
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Sidebar specific styles */
    .nav-link.active {
        background-color: rgba(29, 161, 242, 0.1);
        color: #1da1f2 !important;
    }
    
    .hover-bg-light:hover {
        background-color: rgba(0, 0, 0, 0.03);
        cursor: pointer;
    }
    
    .logo {
        font-weight: 700;
        color: #1da1f2;
    }
</style>