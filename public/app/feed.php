<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Handle active tab
$active_tab = $_GET['tab'] ?? 'latest';
if (!in_array($active_tab, ['latest', 'following'])) {
    $active_tab = 'latest';
}

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tweet_content'])) {
        $content = trim($_POST['tweet_content'] ?? '');
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

// Handle bookmark action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bookmark' && isset($_POST['tweet_id'])) {
    $post_id = (int)$_POST['tweet_id'];
    
    if (has_bookmarked($conn, $user['user_name'], $post_id)) {
        unbookmark_post($conn, $user['user_name'], $post_id);
    } else {
        bookmark_post($conn, $user['user_name'], $post_id);
    }
}

// Get posts based on active tab
if ($active_tab === 'following') {
    $posts = get_feed_posts($conn, $user['user_name'], 30);
} else {
    $posts = get_latest_posts($conn, $user['user_name'], 30);
}

// Set up page variables
$page_title = 'Y | Home';
$page_header = 'Home';

// Capture content in a buffer
ob_start();
?>

<div class="feed-container p-3">
    <!-- New post form -->
    <?php include __DIR__ . '/../../resources/components/modern_composer.php'; ?>

    <!-- Feed tabs configuration -->
    <?php
    $tabs = [
        [
            'id' => 'latest',
            'label' => 'Latest',
            'url' => "?tab=latest",
            'icon' => 'clock'
        ],
        [
            'id' => 'following',
            'label' => 'Following',
            'url' => "?tab=following",
            'icon' => 'people-fill'
        ]
    ];

    include __DIR__ . '/../../resources/components/tabs_navigation.php';
    ?>

    <!-- Posts -->
    <div class="posts-container">
        <?php if (empty($posts)): ?>
            <?php 
                $icon = $active_tab === 'latest' ? 'clock' : 'chat-square-text';
                $title = $active_tab === 'latest' ? 'No posts yet' : 'No posts yet';
                $message = $active_tab === 'latest' ? 'Be the first to post something!' : 'Follow some users to see their posts in your feed!';
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
    .composer-preview {
        display: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Composer text preview functionality
    const composer = document.querySelector('.composer');
    const previewDiv = document.getElementById('composer-preview');
    const previewContent = document.getElementById('preview-content');
    
    if (composer) {
        composer.addEventListener('input', function() {
            updatePreview();
        });
        
        // Initialize preview on page load
        updatePreview();
    }
});

// Update text preview
function updatePreview() {
    const composer = document.querySelector('.composer');
    const previewDiv = document.getElementById('composer-preview');
    const previewContent = document.getElementById('preview-content');
    
    if (!composer || !previewDiv || !previewContent) return;
    
    const content = composer.value;
    
    // Format with hashtags and mentions
    const formattedContent = content.replace(/#([a-zA-Z0-9\-_]+)/g, '<span class="text-primary fw-medium">#$1</span>')
                                .replace(/@([a-zA-Z0-9_]+)/g, '<span class="text-primary fw-medium">@$1</span>');
    
    // Always show preview regardless of content
    previewContent.innerHTML = formattedContent || '<span class="text-muted">(Your post will appear here)</span>';
    previewDiv.style.display = 'block';
}
</script>

<?php
$content = ob_get_clean();
// Render with layout
include __DIR__ . '/../../resources/components/layout.php';
?>