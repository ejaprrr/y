<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Get bookmarks
$posts = get_bookmarked_posts($conn, $user['user_name'], 50);

// Handle bookmark action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bookmark' && isset($_POST['tweet_id'])) {
    $post_id = (int)$_POST['tweet_id'];
    
    if (has_bookmarked($conn, $user['user_name'], $post_id)) {
        unbookmark_post($conn, $user['user_name'], $post_id);
    } else {
        bookmark_post($conn, $user['user_name'], $post_id);
    }
    
    // Redirect to prevent form resubmission
    redirect("bookmarks.php");
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
    redirect("bookmarks.php");
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
    redirect("bookmarks.php");
}

// Set up page variables
$page_title = 'Y | Bookmarks';
$page_header = 'Bookmarks';

// Capture content in a buffer
ob_start();
?>

<div class="bookmarks-container p-3">
    <!-- Header card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="bi bi-bookmark text-primary fs-3"></i>
                </div>
                <div>
                    <h2 class="fs-5 fw-bold mb-1">Saved posts</h2>
                    <p class="text-muted mb-0">Posts you've bookmarked for later</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookmarked posts -->
    <div class="posts-container">
        <?php if (empty($posts)): ?>
            <?php 
                $icon = 'bookmark';
                $title = 'No bookmarked posts';
                $message = 'When you bookmark a post, it will appear here for easy access later.';
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