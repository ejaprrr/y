<?php
/**
 * Post-related functions
 */

/**
 * Format post data (standardized post object)
 * 
 * @param array $row Raw post data row from database
 * @return array Formatted post data
 */
function format_post_data($row) {
    // Ensure all fields exist with null coalescence
    return [
        'id' => $row['id'],
        'username' => $row['author_user_name'] ?? $row['user_name'] ?? '',
        'display_name' => $row['display_name'] ?? $row['user_name'] ?? '',
        'profile_picture_url' => $row['profile_picture_url'] ?? null,
        'content' => $row['text_content'] ?? '',
        'timestamp' => format_timestamp($row['created_at']),
        'raw_timestamp' => $row['created_at'] ?? '',
        'like_count' => $row['like_count'] ?? 0,
        'user_liked' => $row['user_liked'] ?? 0,
        'repost_count' => $row['repost_count'] ?? 0,
        'user_reposted' => $row['user_reposted'] ?? 0,
        'reposted_by' => $row['reposted_by'] ?? null,
        'reply_to_username' => $row['reply_to_username'] ?? null,
        'reply_to_content' => $row['reply_to_content'] ?? null,
        'reply_to_id' => $row['reply_to_id'] ?? null,
        'reply_count' => $row['reply_count'] ?? 0,
        'target_post_id' => $row['target_post_id'] ?? null
    ];
}

/**
 * Get a single post by ID
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID
 * @param string $current_user Current user viewing the post
 * @return array|null Post data or null if not found
 */
