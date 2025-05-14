<?php
require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/user.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/app/post.php";
require_once "../../src/components/app/empty-state.php";
require_once "../../src/components/app/left-sidebar.php";
require_once "../../src/components/app/right-sidebar.php";
require_once "../../src/components/app/page-header.php";
require_once "../../src/components/app/post-composer.php";

// authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}
// initialize variables
$message = '';
$error = '';

// upload base directory
$upload_base = realpath(__DIR__ . "/../uploads");

// set CSRF token
set_csrf_token();

// get user information
$user = get_user($conn, $_SESSION['user_id']);

// determine active tab
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'following' ? 'following' : 'latest';

// check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // check CSRF token
    $valid = check_csrf_token();
    if (!$valid) {
        $error = "invalid CSRF token";
    } else {
        // sanitize and validate content
        $content = sanitize_post_content($_POST['content'] ?? '');
        $content_validation = validate_post_content($content);
        
        if ($content_validation !== true) {
            $error = $content_validation;
        } else {
            // add post
            $success = add_post($conn, $user["username"], $content);
            if ($success) {
                $message = "post created successfully";
            } else {
                $error = "failed to create post";
            }
        }
    }

    
}

// get posts based on active tab
if ($active_tab === 'following') {
    $posts = get_following_posts($conn, $user['id']);
} else {
    $posts = get_posts($conn);
}
?>

<?php render_header("feed"); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/components/post.css">
<link rel="stylesheet" href="../assets/css/components/hashtag.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/post-composer.css">
<link rel="stylesheet" href="../assets/css/components/app/empty-state.css">
<link rel="stylesheet" href="../assets/css/components/page-header.css">

<div class="d-flex">
    <?php render_left_sidebar($user); ?>

    <div class="main-content">
        <?php 
            // Define tabs for the feed
            $feed_tabs = [
                [
                    'label' => 'latest',
                    'url' => 'feed.php?tab=latest',
                    'active' => $active_tab === 'latest'
                ],
                [
                    'label' => 'following',
                    'url' => 'feed.php?tab=following',
                    'active' => $active_tab === 'following'
                ]
            ];
        ?>

        <!-- render page header + composer -->
        <?= render_page_header('feed', 'browse posts and engage with content!', '', $feed_tabs); ?>
        <?= render_post_composer(); ?>

        <!-- messages and errors -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success m-3"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger m-3"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="posts mx-3">
            <!-- render posts / empty state -->
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php render_post($post, $conn); ?> 
                <?php endforeach; ?>
            <?php else: ?>
                <?php 
                    $message = $active_tab === 'following' 
                        ? 'you\'re not following anyone who has posted yet!' 
                        : 'be the first one to post!';
                    
                    render_empty_state(
                        'file-earmark-text', 
                        'no posts yet', 
                        $message
                    ); 
                ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- right sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<!-- ajax -->
<script src="../assets/js/interaction.js"></script>

<?php render_footer(); ?>