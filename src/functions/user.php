<?php
function username_exists($conn, $username) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

function add_user($conn, $username, $hashed_password) {
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $stmt->close();
        return $new_id;
    }

    $stmt->close();
    return false;
}

function get_user($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id, username, display_name, bio, profile_picture, cover_image, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return null; 
    }

    return $user;
}

function get_user_by_username($conn, $username) {
    $stmt = $conn->prepare("SELECT id, username, display_name, bio, profile_picture, cover_image, created_at FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

function update_user_profile($conn, $user_id, $display_name, $bio) {
    $stmt = $conn->prepare("UPDATE users SET display_name = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssi", $display_name, $bio, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function update_profile_picture($conn, $user_id, $profile_picture) {
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $profile_picture, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function update_cover_image($conn, $user_id, $cover_image) {
    $stmt = $conn->prepare("UPDATE users SET cover_image = ? WHERE id = ?");
    $stmt->bind_param("si", $cover_image, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function get_user_posts($conn, $user_id) {
    $stmt = $conn->prepare("SELECT p.id, u.username, p.content, p.created_at 
                           FROM posts p 
                           JOIN users u ON p.user_id = u.id 
                           WHERE p.user_id = ?
                           ORDER BY p.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $posts;
}

function get_user_liked_posts($conn, $user_id) {
    $stmt = $conn->prepare("SELECT p.id, u.username, p.content, p.created_at 
                           FROM posts p 
                           JOIN users u ON p.user_id = u.id
                           JOIN likes l ON p.id = l.post_id
                           WHERE l.user_id = ?
                           ORDER BY l.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $posts;
}

function get_follower_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM follows WHERE followed_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result['count'] ?? 0);
}

function get_following_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM follows WHERE follower_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result['count'] ?? 0);
}

function is_following($conn, $follower_id, $followed_id) {
    $stmt = $conn->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $follower_id, $followed_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_following = $result->num_rows > 0;
    $stmt->close();
    return $is_following;
}

function get_total_likes_received($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM likes l 
                            JOIN posts p ON l.post_id = p.id 
                            WHERE p.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result['count'] ?? 0);
}

?>