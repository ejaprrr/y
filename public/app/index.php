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

// Authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

set_csrf_token();
$user = get_user($conn, $_SESSION['user_id']);

// Handle post creation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $valid = check_csrf_token();
    if (!$valid) {
        echo "Invalid CSRF token.";
        exit();
    }

    $content = sanitize_post_content($_POST['content'] ?? '');
    $content_validation = validate_post_content($content);
    
    if ($content_validation !== true) {
        echo $content_validation;
        exit();
    }

    $success = add_post($conn, $user["username"], $content);
    if ($success) {
        redirect("index.php");
    } else {
        echo "Error saving post.";
        exit();
    }
}

$posts = get_posts($conn);
?>

<?php render_header("feed"); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/components/post.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/composer.css">

<div class="app-container d-flex vh-100">
    <?php render_left_sidebar($user); ?>

    <div class="main-content">
        <?php render_composer(); ?>
        

        <div class="posts mx-3">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php render_post($post, $conn); ?> 
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center my-5 text-lowercase">no posts yet. be the first one!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<script src="../assets/js/interaction.js"></script>

<?php render_footer(); ?>