<?php

require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/user.php";
require_once "../../src/functions/hashtag.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/app/post.php";
require_once "../../src/components/app/empty-state.php";
require_once "../../src/components/app/left-sidebar.php";
require_once "../../src/components/app/right-sidebar.php";
require_once "../../src/components/app/page-header.php";

// Authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

// Set CSRF token
set_csrf_token();

// Get user information
$user = get_user($conn, $_SESSION['user_id']);

// Get hashtag from URL
$tag = isset($_GET['tag']) ? sanitize_input($_GET['tag']) : '';

if (empty($tag)) {
    redirect("feed.php");
}

// Get posts with this hashtag
$posts = get_posts_by_hashtag($conn, $tag);
$post_count = get_hashtag_post_count($conn, $tag);

?>

<?php render_header("#" . $tag); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/components/post.css">
<link rel="stylesheet" href="../assets/css/components/hashtag.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/app/empty-state.css">
<link rel="stylesheet" href="../assets/css/components/page-header.css">

<div class="d-flex">
    <?php render_left_sidebar($user); ?>

    <div class="main-content">
        <?php 
            // Render page header
            render_page_header(
                '#' . htmlspecialchars($tag),
                number_format($post_count) . ' posts',
                'feed.php',
                []
            );
        ?>
        
        <div class="posts mx-3 my-3">
            <!-- render posts / empty state -->
            <?php if (!empty($posts)): ?>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <?php foreach ($posts as $post): ?>
                    <?php render_post($post, $conn); ?> 
                <?php endforeach; ?>
            <?php else: ?>
                <?php 
                    render_empty_state(
                        'hash',
                        'no posts with #' . htmlspecialchars($tag) . ' yet',
                        'be the first to use this hashtag!'
                    );
                ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<!-- AJAX -->
<script src="../assets/js/interaction.js"></script>

<?php render_footer(); ?>