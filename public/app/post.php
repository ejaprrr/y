<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('feed.php');
}

$post_id = (int)$_GET['id'];

// Get the main post
$main_post = get_post($conn, $post_id, $user['user_name']);

if (!$main_post) {
    redirect('feed.php');
}

// Handle new reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tweet_content'])) {
        $content = trim($_POST['tweet_content'] ?? '');
        create_post($conn, $user['user_name'], $content, $post_id);
    }
}

// Handle like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like' && isset($_POST['tweet_id'])) {
    $like_post_id = (int)$_POST['tweet_id'];
    
    if (has_liked($conn, $user['user_name'], $like_post_id)) {
        unlike_post($conn, $user['user_name'], $like_post_id);
    } else {
        like_post($conn, $user['user_name'], $like_post_id);
    }
}

// Handle repost/unrepost action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repost' && isset($_POST['tweet_id'])) {
    $repost_post_id = (int)$_POST['tweet_id'];
    
    if (has_reposted($conn, $user['user_name'], $repost_post_id)) {
        unrepost_post($conn, $user['user_name'], $repost_post_id);
    } else {
        repost_post($conn, $user['user_name'], $repost_post_id);
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

// Get parent post if this is a reply
$parent_post = null;
if (isset($main_post['target_post_id']) && $main_post['target_post_id'] !== null) {
    $parent_post = get_post($conn, $main_post['target_post_id'], $user['user_name']);
}

// Get replies to this post
$replies = get_post_replies($conn, $post_id, $user['user_name']);

// Set up page variables
$page_title = 'Y | Post';

// Capture content in a buffer
ob_start();
?>

<div class="post-container p-3">
    <!-- Parent post (if this is a reply) -->
    <?php if ($parent_post): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3 position-relative">
            <div class="connector-line position-absolute bg-primary" style="width: 2px; left: 40px; top: 75px; bottom: -20px; z-index: 9;"></div>
            
            <div class="d-flex">
                <!-- Profile picture -->
                <a href="profile.php?username=<?php echo $parent_post['username']; ?>" class="text-decoration-none me-3">
                    <div class="rounded-circle overflow-hidden" style="width: 40px; height: 40px; background-color: #f8f9fa;">
                        <?php if (!empty($parent_post['profile_picture_url'])): ?>
                            <img src="<?php echo htmlspecialchars($parent_post['profile_picture_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($parent_post['username']); ?>" 
                                 class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <i class="bi bi-person-circle text-secondary" style="font-size: 1.5rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
                
                <!-- Content -->
                <div class="flex-grow-1">
                    <!-- User info -->
                    <div class="d-flex gap-2 mb-1 flex-wrap align-items-center">
                        <a href="profile.php?username=<?php echo $parent_post['username']; ?>" class="text-decoration-none text-reset">
                            <span class="fw-bold"><?php echo htmlspecialchars($parent_post['display_name'] ?? $parent_post['username']); ?></span>
                        </a>
                        <a href="profile.php?username=<?php echo $parent_post['username']; ?>" class="text-decoration-none text-muted">
                            @<?php echo htmlspecialchars($parent_post['username']); ?>
                        </a>
                        <span class="text-muted">·</span>
                        <span class="text-muted"><?php echo htmlspecialchars($parent_post['timestamp']); ?></span>
                    </div>
                    
                    <!-- Post content -->
                    <p class="mb-2"><?php echo format_content_with_tags(htmlspecialchars($parent_post['content'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main post -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3 position-relative">
            <?php if ($parent_post): ?>
            <div class="connector-line position-absolute bg-primary" style="width: 2px; left: 40px; top: -20px; height: 20px; z-index: 9;"></div>
            <?php endif; ?>
            
            <div class="d-flex">
                <!-- Profile picture -->
                <a href="profile.php?username=<?php echo $main_post['username']; ?>" class="text-decoration-none me-3">
                    <div class="rounded-circle overflow-hidden" style="width: 48px; height: 48px; background-color: #f8f9fa;">
                        <?php if (!empty($main_post['profile_picture_url'])): ?>
                            <img src="<?php echo htmlspecialchars($main_post['profile_picture_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($main_post['username']); ?>" 
                                 class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <i class="bi bi-person-circle text-secondary" style="font-size: 2rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
                
                <!-- Content -->
                <div class="flex-grow-1">
                    <!-- User info -->
                    <div class="d-flex gap-2 mb-2 flex-wrap align-items-center">
                        <a href="profile.php?username=<?php echo $main_post['username']; ?>" class="text-decoration-none text-reset">
                            <span class="fw-bold"><?php echo htmlspecialchars($main_post['display_name'] ?? $main_post['username']); ?></span>
                        </a>
                        <a href="profile.php?username=<?php echo $main_post['username']; ?>" class="text-decoration-none text-muted">
                            @<?php echo htmlspecialchars($main_post['username']); ?>
                        </a>
                        <span class="text-muted">·</span>
                        <span class="text-muted"><?php echo htmlspecialchars($main_post['timestamp']); ?></span>
                    </div>
                    
                    <!-- Post content -->
                    <p class="fs-5 mb-3"><?php echo format_content_with_tags(htmlspecialchars($main_post['content'])); ?></p>

                    <!-- Post date in full format -->
                    <div class="text-muted small mb-3 border-bottom pb-3">
                        <?php echo date('g:i A · M j, Y', strtotime($main_post['raw_timestamp'])); ?>
                    </div>
                    
                    <!-- Post stats -->
                    <div class="d-flex gap-4 py-2 border-bottom">
                        <div><span class="fw-bold"><?php echo $main_post['repost_count']; ?></span> <span class="text-muted">Reposts</span></div>
                        <div><span class="fw-bold"><?php echo $main_post['like_count']; ?></span> <span class="text-muted">Likes</span></div>
                    </div>
                    
                    <!-- Post actions -->
                    <div class="d-flex gap-4 pt-3">
                        <?php $post = $main_post; include __DIR__ . '/../../resources/components/post_actions.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply form -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3">
            <?php 
            // Configure the modern composer for replies
            $is_reply = true;
            $placeholder = "Post your reply";
            $parent_post_id = $main_post['id']; 
            include __DIR__ . '/../../resources/components/modern_composer.php'; 
            ?>
        </div>
    </div>

    <!-- Replies section header -->
    <?php if (count($replies) > 0): ?>
    <h5 class="mb-3 mt-4 fs-5 fw-bold ms-1">Replies</h5>
    
    <!-- Replies -->
    <?php foreach ($replies as $post): ?>
        <?php include __DIR__ . '/../../resources/components/post_card.php'; ?>
    <?php endforeach; ?>
    
    <?php else: ?>
    <?php 
        $icon = 'chat';
        $title = '';
        $message = 'No replies yet. Be the first to reply!';
        include __DIR__ . '/../../resources/components/empty_state.php';
    ?>
    <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reply text preview functionality
    const replyComposer = document.querySelector('.reply-composer');
    const replyPreviewDiv = document.getElementById('reply-preview');
    const replyPreviewContent = document.getElementById('reply-preview-content');
    
    if (replyComposer) {
        replyComposer.addEventListener('input', function() {
            updateReplyPreview();
        });
        
        // Initialize preview on page load
        updateReplyPreview();
    }
});

// Update reply text preview - always show
function updateReplyPreview() {
    const replyComposer = document.querySelector('.reply-composer');
    const replyPreviewDiv = document.getElementById('reply-preview');
    const replyPreviewContent = document.getElementById('reply-preview-content');
    
    if (!replyComposer || !replyPreviewDiv || !replyPreviewContent) return;
    
    const content = replyComposer.value;
    
    // Format with hashtags and mentions
    const formattedContent = content.replace(/#(\w+)/g, '<span class="text-primary fw-medium">#$1</span>')
                                .replace(/@(\w+)/g, '<span class="text-primary fw-medium">@$1</span>');
    
    // Always show preview regardless of content
    replyPreviewContent.innerHTML = formattedContent || '<span class="text-muted">(Your reply will appear here)</span>';
    replyPreviewDiv.style.display = 'block';
}
</script>

<?php
$content = ob_get_clean();
// Render with layout
include __DIR__ . '/../../resources/components/layout.php';
?>