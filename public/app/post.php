<?php
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tweet_content'])) {
    $content = trim($_POST['tweet_content']);
    if (!empty($content)) {
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

// Check if this is a reply, and if so, fetch the parent post
$parent_post = null;
if ($main_post['target_post_id']) {
    $parent_post = get_post($conn, $main_post['target_post_id'], $user['user_name']);
}

// Get replies to this post
$replies = get_post_replies($conn, $post_id, $user['user_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Y | Post</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include_once __DIR__ . '/../../resources/components/sidebar.php'; ?>
            
            <!-- Main content -->
            <div class="col-md-6 px-0 border-end">
                <div class="fw-bold fs-4 p-3 border-bottom">Post</div>
                
                <?php if ($parent_post): ?>
                <div class="mx-3 my-2 p-3 bg-light rounded border-start border-primary border-3">
                    <div class="d-flex gap-2 mb-1">
                        <div class="fw-bold"><?php echo htmlspecialchars($parent_post['username']); ?></div>
                        <div>
                            <a href="profile.php?username=<?php echo $parent_post['username']; ?>" class="text-decoration-none text-muted">
                                @<?php echo htmlspecialchars($parent_post['username']); ?>
                            </a>
                        </div>
                        <div class="text-muted">·</div>
                        <div class="text-muted"><?php echo htmlspecialchars($parent_post['timestamp']); ?></div>
                    </div>
                    <p class="mb-2"><?php echo htmlspecialchars($parent_post['content']); ?></p>
                    <a href="post.php?id=<?php echo $parent_post['id']; ?>" class="text-decoration-none">View this conversation</a>
                </div>
                <?php endif; ?>
                
                <div class="p-3 border-bottom">
                    <div class="d-flex gap-2 mb-1">
                        <div class="fw-bold"><?php echo htmlspecialchars($main_post['username']); ?></div>
                        <div>
                            <a href="profile.php?username=<?php echo $main_post['username']; ?>" class="text-decoration-none text-muted">
                                @<?php echo htmlspecialchars($main_post['username']); ?>
                            </a>
                        </div>
                        <div class="text-muted">·</div>
                        <div class="text-muted"><?php echo htmlspecialchars($main_post['timestamp']); ?></div>
                    </div>
                    <p class="mb-3"><?php echo htmlspecialchars($main_post['content']); ?></p>
                    
                    <div class="d-flex gap-4 pt-2">
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="like">
                            <input type="hidden" name="tweet_id" value="<?php echo $main_post['id']; ?>">
                            <button type="submit" class="btn btn-sm text-muted p-0 border-0">
                                <?php echo $main_post['user_liked'] ? '<i class="bi bi-heart-fill text-danger"></i>' : '<i class="bi bi-heart"></i>'; ?> 
                                <span class="small"><?php echo $main_post['like_count']; ?></span>
                            </button>
                        </form>
                        
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="repost">
                            <input type="hidden" name="tweet_id" value="<?php echo $main_post['id']; ?>">
                            <button type="submit" class="btn btn-sm text-muted p-0 border-0">
                                <?php echo $main_post['user_reposted'] ? '<i class="bi bi-repeat text-success"></i>' : '<i class="bi bi-repeat"></i>'; ?>
                                <span class="small"><?php echo $main_post['repost_count']; ?></span>
                            </button>
                        </form>
                        
                        <span class="text-muted">
                            <i class="bi bi-chat"></i>
                            <span class="small"><?php echo count($replies); ?> Replies</span>
                        </span>
                    </div>
                </div>
                
                <div class="p-3 border-bottom bg-light">
                    <h5>Post a reply</h5>
                    <form method="post" action="">
                        <textarea name="tweet_content" class="form-control mb-2" rows="2" maxlength="280" placeholder="Post your reply"></textarea>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill">Reply</button>
                        </div>
                    </form>
                </div>
                
                <?php if (count($replies) > 0): ?>
                <div>
                    <h5 class="p-3 mb-0">Replies</h5>
                    
                    <?php foreach ($replies as $reply): ?>
                    <div class="p-3 border-bottom" id="reply-<?php echo $reply['id']; ?>">
                        <div class="d-flex gap-2 mb-1">
                            <div class="fw-bold"><?php echo htmlspecialchars($reply['username']); ?></div>
                            <div>
                                <a href="profile.php?username=<?php echo $reply['username']; ?>" class="text-decoration-none text-muted">
                                    @<?php echo htmlspecialchars($reply['username']); ?>
                                </a>
                            </div>
                            <div class="text-muted">·</div>
                            <div class="text-muted"><?php echo htmlspecialchars($reply['timestamp']); ?></div>
                        </div>
                        <p class="mb-2"><?php echo htmlspecialchars($reply['content']); ?></p>
                        
                        <div class="d-flex gap-4">
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="like">
                                <input type="hidden" name="tweet_id" value="<?php echo $reply['id']; ?>">
                                <button type="submit" class="btn btn-sm text-muted p-0 border-0">
                                    <?php echo $reply['user_liked'] ? '<i class="bi bi-heart-fill text-danger"></i>' : '<i class="bi bi-heart"></i>'; ?> 
                                    <span class="small"><?php echo $reply['like_count']; ?></span>
                                </button>
                            </form>
                            
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="repost">
                                <input type="hidden" name="tweet_id" value="<?php echo $reply['id']; ?>">
                                <button type="submit" class="btn btn-sm text-muted p-0 border-0">
                                    <?php echo $reply['user_reposted'] ? '<i class="bi bi-repeat text-success"></i>' : '<i class="bi bi-repeat"></i>'; ?>
                                    <span class="small"><?php echo $reply['repost_count']; ?></span>
                                </button>
                            </form>
                            
                            <form method="post" action="post.php?id=<?php echo $reply['id']; ?>" class="d-inline">
                                <button type="submit" class="btn btn-sm text-muted p-0 border-0">
                                    <i class="bi bi-chat"></i>
                                    <span class="small"><?php if ($reply['reply_count'] > 0): ?><?php echo $reply['reply_count']; ?><?php endif; ?></span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right column -->
            <?php include_once __DIR__ . '/../../resources/components/right_sidebar.php'; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>