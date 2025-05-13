<?php
require_once "../../src/functions/connection.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/user.php";
require_once "../../src/components/post.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/app/left-sidebar.php";
require_once "../../src/components/app/right-sidebar.php";
require_once "../../src/components/app/composer.php";
require_once "../../src/components/empty-state.php";

// authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

// set CSRF token
set_csrf_token();

// get user information
$user = get_user($conn, $_SESSION['user_id']);

// handle post creation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // check CSRF token
    $valid = check_csrf_token();
    if (!$valid) {
        echo "Invalid CSRF token.";
        exit();
    }

    // sanitize and validate content
    $content = sanitize_post_content($_POST['content'] ?? '');
    $content_validation = validate_post_content($content);
    
    if ($content_validation !== true) {
        echo $content_validation;
        exit();
    }

    // add post
    $success = add_post($conn, $user["username"], $content);
    if ($success) {
        redirect("feed.php");
    } else {
        echo "Error saving post.";
        exit();
    }
}

// get posts
$posts = get_posts($conn);
?>

<?php render_header("feed"); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/components/post.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/composer.css">
<link rel="stylesheet" href="../assets/css/components/empty-state.css">

<div class="d-flex">
    <?php render_left_sidebar($user); ?>

    <div class="main-content">
        <?php render_composer(); ?>
        
        <div class="posts mx-3">
            <!-- render posts / empty state -->
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php render_post($post, $conn); ?> 
                <?php endforeach; ?>
            <?php else: ?>
                <?php render_empty_state(
                    'file-earmark-text', 
                    'no posts yet', 
                    'be the first one to post!',
                ); ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- right sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<!-- ajax -->
<script src="../assets/js/interaction.js"></script>

<?php render_footer(); ?>