function get_post($conn, $post_id, $current_user) {
    $stmt = $conn->prepare("
        SELECT 
            p.*, 
            u.user_name,
            u.display_name,
            u.profile_picture_url,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
            (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
            EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_name = ?) AS user_reposted,
            (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count
        FROM posts p
        JOIN users u ON p.author_user_name = u.user_name
        WHERE p.id = ?
    ");
    $stmt->bind_param("ssi", $current_user, $current_user, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return format_post_data($result->fetch_assoc());
    }
    
    return null;
}

/**
 * Create a new post
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Post author
 * @param string $content Post content
 * @param int|null $target_post_id Target post ID for replies
 * @return int|false Post ID on success, false on failure
 */
function create_post($conn, $user_name, $content, $target_post_id = null) {
    $content = trim($content);
    
    if (empty($content)) {
        return false;
    }
    
    if ($target_post_id !== null) {
        $stmt = $conn->prepare("INSERT INTO posts (author_user_name, text_content, target_post_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $user_name, $content, $target_post_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO posts (author_user_name, text_content) VALUES (?, ?)");
        $stmt->bind_param("ss", $user_name, $content);
    }
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    
    return false;
}

/**
 * Like a post
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name User liking the post
 * @param int $post_id Post ID
 * @return bool Success status
 */
function like_post($conn, $user_name, $post_id) {
    $stmt = $conn->prepare("INSERT IGNORE INTO likes (user_name, post_id) VALUES (?, ?)");
    $stmt->bind_param("si", $user_name, $post_id);
    return $stmt->execute();
}

/**
 * Unlike a post
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name User unliking the post
 * @param int $post_id Post ID
 * @return bool Success status
 */
function unlike_post($conn, $user_name, $post_id) {
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_name = ? AND post_id = ?");
    $stmt->bind_param("si", $user_name, $post_id);
    return $stmt->execute();
}

/**
 * Check if user has liked a post
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param int $post_id Post ID
 * @return bool True if liked
 */
function has_liked($conn, $user_name, $post_id) {
    $stmt = $conn->prepare("SELECT 1 FROM likes WHERE user_name = ? AND post_id = ? LIMIT 1");
    $stmt->bind_param("si", $user_name, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result && $result->num_rows > 0);
}

/**
 * Repost a post
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name User reposting the post
 * @param int $post_id Post ID
 * @return bool Success status
 */
function repost_post($conn, $user_name, $post_id) {
    $stmt = $conn->prepare("INSERT IGNORE INTO reposts (user_name, post_id) VALUES (?, ?)");
    $stmt->bind_param("si", $user_name, $post_id);
    return $stmt->execute();
}

/**
 * Unrepost a post
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name User unreposting the post
 * @param int $post_id Post ID
 * @return bool Success status
 */
function unrepost_post($conn, $user_name, $post_id) {
    $stmt = $conn->prepare("DELETE FROM reposts WHERE user_name = ? AND post_id = ?");
    $stmt->bind_param("si", $user_name, $post_id);
    return $stmt->execute();
}

/**
 * Check if user has reposted a post
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param int $post_id Post ID
 * @return bool True if reposted
 */
function has_reposted($conn, $user_name, $post_id) {
    $stmt = $conn->prepare("SELECT 1 FROM reposts WHERE user_name = ? AND post_id = ? LIMIT 1");
    $stmt->bind_param("si", $user_name, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result && $result->num_rows > 0);
}

/**
 * Get feed posts (original posts, reposts, replies)
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Current user
 * @param int $limit Maximum posts to return
 * @return array Posts
 */
function get_feed_posts($conn, $user_name, $limit = 30) {
    try {
        $stmt = $conn->prepare("
            (SELECT 
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
                p.created_at AS sort_time
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            WHERE p.target_post_id IS NULL)
            
            UNION ALL
            
            (SELECT 
                p.*, 
                u.user_name,
                u.display_name,
                u.profile_picture_url,
                r.user_name as reposted_by,
                target_u.user_name as reply_to_username,
                target_p.text_content as reply_to_content,
                target_p.id as reply_to_id,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
                (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
                1 AS user_reposted,
                (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count,
                r.created_at AS sort_time
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            JOIN reposts r ON p.id = r.post_id
            LEFT JOIN posts target_p ON p.target_post_id = target_p.id
            LEFT JOIN users target_u ON target_p.author_user_name = target_u.user_name
            WHERE r.user_name = ?)

            UNION ALL
            
            (SELECT 
                p.*, 
                u.user_name,
                u.display_name,
                u.profile_picture_url,
                NULL as reposted_by,
                target_u.user_name as reply_to_username,
                target_p.text_content as reply_to_content,
                target_p.id as reply_to_id,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
                (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
                EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_name = ?) AS user_reposted,
                (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count,
                p.created_at AS sort_time
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            JOIN posts target_p ON p.target_post_id = target_p.id
            JOIN users target_u ON target_p.author_user_name = target_u.user_name)
            
            ORDER BY sort_time DESC
            LIMIT ?
        ");
        
        if (!$stmt) {
            error_log("SQL prepare error: " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("ssssssi", $user_name, $user_name, $user_name, $user_name, $user_name, $user_name, $limit);
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("SQL execute error: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $posts = [];
        
        while ($row = $result->fetch_assoc()) {
            $posts[] = format_post_data($row);
        }
        
        return $posts;
    } catch (Exception $e) {
        error_log("Exception in get_feed_posts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get user posts based on type (posts or replies)
 * 
 * @param mysqli $conn Database connection
 * @param string $profile_username User whose posts to get
 * @param string $current_user Current viewing user
 * @param string $type Type of posts ('posts' or 'replies')
 * @param int $limit Maximum posts to return
 * @return array Posts
 */
function get_user_posts($conn, $profile_username, $current_user, $type = 'posts', $limit = 30) {
    if ($type === 'posts') {
        // Get original posts and reposts
        $stmt = $conn->prepare("
            (SELECT 
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
                p.created_at AS sort_time
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            WHERE p.author_user_name = ? AND p.target_post_id IS NULL)
            
            UNION ALL
            
            (SELECT 
                p.*, 
                u.user_name,
                u.display_name,
                u.profile_picture_url,
                r.user_name as reposted_by,
                target_u.user_name as reply_to_username,
                target_p.text_content as reply_to_content,
                target_p.id as reply_to_id,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
                (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
                EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_name = ?) AS user_reposted,
                (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count,
                r.created_at AS sort_time
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            JOIN reposts r ON p.id = r.post_id
            LEFT JOIN posts target_p ON p.target_post_id = target_p.id
            LEFT JOIN users target_u ON target_p.author_user_name = target_u.user_name
            WHERE r.user_name = ?)
            
            ORDER BY sort_time DESC
            LIMIT ?
        ");
        $stmt->bind_param("ssssssi", $current_user, $current_user, $profile_username, $current_user, $current_user, $profile_username, $limit);
    } else {
        // This is the "replies" tab - no changes needed here
        $stmt = $conn->prepare("
            SELECT 
                p.*, 
                u.user_name,
                u.display_name,
                u.profile_picture_url,
                NULL as reposted_by,
                target_u.user_name as reply_to_username,
                target_p.text_content as reply_to_content,
                target_p.id as reply_to_id,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
                (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
                EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_name = ?) AS user_reposted,
                (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count,
                p.created_at AS sort_time
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            JOIN posts target_p ON p.target_post_id = target_p.id
            JOIN users target_u ON target_p.author_user_name = target_u.user_name
            WHERE p.author_user_name = ?
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("sssi", $current_user, $current_user, $profile_username, $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = format_post_data($row);
    }
    
    return $posts;
}

/**
 * Get replies to a specific post
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID
 * @param string $current_user Current user viewing the replies
 * @return array Replies
 */
function get_post_replies($conn, $post_id, $current_user) {
    $stmt = $conn->prepare("
        SELECT 
            p.*, 
            u.user_name,
            u.display_name,
            u.profile_picture_url,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
            (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
            EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_name = ?) AS user_reposted,
            (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count
        FROM posts p
        JOIN users u ON p.author_user_name = u.user_name
        WHERE p.target_post_id = ?
        ORDER BY p.created_at ASC
    ");
    $stmt->bind_param("ssi", $current_user, $current_user, $post_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $replies = [];
    
    while ($row = $result->fetch_assoc()) {
        $replies[] = format_post_data($row);
    }
    
    return $replies;
}

/**
 * Delete a post and all its associated data
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID to delete
 * @param string $user_name User attempting to delete the post
 * @return bool Success status
 */
function delete_post($conn, $post_id, $user_name) {
    // First, verify that the user is the author of this post
    $stmt = $conn->prepare("SELECT author_user_name FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
        
        // Check if user is the author
        if ($post['author_user_name'] !== $user_name) {
            return false; // Not authorized
        }
        
        // Begin transaction to ensure all related data is deleted
        $conn->begin_transaction();
        
        try {
            // Step 1: Find all replies to this post (for recursion)
            $stmt = $conn->prepare("SELECT id FROM posts WHERE target_post_id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $replies_result = $stmt->get_result();
            $reply_ids = [];
            
            while ($reply = $replies_result->fetch_assoc()) {
                $reply_ids[] = $reply['id'];
                // Recursively delete each reply and its children
                delete_post_cascade($conn, $reply['id']);
            }
            
            // Step 2: Delete likes for this post
            $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            
            // Step 3: Delete reposts for this post
            $stmt = $conn->prepare("DELETE FROM reposts WHERE post_id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            
            // Step 4: Delete the post itself
            $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->bind_param("i", $post_id);
            $success = $stmt->execute();
            
            // Commit the transaction
            if ($success) {
                $conn->commit();
                return true;
            } else {
                $conn->rollback();
                return false;
            }
        } catch (Exception $e) {
            // Something went wrong, rollback
            $conn->rollback();
            error_log("Error deleting post: " . $e->getMessage());
            return false;
        }
    }
    
    return false;
}

/**
 * Helper function for recursive deletion of post tree
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID to delete
 * @return bool Success status
 */
function delete_post_cascade($conn, $post_id) {
    try {
        // Step 1: Find all replies to this post
        $stmt = $conn->prepare("SELECT id FROM posts WHERE target_post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $replies_result = $stmt->get_result();
        
        // Step 2: Recursively delete each reply
        while ($reply = $replies_result->fetch_assoc()) {
            delete_post_cascade($conn, $reply['id']);
        }
        
        // Step 3: Delete likes for this post
        $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        
        // Step 4: Delete reposts for this post
        $stmt = $conn->prepare("DELETE FROM reposts WHERE post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        
        // Step 5: Delete the post itself
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in cascade delete: " . $e->getMessage());
        return false;
    }
}
?>