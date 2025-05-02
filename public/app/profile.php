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
        // Unfollow
        unfollow_user($conn, $user['user_name'], $profile_username);
        $is_following = false;
        $stats['followers']--;
    } else {
        // Follow
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Y | @<?php echo htmlspecialchars($profile_username); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .follow-button.following:hover::after {
            content: 'Unfollow';
        }
        .follow-button.following::after {
            content: 'Following';
        }
        .follow-button.following:hover {
            background-color: #f8d7da;
            color: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include_once __DIR__ . '/../../resources/components/sidebar.php'; ?>
            
            <!-- Main content -->
            <div class="col-md-6 px-0 border-end">
                <div class="p-3 border-bottom">
                    <div class="mb-4">
                        <div class="mb-2 fs-1"><i class="bi bi-person-circle"></i></div>
                        <div class="fs-4 fw-bold"><?php echo htmlspecialchars($profile_user['user_name']); ?></div>
                        <div class="text-muted">@<?php echo htmlspecialchars($profile_user['user_name']); ?></div>
                        
                        <?php if ($profile_username !== $user['user_name']): ?>
                        <form method="post" class="mt-2">
                            <input type="hidden" name="action" value="follow">
                            <button type="submit" class="btn <?php echo $is_following ? 'btn-outline-dark' : 'btn-primary'; ?> rounded-pill follow-button <?php echo $is_following ? 'following' : ''; ?>">
                                <?php echo $is_following ? '' : 'Follow'; ?>
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="my-3">
                            <?php echo isset($profile_user['profile_bio_content']) ? htmlspecialchars($profile_user['profile_bio_content']) : '<span class="text-muted">No bio yet</span>'; ?>
                        </div>
                        
                        <div class="d-flex gap-4">
                            <div><span class="fw-bold"><?php echo $stats['posts']; ?></span> <span class="text-muted">Posts</span></div>
                            <div><span class="fw-bold"><?php echo $stats['following']; ?></span> <span class="text-muted">Following</span></div>
                            <div><span class="fw-bold"><?php echo $stats['followers']; ?></span> <span class="text-muted">Followers</span></div>
                        </div>
                    </div>
                </div>
                
                <ul class="nav nav-tabs">
                    <li class="nav-item w-50 text-center">
                        <a class="nav-link <?php echo $active_tab === 'posts' ? 'active' : ''; ?>" 
                           href="?username=<?php echo $profile_username; ?>&tab=posts">Posts</a>
                    </li>
                    <li class="nav-item w-50 text-center">
                        <a class="nav-link <?php echo $active_tab === 'replies' ? 'active' : ''; ?>" 
                           href="?username=<?php echo $profile_username; ?>&tab=replies">Replies</a>
                    </li>
                </ul>
                
                <div class="posts">
                    <?php if (empty($posts)): ?>
                        <div class="p-4 text-center text-muted">
                            No <?php echo $active_tab === 'posts' ? 'posts' : 'replies'; ?> yet.
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="p-3 border-bottom" id="post-<?php echo $post['id']; ?>">
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
                    <?php endif; ?>
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