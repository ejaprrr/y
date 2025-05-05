<?php
// Determine current page
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<div class="col-md-3 position-sticky top-0 vh-100 d-flex flex-column py-3">
    <!-- Y Logo -->
    <a href="/y/public/app/feed.php" class="text-decoration-none ps-3 mb-4">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                 style="width: 42px; height: 42px;">
                <span class="fs-3 fw-bold text-white">Y</span>
            </div>
        </div>
    </a>
    
    <!-- Navigation Items -->
    <nav class="mb-auto">
        <ul class="nav flex-column gap-1 px-2">
            <?php
            $nav_items = [
                [
                    'url' => '/y/public/app/feed.php',
                    'icon' => 'house-door',
                    'label' => 'Home',
                    'active' => strpos($_SERVER['PHP_SELF'], 'feed.php') !== false
                ],
                [
                    'url' => '/y/public/app/notifications.php',
                    'icon' => 'bell',
                    'label' => 'Notifications',
                    'active' => strpos($_SERVER['PHP_SELF'], 'notifications.php') !== false,
                    'badge' => count_unread_notifications($conn, $user['user_name'])
                ],
                [
                    'url' => '/y/public/app/search.php',
                    'icon' => 'search',
                    'label' => 'Explore',
                    'active' => strpos($_SERVER['PHP_SELF'], 'search.php') !== false
                ],
                [
                    'url' => '/y/public/app/bookmarks.php',
                    'icon' => 'bookmark',
                    'label' => 'Bookmarks',
                    'active' => strpos($_SERVER['PHP_SELF'], 'bookmarks.php') !== false
                ],
                [
                    'url' => '/y/public/app/profile.php?username='.$user['user_name'],
                    'icon' => 'person',
                    'label' => 'Profile',
                    'active' => strpos($_SERVER['PHP_SELF'], 'profile.php') !== false && isset($_GET['username']) && $_GET['username'] === $user['user_name']
                ]
            ];
            
            foreach($nav_items as $item): ?>
            <li class="nav-item">
                <a href="<?php echo $item['url']; ?>" 
                   class="nav-link rounded-pill py-3 px-4 d-flex align-items-center position-relative <?php echo $item['active'] ? 'active fw-medium bg-primary-bg-subtle text-primary' : 'text-dark'; ?>">
                    
                    <?php if($item['active']): ?>
                    <div class="position-absolute start-0 top-50 translate-middle-y rounded-end" 
                         style="width: 4px; height: 60%; background-color: var(--bs-primary);"></div>
                    <?php endif; ?>
                    
                    <i class="bi bi-<?php echo $item['icon']; ?> fs-5 <?php echo $item['active'] ? 'text-primary' : ''; ?>"></i>
                    <span class="ms-3 flex-grow-1"><?php echo $item['label']; ?></span>
                    
                    <?php if(isset($item['badge']) && $item['badge'] > 0): ?>
                    <span class="badge rounded-pill bg-danger">
                        <?php echo $item['badge'] > 9 ? '9+' : $item['badge']; ?>
                    </span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
            
            <!-- Post Button -->
            <li class="nav-item mt-3 px-2">
                <a href="#" class="btn btn-primary rounded-pill py-2 fw-medium d-flex align-items-center justify-content-center"
                   data-bs-toggle="modal" data-bs-target="#composeModal">
                    <i class="bi bi-plus-lg me-1"></i> Post
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- User Profile Section -->
    <div class="mt-auto mx-2">
        <div class="card rounded-4 border-0">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <a href="/y/public/app/profile.php?username=<?php echo $user['user_name']; ?>" class="text-decoration-none me-3">
                        <?php 
                            $profile_picture_url = $user['profile_picture_url'] ?? null;
                            $username = $user['user_name'];
                            $size = '46';
                            include __DIR__ . '/user_avatar.php'; 
                        ?>
                    </a>
                    <div class="flex-grow-1 me-2">
                        <a href="/y/public/app/profile.php?username=<?php echo $user['user_name']; ?>" class="text-decoration-none text-reset">
                            <div class="fw-bold text-truncate"><?php echo htmlspecialchars($user['display_name'] ?? $user['user_name']); ?></div>
                            <div class="text-muted small text-truncate">@<?php echo htmlspecialchars($user['user_name']); ?></div>
                        </a>
                    </div>
                    
                    <!-- Menu Dropdown -->
                    <div class="dropdown">
                        <button class="action-icon-wrapper bg-transparent" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bi bi-gear"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 py-2">
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2" href="/y/public/app/edit_profile.php">
                                    <i class="bi bi-pencil text-primary me-2"></i> Edit Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center px-3 py-2" href="/y/public/auth/logout.php">
                                    <i class="bi bi-box-arrow-right text-danger me-2"></i> Log Out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>