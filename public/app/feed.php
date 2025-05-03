<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Handle new post submission (only for original posts, not replies)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tweet_content'])) {
    $content = trim($_POST['tweet_content']);
    if (!empty($content)) {
        create_post($conn, $user['user_name'], $content);
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
}

// Handle repost/unrepost action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repost' && isset($_POST['tweet_id'])) {
    $post_id = (int)$_POST['tweet_id'];
    
    if (has_reposted($conn, $user['user_name'], $post_id)) {
        unrepost_post($conn, $user['user_name'], $post_id);
    } else {
        repost_post($conn, $user['user_name'], $post_id);
    }
}

// Get feed posts
$posts = get_feed_posts($conn, $user['user_name'], 30);

// Set up page variables
$page_title = 'Y | Home';
$page_header = 'Home';

// Capture content in a buffer
ob_start();
?>

<div class="feed-container p-3">
    <!-- New post form -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="d-flex">
                <div class="me-3">
                    <div class="rounded-circle overflow-hidden" style="width: 48px; height: 48px; background-color: #f8f9fa;">
                        <?php if (!empty($user['profile_picture_url'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($user['user_name']); ?>" 
                                 class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <i class="bi bi-person-circle text-secondary" style="font-size: 2rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <form method="post" action="">
                        <div class="position-relative">
                            <textarea 
                                name="tweet_content" 
                                class="form-control border-0 fs-5 mb-3 composer" 
                                rows="2"
                                maxlength="280" 
                                placeholder="What's happening?"
                            ></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-primary">
                                <i class="bi bi-image me-2"></i>
                                <i class="bi bi-emoji-smile me-2"></i>
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Feed divider -->
    <div class="py-2 px-3 mb-3 bg-light rounded-3 d-flex align-items-center border">
        <div class="text-primary fw-semibold">
            <i class="bi bi-stars me-1"></i> Latest posts
        </div>
    </div>

    <!-- Posts -->
    <div class="posts-container">
        <?php if (empty($posts)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                <div class="mb-4">
                    <i class="bi bi-chat-square-text" style="font-size: 3rem;"></i>
                </div>
                <h5>No posts yet</h5>
                <p class="text-muted">Follow some users to see their posts in your feed!</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-3 hover-post">
                    <div class="card-body p-0">
                        <?php include __DIR__ . '/../../resources/components/post_item.php'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .composer {
        min-height: 80px;
        font-size: 1.1rem !important;
    }
    .composer:focus {
        box-shadow: none;
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