<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Get hashtag parameter
$tag = $_GET['tag'] ?? '';
if (empty($tag)) {
    redirect('feed.php');
}

// Get posts with this hashtag
$posts = get_hashtag_posts($conn, $tag, $user['user_name'], 50);

// Handle like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like' && isset($_POST['tweet_id'])) {
    $post_id = (int)$_POST['tweet_id'];
    
    if (has_liked($conn, $user['user_name'], $post_id)) {
        unlike_post($conn, $user['user_name'], $post_id);
    } else {
        like_post($conn, $user['user_name'], $post_id);
    }
    
    // Redirect to prevent form resubmission
    redirect("hashtag.php?tag=".urlencode($tag));
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
    redirect("hashtag.php?tag=".urlencode($tag));
}

// Get hashtag info
$stmt = $conn->prepare("SELECT * FROM hashtags WHERE tag_name = ? LIMIT 1");
$stmt->bind_param("s", $tag);
$stmt->execute();
$result = $stmt->get_result();
$hashtag_info = ($result->num_rows > 0) ? $result->fetch_assoc() : ['tag_name' => $tag, 'usage_count' => 0];

// Set up page variables
$page_title = 'Y | #' . htmlspecialchars($tag);
$page_header = '#' . htmlspecialchars($tag);

// Capture content in a buffer
ob_start();
?>

<div class="hashtag-container p-3">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h1 class="fs-4 fw-bold">#<?php echo htmlspecialchars($tag); ?></h1>
            <p class="text-muted"><?php echo number_format($hashtag_info['usage_count']); ?> posts</p>
        </div>
    </div>
    
    <?php
    // Hashtag tabs configuration
    $tabs = [
        [
            'id' => 'top',
            'label' => 'Top',
            'url' => "?tag=" . urlencode($tag) . "&tab=top",
            'icon' => 'star'
        ],
        [
            'id' => 'latest',
            'label' => 'Latest',
            'url' => "?tag=" . urlencode($tag) . "&tab=latest",
            'icon' => 'clock'
        ]
    ];

    // Include tabs component
    include __DIR__ . '/../../resources/components/tabs_navigation.php';
    ?>
    
    <!-- Posts with this hashtag -->
    <div class="posts-container">
        <?php if (empty($posts)): ?>
            <?php 
                $icon = 'hash';
                $title = 'No posts found';
                $message = 'There are no posts containing #' . htmlspecialchars($tag) . ' yet.';
                include __DIR__ . '/../../resources/components/empty_state.php'; 
            ?>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php include __DIR__ . '/../../resources/components/post_card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
// Render with layout
include __DIR__ . '/../../resources/components/layout.php';
?>