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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Y | feed</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .tweet:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include_once __DIR__ . '/../../resources/components/sidebar.php'; ?>
            
            <!-- Main content -->
            <div class="col-md-6 px-0 border-end">
                <div class="fw-bold fs-4 p-3 border-bottom">Home</div>
                
                <div class="p-3 border-bottom">
                    <form method="post" action="">
                        <textarea name="tweet_content" class="form-control border-0 mb-3" rows="3" maxlength="280" placeholder="What's happening?"></textarea>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Post</button>
                        </div>
                    </form>
                </div>
                
                <div id="feed">
                    <?php foreach ($posts as $post): ?>
                        <div class="tweet p-3 border-bottom" id="post-<?php echo $post['id']; ?>">
                            <?php if ($post['reposted_by']): ?>
                                <div class="text-muted mb-2 small">
                                    <i class="bi bi-repeat"></i> Reposted by @<?php echo htmlspecialchars($post['reposted_by']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($post['reply_to_username']): ?>
                                <div class="text-muted mb-2 small">
                                    <i class="bi bi-chat"></i> @<?php echo htmlspecialchars($post['username']); ?> replying to @<?php echo htmlspecialchars($post['reply_to_username']); ?>
                                    <div class="ms-3 ps-2 border-start text-muted">
                                        <p class="small text-truncate m-0">"<?php echo htmlspecialchars($post['reply_to_content']); ?>"</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex gap-2 mb-1">
                                <div class="fw-bold"><?php echo htmlspecialchars($post['display_name']); ?></div>
                                <div>
                                    <a href="profile.php?username=<?php echo $post['username']; ?>" class="text-decoration-none text-muted">
                                        @<?php echo htmlspecialchars($post['username']); ?>
                                    </a>
                                </div>
                                <div class="text-muted">·</div>
                                <div class="text-muted"><?php echo htmlspecialchars($post['timestamp']); ?></div>
                            </div>
                            
                            <p class="mb-2">
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($post['content']); ?>
                                </a>
                            </p>
                            
                            <div class="d-flex gap-4">
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="action" value="like">
                                    <input type="hidden" name="tweet_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="btn btn-sm text-muted p-0 border-0">
                                        <?php echo $post['user_liked'] ? '<i class="bi bi-heart-fill text-danger"></i>' : '<i class="bi bi-heart"></i>'; ?> 
                                        <span class="small"><?php echo $post['like_count']; ?></span>
                                    </button>
                                </form>
                                
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="action" value="repost">
                                    <input type="hidden" name="tweet_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="btn btn-sm text-muted p-0 border-0">
                                        <?php echo $post['user_reposted'] ? '<i class="bi bi-repeat text-success"></i>' : '<i class="bi bi-repeat"></i>'; ?>
                                        <span class="small"><?php echo $post['repost_count']; ?></span>
                                    </button>
                                </form>
                                
                                <form method="post" action="post.php?id=<?php echo $post['id']; ?>" class="d-inline">
                                    <button type="submit" class="btn btn-sm text-muted p-0 border-0">
                                        <i class="bi bi-chat"></i>
                                        <span class="small"><?php if ($post['reply_count'] > 0): ?><?php echo $post['reply_count']; ?><?php endif; ?></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Right column -->
            <?php include_once __DIR__ . '/../../resources/components/right_sidebar.php'; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>