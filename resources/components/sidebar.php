<?php
// Determine current page
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<div class="col-md-3 position-sticky vh-100" style="top: 0;">
    <div class="sidebar-content p-3 h-100 d-flex flex-column">
        <!-- Y logo -->
        <div class="my-3">
            <a href="/y/public/app/feed.php" class="text-decoration-none">
                <div class="fs-1 fw-bold text-primary">y</div>
            </a>
        </div>
        
        <!-- Main navigation -->
        <div class="nav-section flex-grow-1">
            <ul class="list-unstyled">
                <li class="nav-item mb-2">
                    <a href="/y/public/app/feed.php" class="nav-link d-flex align-items-center p-2 text-dark text-decoration-none rounded-3 hover-bg-light <?php echo strpos($_SERVER['PHP_SELF'], 'feed.php') !== false ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-house-door fs-5 me-3"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="/y/public/app/notifications.php" class="nav-link d-flex align-items-center p-2 text-dark text-decoration-none rounded-3 hover-bg-light <?php echo strpos($_SERVER['PHP_SELF'], 'notifications.php') !== false ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-bell fs-5 me-3"></i>
                        <span>Notifications</span>
                        <?php 
                            $unread_count = count_unread_notifications($conn, $user['user_name']); 
                            if ($unread_count > 0):
                        ?>
                            <span class="badge rounded-pill bg-danger ms-auto"><?php echo $unread_count > 9 ? '9+' : $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="/y/public/app/search.php" class="nav-link d-flex align-items-center p-2 text-dark text-decoration-none rounded-3 hover-bg-light <?php echo strpos($_SERVER['PHP_SELF'], 'search.php') !== false ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-search fs-5 me-3"></i>
                        <span>Search</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="/y/public/app/bookmarks.php" class="nav-link d-flex align-items-center p-2 text-dark text-decoration-none rounded-3 hover-bg-light <?php echo strpos($_SERVER['PHP_SELF'], 'bookmarks.php') !== false ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-bookmark fs-5 me-3"></i>
                        <span>Bookmarks</span>
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="/y/public/app/profile.php?username=<?php echo $user['user_name']; ?>" class="nav-link d-flex align-items-center p-2 text-dark text-decoration-none rounded-3 hover-bg-light <?php echo strpos($_SERVER['PHP_SELF'], 'profile.php') !== false && isset($_GET['username']) && $_GET['username'] === $user['user_name'] ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-person fs-5 me-3"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- User profile section -->
        <div class="profile-section mt-auto">
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <a href="/y/public/app/profile.php?username=<?php echo $user['user_name']; ?>" class="text-decoration-none text-reset me-3">
                            <?php 
                                $profile_picture_url = $user['profile_picture_url'] ?? null;
                                $username = $user['user_name'];
                                $size = '48';
                                include __DIR__ . '/user_avatar.php'; 
                            ?>
                        </a>
                        <div class="flex-grow-1 overflow-hidden">
                            <a href="/y/public/app/profile.php?username=<?php echo $user['user_name']; ?>" class="text-decoration-none text-reset">
                                <div class="fw-bold text-truncate"><?php echo htmlspecialchars($user['display_name'] ?? $user['user_name']); ?></div>
                                <div class="text-muted text-truncate">@<?php echo htmlspecialchars($user['user_name']); ?></div>
                            </a>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm text-muted border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/y/public/app/edit_profile.php">Edit Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/y/public/auth/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-icon-container {
        width: 24px;
        display: flex;
        justify-content: center;
    }
    
    .notification-badge {
        font-size: 0.6rem;
        padding: 0.25rem 0.4rem;
        transform: translate(25%, -25%) !important;
    }
</style>