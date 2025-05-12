<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once "../../src/functions/connection.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/user.php";
require_once "../../src/components/post.php";
require_once "../../src/components/layout.php";

if (!check_login()) {
    redirect("../auth/log-in.php");
}

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

<h1>welcome, <?= $user["username"] ?>!</h1>
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
        <?php render_post($post, $_SESSION['user_id'], $conn); ?>
    <?php endforeach; ?>
<?php else: ?>
    <p>no posts yet. be the first one!</p>
<?php endif; ?>

<script>
// handle interactions using AJAX
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postDiv = this.closest('.post');
            const postId = postDiv.getAttribute('data-post-id');
            const liked = this.getAttribute('data-liked') === '1';
            const action = liked ? 'unlike' : 'like';
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;

            btn.disabled = true;
            fetch('interaction.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `post_id=${encodeURIComponent(postId)}&action=${action}&csrf_token=${encodeURIComponent(csrfToken)}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    this.setAttribute('data-liked', data.liked ? '1' : '0');
                    this.textContent = (data.liked ? 'Unlike' : 'Like') + ` (${data.like_count})`;
                }
            })
            .finally(() => {
                btn.disabled = false;
            });
        });
    });
});
</script>

<?php render_footer(); ?>