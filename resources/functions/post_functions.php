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
        'reply_count' => $row['reply_count'] ?? 0,
        'reposted_by' => $row['reposted_by'] ?? null,
        'reply_to_username' => $row['reply_to_username'] ?? null,
        'reply_to_content' => $row['reply_to_content'] ?? null,
        'reply_to_id' => $row['reply_to_id'] ?? null,
        'target_post_id' => $row['target_post_id'] ?? null,
        'user_bookmarked' => $row['user_bookmarked'] ?? 0,
    ];
}

/**
 * Get a single post
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID
 * @param string $current_user Current user viewing the post
 * @return array|false Post data or false if not found
 */
function get_post($conn, $post_id, $current_user) {
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
            EXISTS(SELECT 1 FROM bookmarks WHERE post_id = p.id AND user_name = ?) AS user_bookmarked
        FROM posts p
        JOIN users u ON p.author_user_name = u.user_name
        WHERE p.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("sssi", $current_user, $current_user, $current_user, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    return format_post_data($result->fetch_assoc());
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
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        if ($target_post_id !== null) {
            $stmt = $conn->prepare("INSERT INTO posts (author_user_name, text_content, target_post_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $user_name, $content, $target_post_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO posts (author_user_name, text_content) VALUES (?, ?)");
            $stmt->bind_param("ss", $user_name, $content);
        }
        
        if ($stmt->execute()) {
            $post_id = $conn->insert_id;
            
            // If this is a reply, notify the original author
            if ($target_post_id && $post_id) {
                $stmt = $conn->prepare("SELECT author_user_name FROM posts WHERE id = ?");
                $stmt->bind_param("i", $target_post_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $row = $result->fetch_assoc()) {
                    create_notification($conn, 'reply', $row['author_user_name'], $user_name, $post_id);
                }
            }
            
            // Process mentions
            if ($post_id) {
                process_mentions($conn, $content, $user_name, $post_id);
            }
            
            // Process hashtags
            if (function_exists('process_content_tags')) {
                process_content_tags($conn, $content);
            }
            
            $conn->commit();
            return $post_id;
        }
        
        $conn->rollback();
        return false;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating post: " . $e->getMessage());
        return false;
    }
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
    $success = $stmt->execute();
    
    // Get post author to notify them
    $stmt = $conn->prepare("SELECT author_user_name FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        create_notification($conn, 'like', $row['author_user_name'], $user_name, $post_id);
    }
    
    return $success;
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
    $success = $stmt->execute();
    
    // Get post author to notify them
    $stmt = $conn->prepare("SELECT author_user_name FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        create_notification($conn, 'repost', $row['author_user_name'], $user_name, $post_id);
    }
    
    return $success;
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
 * Get feed posts (posts from users the current user follows)
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Current username
 * @param int $limit Maximum posts to return
 * @return array Posts
 */
function get_feed_posts($conn, $user_name, $limit = 30) {
    $stmt = $conn->prepare("
        SELECT 
            p.*, 
            u.user_name,
            u.display_name,
            u.profile_picture_url,
            feed.repost_user_name as reposted_by,
            target_u.user_name as reply_to_username,
            target_p.text_content as reply_to_content, 
            target_p.id as reply_to_id,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
            (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
            EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_name = ?) AS user_reposted,
            (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count,
            EXISTS(SELECT 1 FROM bookmarks WHERE post_id = p.id AND user_name = ?) AS user_bookmarked
        FROM (
            SELECT p.id, p.created_at, NULL as repost_user_name
            FROM posts p
            WHERE p.author_user_name IN (
                SELECT following_user_name FROM follows WHERE user_name = ?
            )
            UNION
            SELECT p.id, r.created_at, r.user_name as repost_user_name
            FROM reposts r
            JOIN posts p ON r.post_id = p.id
            WHERE r.user_name IN (
                SELECT following_user_name FROM follows WHERE user_name = ?
            )
        ) AS feed
        JOIN posts p ON feed.id = p.id
        JOIN users u ON p.author_user_name = u.user_name
        LEFT JOIN posts target_p ON p.target_post_id = target_p.id
        LEFT JOIN users target_u ON target_p.author_user_name = target_u.user_name
        ORDER BY feed.created_at DESC
        LIMIT ?
    ");
    
    $stmt->bind_param("sssssi", $user_name, $user_name, $user_name, $user_name, $user_name, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = format_post_data($row);
    }
    
    return $posts;
}

/**
 * Get posts by a specific user
 * 
 * @param mysqli $conn Database connection
 * @param string $profile_username Username of profile to view
 * @param string $current_user Current user viewing the profile
 * @param string $tab Which tab to view (posts|replies)
 * @param int $limit Maximum posts to return
 * @return array Posts
 */
function get_user_posts($conn, $profile_username, $current_user, $tab = 'posts', $limit = 30) {
    if ($tab === 'posts') {
        // This is the "posts" tab (original posts + reposts)
        $stmt = $conn->prepare("
            WITH all_user_content AS (
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
                EXISTS(SELECT 1 FROM bookmarks WHERE post_id = p.id AND user_name = ?) AS user_bookmarked,
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
                EXISTS(SELECT 1 FROM bookmarks WHERE post_id = p.id AND user_name = ?) AS user_bookmarked,
                r.created_at AS sort_time
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            JOIN reposts r ON p.id = r.post_id
            LEFT JOIN posts target_p ON p.target_post_id = target_p.id
            LEFT JOIN users target_u ON target_p.author_user_name = target_u.user_name
            WHERE r.user_name = ?))
            
            SELECT * FROM all_user_content
            ORDER BY sort_time DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ssssssssi", $current_user, $current_user, $current_user, $profile_username, $current_user, $current_user, $current_user, $profile_username, $limit);
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
                EXISTS(SELECT 1 FROM bookmarks WHERE post_id = p.id AND user_name = ?) AS user_bookmarked
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            LEFT JOIN posts target_p ON p.target_post_id = target_p.id
            LEFT JOIN users target_u ON target_p.author_user_name = target_u.user_name
            WHERE p.author_user_name = ? AND p.target_post_id IS NOT NULL
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ssssi", $current_user, $current_user, $current_user, $profile_username, $limit);
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
 * Get replies to a post
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID
 * @param string $current_user Current viewing user
 * @return array Replies
 */
function get_post_replies($conn, $post_id, $current_user) {
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
            EXISTS(SELECT 1 FROM bookmarks WHERE post_id = p.id AND user_name = ?) AS user_bookmarked
        FROM posts p
        JOIN users u ON p.author_user_name = u.user_name
        WHERE p.target_post_id = ?
        ORDER BY p.created_at ASC
    ");
    
    $stmt->bind_param("sssi", $current_user, $current_user, $current_user, $post_id);
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
    $stmt = $conn->prepare("SELECT author_user_name, text_content FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
        $post_content = $post['text_content']; // Store content for hashtag processing
        
        // Check if user is the author
        if ($post['author_user_name'] !== $user_name) {
            return false; // Not authorized
        }
        
        // Begin transaction to ensure all related data is deleted
        $conn->begin_transaction();
        
        try {
            // Step 1: Find all replies to this post (for recursion)
            $stmt = $conn->prepare("SELECT id, text_content FROM posts WHERE target_post_id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $replies_result = $stmt->get_result();
            $reply_ids = [];
            
            while ($reply = $replies_result->fetch_assoc()) {
                $reply_ids[] = $reply['id'];
                // Process hashtags for replies before deleting
                process_hashtag_deletion($conn, $reply['text_content']);
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
            
            // Step 4: Delete bookmarks for this post
            $stmt = $conn->prepare("DELETE FROM bookmarks WHERE post_id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            
            // Step 5: Process hashtags before deleting the post
            process_hashtag_deletion($conn, $post_content);
            
            // Step 6: Delete the post itself
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
        // First get the post content for hashtag processing
        $stmt = $conn->prepare("SELECT text_content FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $post = $result->fetch_assoc();
            $post_content = $post['text_content'];
            
            // Step 1: Find all replies to this post
            $stmt = $conn->prepare("SELECT id, text_content FROM posts WHERE target_post_id = ?");
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $replies_result = $stmt->get_result();
            
            // Step 2: Recursively delete each reply
            while ($reply = $replies_result->fetch_assoc()) {
                process_hashtag_deletion($conn, $reply['text_content']);
                delete_post_cascade($conn, $reply['id']);
            }
        }
        
        // Step 3: Delete likes for this post
        $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        
        // Step 4: Delete reposts for this post
        $stmt = $conn->prepare("DELETE FROM reposts WHERE post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        
        // Step 5: Delete bookmarks for this post
        $stmt = $conn->prepare("DELETE FROM bookmarks WHERE post_id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        
        // Step 6: Process hashtags before deleting
        if (isset($post_content)) {
            process_hashtag_deletion($conn, $post_content);
        }
        
        // Step 7: Delete the post itself
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error in cascade delete: " . $e->getMessage());
        return false;
    }
}

/**
 * Get latest posts for discovery feed
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param int $limit Maximum posts to return
 * @return array Latest posts
 */
function get_latest_posts($conn, $user_name, $limit = 30) {
    $stmt = $conn->prepare("
        SELECT 
            p.*, 
            u.user_name,
            u.display_name,
            u.profile_picture_url,
            feed.repost_user_name as reposted_by,
            target_u.user_name as reply_to_username,
            target_p.text_content as reply_to_content, 
            target_p.id as reply_to_id,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?) AS user_liked,
            (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) AS repost_count,
            EXISTS(SELECT 1 FROM reposts WHERE post_id = p.id AND user_name = ?) AS user_reposted,
            (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count,
            EXISTS(SELECT 1 FROM bookmarks WHERE post_id = p.id AND user_name = ?) AS user_bookmarked
        FROM (
            -- Original posts
            SELECT 
                p.id, 
                p.created_at, 
                NULL as repost_user_name
            FROM posts p
            
            UNION ALL
            
            -- Reposts
            SELECT 
                p.id, 
                r.created_at,
                r.user_name as repost_user_name
            FROM reposts r
            JOIN posts p ON r.post_id = p.id
        ) AS feed
        JOIN posts p ON feed.id = p.id
        JOIN users u ON p.author_user_name = u.user_name
        LEFT JOIN posts target_p ON p.target_post_id = target_p.id
        LEFT JOIN users target_u ON target_p.author_user_name = target_u.user_name
        ORDER BY feed.created_at DESC
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

/**
 * Get like count for a post
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID
 * @return int Number of likes
 */
function get_like_count($conn, $post_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    return (int)$row[0];
}

/**
 * Get repost count for a post
 * 
 * @param mysqli $conn Database connection
 * @param int $post_id Post ID
 * @return int Number of reposts
 */
function get_repost_count($conn, $post_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reposts WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    return (int)$row[0];
}
?>