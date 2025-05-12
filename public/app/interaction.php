<?php
require_once "../../src/functions/connection.php";
require_once "../../src/functions/like.php";
require_once "../../src/functions/user.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/post.php";

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
$action = $_POST['action'] ?? '';
$target_id = (int)($_POST['target_id'] ?? 0);

if ($action === 'like' || $action === 'unlike') {
    $post_id = $target_id;

    if (!post_exists($conn, $post_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid post id']);
        exit();
    }

    if ($action === 'like') {
        $success = like_post($conn, $user_id, $post_id);
        if (!$success) {
            http_response_code(500);
            echo json_encode(['error' => 'failed to like post']);
            exit();
        }
    } elseif ($action === 'unlike') {
        $success = unlike_post($conn, $user_id, $post_id);
        if (!$success) {
            http_response_code(500);
            echo json_encode(['error' => 'failed to unlike post']);
            exit();
        }
    }

    echo json_encode([
        'liked' => has_liked($conn, $user_id, $post_id),
        'like_count' => get_like_count($conn, $post_id)
    ]);
    exit();
}

if ($action === 'follow' || $action === 'unfollow') {
    $followed_id = $target_id;

    if ($user_id === $followed_id) {
        http_response_code(400);
        echo json_encode(['error' => 'cannot follow yourself']);
        exit();
    }

    if ($action === 'follow') {
        $stmt = $conn->prepare("INSERT IGNORE INTO follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $followed_id);
        $success = $stmt->execute();
        $stmt->close();

        if (!$success) {
            http_response_code(500);
            echo json_encode(['error' => 'failed to follow user']);
            exit();
        }
    } elseif ($action === 'unfollow') {
        $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->bind_param("ii", $user_id, $followed_id);
        $success = $stmt->execute();
        $stmt->close();

        if (!$success) {
            http_response_code(500);
            echo json_encode(['error' => 'failed to unfollow user']);
            exit();
        }
    }

    echo json_encode([
        'following' => is_following($conn, $user_id, $followed_id),
        'follower_count' => get_follower_count($conn, $followed_id)
    ]);
    exit();
}

http_response_code(400);
echo json_encode(['error' => 'invalid action']);
exit();
?>