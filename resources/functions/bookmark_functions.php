<?php
/**
 * Bookmark a post
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param int $post_id Post ID
 * @return bool Success status
 */
function bookmark_post($conn, $user_name, $post_id) {
    try {
        $stmt = $conn->prepare("INSERT INTO bookmarks (user_name, post_id) VALUES (?, ?)");
        $stmt->bind_param("si", $user_name, $post_id);
        return $stmt->execute();
    } catch (Exception $e) {
        // If duplicate entry, consider it a success
        if ($conn->errno === 1062) {
            return true;
        }
        error_log("Error bookmarking post: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove bookmark from a post
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param int $post_id Post ID
 * @return bool Success status
 */
function unbookmark_post($conn, $user_name, $post_id) {
    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_name = ? AND post_id = ?");
    $stmt->bind_param("si", $user_name, $post_id);
    return $stmt->execute();
}

/**
 * Check if user has bookmarked a post
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param int $post_id Post ID
 * @return bool True if post is bookmarked
 */
function has_bookmarked($conn, $user_name, $post_id) {
    $stmt = $conn->prepare("SELECT 1 FROM bookmarks WHERE user_name = ? AND post_id = ? LIMIT 1");
    $stmt->bind_param("si", $user_name, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Get all bookmarked posts for a user
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param int $limit Maximum posts to return
 * @return array Bookmarked posts
 */
function get_bookmarked_posts($conn, $user_name, $limit = 100) {
    $stmt = $conn->prepare("
        SELECT 
            p.*, 
            u.user_name,
            u.display_name,
            u.profile_picture_url,
            NULL as reposted_by,
            NULL as reply_to_username,
            NULL as reply_to_content,
            NULL as reply_to_id,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
            (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
            EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_name = ?) AS user_reposted,
            (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count,
            1 AS user_bookmarked
        FROM bookmarks b
        JOIN posts p ON b.post_id = p.id
        JOIN users u ON p.author_user_name = u.user_name
        WHERE b.user_name = ?
        ORDER BY b.created_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("sssi", $user_name, $user_name, $user_name, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = format_post_data($row);
    }
    
    return $posts;
}
?>