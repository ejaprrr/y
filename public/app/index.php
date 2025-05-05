<?php

require_once "../../src/functions/connection.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/user.php";
require_once "../../src/components/post.php";
require_once "../../src/components/layout.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

check_login();
set_csrf_token();

$user = get_user($conn, $_SESSION['user_id']);

// handle post creation
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

    $success = add_post($conn, $user["user_name"], $content);

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

<h1>welcome, <?= $user["user_name"] ?>!</h1>
<a href="../auth/log-out.php">log out</a>

<h2>create a post</h2>
<form method="POST">
    <textarea name="content" placeholder="what's happening?" required></textarea>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="submit" value="post">
</form>

<h2>recent posts</h2>
<?php if (!empty($posts)): ?>
    <?php foreach ($posts as $post): ?>
        <?php render_post($post['user_name'], $post['content'], $post['created_at']); ?>
    <?php endforeach; ?>
<?php else: ?>
    <p>no posts yet. be the first one!</p>
<?php endif; ?>

<?php render_footer(); ?>