<?php

function has_liked($conn, $user_id, $post_id) {
    $stmt = $conn->prepare("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $liked = $result->num_rows > 0;
    $stmt->close();
    return $liked;
}

function like_post($conn, $user_id, $post_id) {
    if (!has_liked($conn, $user_id, $post_id)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO likes (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    return false;
}

function unlike_post($conn, $user_id, $post_id) {
    if (has_liked($conn, $user_id, $post_id)) {
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    return false;
}

function get_like_count($conn, $post_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["count"] ?? 0);
}
?>