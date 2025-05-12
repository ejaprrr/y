<?php
require_once "../../src/functions/connection.php";
require_once "../../src/functions/like.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";

set_endpoint_header();

if (!check_login()) {
    http_response_code(403);
    echo json_encode(['error' => 'unauthorized']);
    exit();
}

if (!check_csrf_token()) {
    http_response_code(403);
    echo json_encode(['error' => 'invalid csrf token']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = (int)($_POST['post_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!post_exists($conn, $post_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid post id']);
    exit();
}

if ($action === 'like') {
    $success = like_post($conn, $user_id, $post_id);
    if ($success === false) {
        http_response_code(500);
        echo json_encode(['error' => 'failed to like post']);
        exit();
    }
} elseif ($action === 'unlike') {
    $success = unlike_post($conn, $user_id, $post_id);
    if ($success === false) {
        http_response_code(500);
        echo json_encode(['error' => 'failed to unlike post']);
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'invalid action']);
    exit();
}

echo json_encode([
    'liked' => has_liked($conn, $user_id, $post_id),
    'like_count' => get_like_count($conn, $post_id)
]);
?>