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

<div class="app-container d-flex vh-100">
    <?php render_left_sidebar($user); ?>

    <!-- Main Feed -->
    <div class="main-content">
        <!-- Post Form -->
        <div class="card rounded-4 mx-3 my-3">
            <div class="card-body p-3">
                <form method="POST">
                    <textarea name="content" placeholder="what's happening?" class="form-control bg-transparent text-white border-0 mb-3 text-lowercase" style="height: 100px; resize: none;" required></textarea>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-3 px-4 text-lowercase fw-semibold">post</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Posts -->
        <div class="posts mx-3">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php render_post($post, $conn); ?> 
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted my-5 text-lowercase">no posts yet. be the first one!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<script src="../assets/js/interaction.js"></script>

<?php render_footer(); ?>