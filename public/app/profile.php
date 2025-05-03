<?php
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Determine whose profile to view
$profile_username = $_GET['username'] ?? $user['user_name'];

// Get profile user's information
$profile_user = find_user($conn, $profile_username);

if (!$profile_user) {
    redirect('feed.php');
}

// Get user stats (followers, following, posts)
$stats = get_user_stats($conn, $profile_username);

// Determine which tab to show
$active_tab = $_GET['tab'] ?? 'posts';

// Get posts based on selected tab
$posts = get_user_posts($conn, $profile_username, $user['user_name'], $active_tab);

// Check if current user follows profile user
$is_following = ($user['user_name'] !== $profile_username) ? 
    is_following($conn, $user['user_name'], $profile_username) : false;

// Handle follow/unfollow action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'follow') {
    if ($is_following) {
        unfollow_user($conn, $user['user_name'], $profile_username);
        $is_following = false;
        $stats['followers']--;
    } else {
        follow_user($conn, $user['user_name'], $profile_username);
        $is_following = true;
        $stats['followers']++;
    }
}

// Handle like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like' && isset($_POST['tweet_id'])) {
    $post_id = (int)$_POST['tweet_id'];
    
    if (has_liked($conn, $user['user_name'], $post_id)) {
        unlike_post($conn, $user['user_name'], $post_id);
    } else {
        like_post($conn, $user['user_name'], $post_id);
    }
    
    // Redirect to prevent form resubmission
    redirect("profile.php?username=$profile_username&tab=$active_tab");
}

// Handle repost/unrepost action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repost' && isset($_POST['tweet_id'])) {
    $post_id = (int)$_POST['tweet_id'];
    
    if (has_reposted($conn, $user['user_name'], $post_id)) {
        unrepost_post($conn, $user['user_name'], $post_id);
    } else {
        repost_post($conn, $user['user_name'], $post_id);
    }
    
    // Redirect to prevent form resubmission
    redirect("profile.php?username=$profile_username&tab=$active_tab");
}

// Profile tabs configuration
$tabs = [
    [
        'id' => 'posts',
        'label' => 'Posts',
        'url' => "?username=$profile_username&tab=posts",
        'icon' => 'file-text'
    ],
    [
        'id' => 'replies',
        'label' => 'Replies',
        'url' => "?username=$profile_username&tab=replies",
        'icon' => 'chat'
    ]
];

// Set up page variables
$page_title = 'Y | @' . htmlspecialchars($profile_username);
$page_header = null; // We'll handle the header manually in the profile

// Capture content in a buffer
ob_start();
?>

<div class="profile-container p-3">
    <!-- Profile header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <!-- Profile banner -->
        <div class="profile-banner bg-primary bg-gradient w-100" style="height: 150px;"></div>
        
        <!-- Profile info section -->
        <div class="profile-info px-4 pt-3 pb-4">
            <!-- Profile picture -->
            <div class="profile-picture position-relative mb-3">
                <div class="rounded-circle overflow-hidden position-absolute bg-white p-1" style="width: 120px; height: 120px; top: -60px; border: 4px solid white;">
                    <?php if (!empty($profile_user['profile_picture_url'])): ?>
                        <img src="<?php echo htmlspecialchars($profile_user['profile_picture_url']); ?>" 
                             alt="<?php echo htmlspecialchars($profile_user['user_name']); ?>" 
                             class="img-fluid rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex justify-content-center align-items-center h-100 bg-light rounded-circle">
                            <i class="bi bi-person-circle text-secondary" style="font-size: 4rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Profile actions -->
            <div class="d-flex justify-content-end mb-4 pt-2">
                <?php if ($profile_username !== $user['user_name']): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="follow">
                        <button type="submit" class="btn <?php echo $is_following ? 'btn-outline-dark' : 'btn-primary'; ?> rounded-pill follow-button <?php echo $is_following ? 'following' : ''; ?>" style="min-width: 110px;">
                            <?php echo $is_following ? '' : 'Follow'; ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="/y/public/app/edit_profile.php" class="btn btn-outline-primary rounded-pill">
                        Edit Profile
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Profile name and details -->
            <div class="profile-details">
                <h1 class="fs-4 fw-bold mb-1">
                    <?php echo htmlspecialchars($profile_user['display_name'] ?? $profile_user['user_name']); ?>
                </h1>
                <p class="text-muted mb-3">@<?php echo htmlspecialchars($profile_user['user_name']); ?></p>
                
                <?php if (!empty($profile_user['profile_bio_content'])): ?>
                    <p class="mb-3"><?php echo htmlspecialchars($profile_user['profile_bio_content']); ?></p>
                <?php endif; ?>
                
                <!-- Profile stats -->
                <div class="d-flex gap-4 text-muted mb-2">
                    <div><a href="#" class="text-decoration-none"><span class="fw-bold text-dark"><?php echo $stats['posts']; ?></span> <span class="text-muted">Posts</span></a></div>
                    <div><a href="#" class="text-decoration-none"><span class="fw-bold text-dark"><?php echo $stats['following']; ?></span> <span class="text-muted">Following</span></a></div>
                    <div><a href="#" class="text-decoration-none"><span class="fw-bold text-dark"><?php echo $stats['followers']; ?></span> <span class="text-muted">Followers</span></a></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile tabs - Now using the shared component -->
    <?php include __DIR__ . '/../../resources/components/tabs_navigation.php'; ?>

    <!-- Posts section -->
    <div class="posts-container">
        <?php if (empty($posts)): ?>
            <?php 
                $icon = 'chat-square-text';
                $title = $active_tab === 'posts' ? 'No posts yet' : 'No replies yet';
                
                if ($profile_username === $user['user_name']) {
                    $message = $active_tab === 'posts' ? 'Your posts will appear here.' : 'Your replies will appear here.';
                } else {
                    $message = $active_tab === 'posts' ? 'This user hasn\'t posted yet.' : 'This user hasn\'t replied to any posts yet.';
                }
                
                include __DIR__ . '/../../resources/components/empty_state.php';
            ?>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php include __DIR__ . '/../../resources/components/post_card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .profile-banner {
        background: linear-gradient(135deg, #1da1f2, #0c66a0);
    }
    .profile-tabs .nav-link.active {
        color: #1da1f2;
        font-weight: 600;
    }
    .hover-bg-light:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
    .hover-post:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important;
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
    .tweet {
        border-bottom: none !important;
    }
</style>

<?php
$content = ob_get_clean();
// Render with layout
include __DIR__ . '/../../resources/components/layout.php';
?>