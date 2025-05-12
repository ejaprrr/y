<?php
require_once "../../src/functions/connection.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/user.php";
require_once "../../src/functions/validation.php";
require_once "../../src/components/post.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/app/left-sidebar.php";
require_once "../../src/components/app/right-sidebar.php";
require_once "../../src/components/app/profile-header.php";

// Authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

set_csrf_token();
$current_user = get_user($conn, $_SESSION['user_id']);

// Get username from URL or default to current user
$username = isset($_GET['username']) ? sanitize_input($_GET['username']) : $current_user['username'];
$profile_user = get_user_by_username($conn, $username);

if (!$profile_user) {
    // User not found
    header("Location: index.php");
    exit();
}

// Get user's posts
$posts = get_user_posts($conn, $profile_user['id']);

// Get user's liked posts
$liked_posts = get_user_liked_posts($conn, $profile_user['id']);

// Determine if this is the current user's profile
$is_own_profile = ($profile_user['id'] == $_SESSION['user_id']);

// Fetch dynamic stats
$followers_count = get_follower_count($conn, $profile_user['id']);
$following_count = get_following_count($conn, $profile_user['id']);
$likes_received = get_total_likes_received($conn, $profile_user['id']);
$is_following = is_following($conn, $current_user['id'], $profile_user['id']);

// Determine which tab is active (default to 'posts')
$active_tab = isset($_GET['tab']) ? sanitize_input($_GET['tab']) : 'posts';

?>

<?php render_header($profile_user['username']); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/pages/profile.css">
<link rel="stylesheet" href="../assets/css/components/post.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">

<div class="app-container d-flex vh-100">
    <?php render_left_sidebar($current_user); ?>

    <div class="main-content">
        <!-- Fixed header -->
        <?php 
        // Create tabs array for profile page
        $profile_tabs = [
            [
                'label' => 'Posts',
                'url' => '?username=' . htmlspecialchars($profile_user['username']) . '&tab=posts',
                'active' => $active_tab === 'posts'
            ],
            [
                'label' => 'Replies',
                'url' => '?username=' . htmlspecialchars($profile_user['username']) . '&tab=replies',
                'active' => $active_tab === 'replies'
            ],
            [
                'label' => 'Likes',
                'url' => '?username=' . htmlspecialchars($profile_user['username']) . '&tab=likes',
                'active' => $active_tab === 'likes'
            ]
        ];

        // Render the profile header component
        render_profile_header(
            $profile_user['display_name'] ?? $profile_user['username'],
            count($posts) . ' posts',
            'index.php',
            $profile_tabs
        );
        ?>

        <!-- Scrollable content -->
        <div class="profile-scrollable-content">
            <!-- Profile header and posts -->
            <div class="profile-header">
                <?php if ($profile_user['cover_image']): ?>
                    <div class="cover-image" style="background-image: url('<?= htmlspecialchars($profile_user['cover_image']) ?>')"></div>
                <?php else: ?>
                    <div class="cover-image default-cover"></div>
                <?php endif; ?>
                
                <div class="position-relative px-3 mb-3 profile-info">
                    <div class="d-flex justify-content-between">
                        <div class="profile-picture-container">
                            <?php if ($profile_user['profile_picture']): ?>
                                <img src="<?= htmlspecialchars($profile_user['profile_picture']) ?>" alt="Profile picture" class="profile-picture">
                            <?php else: ?>
                                <div class="profile-picture default-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="profile-header-actions">
                            <?php if ($is_own_profile): ?>
                                <a href="edit-profile.php" class="btn btn-primary rounded-3 px-4 text-lowercase fw-semibold">edit profile</a>
                            <?php else: ?>
                                <button 
                                    class="btn btn-primary rounded-3 text-lowercase fw-semibold follow-btn" 
                                    data-following="<?= $is_following ? '1' : '0' ?>" 
                                    data-user-id="<?= $profile_user['id'] ?>">
                                    <?= $is_following ? 'unfollow' : 'follow' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h2 class="mb-1 fw-bold"><?= htmlspecialchars($profile_user['display_name'] ?? $profile_user['username']) ?></h2>
                        <p class=" mb-2">@<?= htmlspecialchars($profile_user['username']) ?></p>
                    </div>
                    
                    <?php if (!empty($profile_user['bio'])): ?>
                        <div class="mb-3 user-bio">
                            <p><?= nl2br(htmlspecialchars($profile_user['bio'])) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-4 mb-3 small">
                        <div class="user-stat">
                            <span class="fw-bold"><?= $following_count ?></span>
                            <span class="ms-1">following</span>
                        </div>
                        <div class="user-stat">
                            <span class="fw-bold"><?= $followers_count ?></span>
                            <span class="ms-1">followers</span>
                        </div>
                        <div class="user-stat">
                            <span class="fw-bold"><?= $likes_received ?></span>
                            <span class="ms-1">likes</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="posts mx-3">
                <!-- Add this hidden input field inside the profile page -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <?php if ($active_tab === 'posts'): ?>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <?php render_post($post, $conn); ?> 
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center my-5 py-5 opacity-75 empty-state">
                            <i class="bi bi-file-earmark-text fs-1 mb-3"></i>
                            <p class="text-lowercase">no posts yet</p>
                        </div>
                    <?php endif; ?>
                <?php elseif ($active_tab === 'likes'): ?>
                    <?php if (!empty($liked_posts)): ?>
                        <?php foreach ($liked_posts as $post): ?>
                            <?php render_post($post, $conn); ?> 
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center my-5 py-5 opacity-75 empty-state">
                            <i class="bi bi-heart fs-1 mb-3"></i>
                            <p class="text-lowercase">no liked posts yet</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<script src="../assets/js/interaction.js"></script>

<?php render_footer(); ?>