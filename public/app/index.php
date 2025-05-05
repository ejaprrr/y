<?php

session_start();
require_once "../../src/functions/connection.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/post.php";
require_once "../../src/components/post.php"; // Include the post component

check_login();

$user_name = $_SESSION['user_name'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $content = trim($_POST['content'] ?? '');

    if (empty($content)) {
        echo "Post content cannot be empty.";
        exit();
    }

    if (add_post($conn, $user_name, $content)) {
        redirect("index.php");
    } else {
        echo "Error saving post.";
    }
}

$posts = get_posts($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($user_name) ?>!</h1>
    <a href="../auth/log-out.php">Log out</a>

    <h2>Create a Post</h2>
    <form method="POST">
        <textarea name="content" placeholder="What's happening?" required></textarea>
        <input type="submit" value="Post">
    </form>

    <h2>Recent Posts</h2>
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <?php render_post($post['user_name'], $post['content'], $post['created_at']); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts yet. Be the first to post!</p>
    <?php endif; ?>
</body>
</html>