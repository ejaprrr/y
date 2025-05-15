<?php
require_once "../../src/functions/connection.php";
require_once "../../src/functions/like.php";
require_once "../../src/functions/user.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/post.php";

set_endpoint_header();

if (!check_login()) {
    respond(["error" => "unauthorized"], 403);
}

function respond($data, $code = 200) {
    regenerate_csrf_token(false);
    http_response_code($code);
    $data['new_csrf_token'] = $_SESSION['csrf_token'];
    $data['success'] = true;
    echo json_encode($data);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_ajax_csrf_token()) {
        respond(["error" => "invalid csrf token"], 403);
    }

    $user_id = $_SESSION["user_id"];
    $action = $_POST["action"] ?? "";
    $target_id = (int)($_POST["target_id"] ?? 0);

    if ($action === "like" || $action === "unlike") {
        $post_id = $target_id;

        if (!post_exists($conn, $post_id)) {
            respond(["error" => "invalid post id"], 400);
        }

        if ($action === "like") {
            $success = like_post($conn, $user_id, $post_id);
            if (!$success) {
                respond(["error" => "failed to like post"], 500);
            }
        } elseif ($action === "unlike") {
            $success = unlike_post($conn, $user_id, $post_id);
            if (!$success) {
                respond(["error" => "failed to unlike post"], 500);
            }
        }

        respond([
            "liked" => has_liked($conn, $user_id, $post_id),
            "like_count" => get_like_count($conn, $post_id)
        ]);
    }

    if ($action === "follow" || $action === "unfollow") {
        $followed_id = $target_id;

        if ($user_id === $followed_id) {
            respond(["error" => "cannot follow yourself"], 400);
        }

        if ($action === "follow") {
            $stmt = $conn->prepare("INSERT IGNORE INTO follows (follower_id, followed_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $followed_id);
            $success = $stmt->execute();
            $stmt->close();

            if (!$success) {
                respond(["error" => "failed to follow user"], 500);
            }
        } elseif ($action === "unfollow") {
            $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
            $stmt->bind_param("ii", $user_id, $followed_id);
            $success = $stmt->execute();
            $stmt->close();

            if (!$success) {
                respond(["error" => "failed to unfollow user"], 500);
            }
        }

        respond([
            "following" => is_following($conn, $user_id, $followed_id),
            "follower_count" => get_follower_count($conn, $followed_id)
        ]);
    }

    if ($action === "delete") {
        $post_id = $target_id;

        if (!post_exists($conn, $post_id)) {
            respond(["error" => "invalid post id"], 400);
        }

        // Check if the user owns the post
        $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();

        if ($post["user_id"] !== $user_id) {
            respond(["error" => "unauthorized"], 403);
        }

        // Delete the post
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $success = $stmt->execute();
        $stmt->close();

        if (!$success) {
            respond(["error" => "failed to delete post"], 500);
        }

        respond(["success" => true]);
    }

    respond(['success' => true]);
}

respond(["error" => "invalid action"], 400);
?>
