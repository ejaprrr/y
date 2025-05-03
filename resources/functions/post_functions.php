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

/**
 * Get recommended posts for a user
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param int $limit Maximum posts to return
 * @return array Recommended posts
 */
function get_recommended_posts($conn, $user_name, $limit = 30) {
    try {
        // 1. Get the user's liked hashtags (weighted by recency)
        $liked_hashtags = get_user_liked_hashtags($conn, $user_name);
        
        // 2. Get people the user follows (for collaborative filtering)
        $following_users = get_following_users($conn, $user_name);
        
        // 3. Get hashtags used by people the user follows
        $following_hashtags = get_following_users_hashtags($conn, $following_users);
        
        // 4. Merge and weigh the hashtags
        $weighted_hashtags = calculate_hashtag_weights($liked_hashtags, $following_hashtags);
        
        // 5. Get trending posts (for diversity in recommendations)
        $trending_posts = get_trending_posts($conn, $user_name, floor($limit * 0.3)); // 30% trending
        
        // 6. Get personalized posts based on weighted hashtags
        $personalized_posts = get_hashtag_weighted_posts($conn, $user_name, $weighted_hashtags, floor($limit * 0.5)); // 50% personalized
        
        // 7. Get posts from users with similar interests (collaborative filtering)
        $collaborative_posts = get_collaborative_filtered_posts($conn, $user_name, $following_users, floor($limit * 0.2)); // 20% collaborative

        // 8. Merge all posts and remove duplicates
        $all_posts = array_merge($personalized_posts, $trending_posts, $collaborative_posts);
        $unique_posts = remove_duplicate_posts($all_posts);
        
        // 9. Sort by relevance score and limit to requested number
        usort($unique_posts, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });
        
        // Take only the requested number of posts
        $recommended_posts = array_slice($unique_posts, 0, $limit);
        
        // Format the posts for display (remove the recommendation metadata)
        foreach ($recommended_posts as &$post) {
            unset($post['relevance_score']);
            unset($post['recommendation_reason']);
        }
        
        return $recommended_posts;
    } catch (Exception $e) {
        error_log("Error in recommendation algorithm: " . $e->getMessage());
        
        // Fallback to regular feed posts if recommendation fails
        return get_feed_posts($conn, $user_name, $limit);
    }
}

/**
 * Get hashtags from posts liked by the user
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @return array Weighted hashtags from liked posts
 */
function get_user_liked_hashtags($conn, $user_name) {
    $hashtags = [];
    
    // Get posts liked by the user, ordered by most recent
    $stmt = $conn->prepare("
        SELECT p.text_content, l.created_at 
        FROM likes l
        JOIN posts p ON l.post_id = p.id
        WHERE l.user_name = ?
        ORDER BY l.created_at DESC
        LIMIT 100
    ");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $now = new DateTime();
    
    while ($row = $result->fetch_assoc()) {
        // Extract hashtags from post
        $post_hashtags = extract_hashtags($row['text_content']);
        
        // Calculate recency weight (newer likes have higher weight)
        $created_date = new DateTime($row['created_at']);
        $days_ago = $now->diff($created_date)->days + 1; // +1 to avoid division by zero
        $recency_weight = 1 / log10($days_ago + 1.5); // logarithmic decay
        
        // Add hashtags with weight
        foreach ($post_hashtags as $tag) {
            if (!isset($hashtags[$tag])) {
                $hashtags[$tag] = 0;
            }
            $hashtags[$tag] += $recency_weight;
        }
    }
    
    // Sort hashtags by weight
    arsort($hashtags);
    
    return $hashtags;
}

/**
 * Get users that the specified user follows
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @return array Following usernames
 */
function get_following_users($conn, $user_name) {
    $following = [];
    
    $stmt = $conn->prepare("
        SELECT following_user_name 
        FROM follows 
        WHERE user_name = ?
    ");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $following[] = $row['following_user_name'];
    }
    
    return $following;
}

/**
 * Get hashtags from posts by users the user follows
 *
 * @param mysqli $conn Database connection
 * @param array $following_users Following usernames
 * @return array Hashtags from following users' posts
 */
function get_following_users_hashtags($conn, $following_users) {
    if (empty($following_users)) {
        return [];
    }
    
    $hashtags = [];
    $placeholders = str_repeat('?,', count($following_users) - 1) . '?';
    
    $stmt = $conn->prepare("
        SELECT p.text_content
        FROM posts p
        WHERE p.author_user_name IN ($placeholders)
        ORDER BY p.created_at DESC
        LIMIT 200
    ");
    
    $stmt->bind_param(str_repeat('s', count($following_users)), ...$following_users);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $post_hashtags = extract_hashtags($row['text_content']);
        
        foreach ($post_hashtags as $tag) {
            if (!isset($hashtags[$tag])) {
                $hashtags[$tag] = 0;
            }
            $hashtags[$tag] += 1;
        }
    }
    
    return $hashtags;
}

/**
 * Calculate weights for hashtags based on user interests
 *
 * @param array $liked_hashtags Hashtags from liked posts
 * @param array $following_hashtags Hashtags from following users
 * @return array Weighted hashtags
 */
function calculate_hashtag_weights($liked_hashtags, $following_hashtags) {
    $weighted_hashtags = [];
    
    // Liked hashtags have higher weight (0.7)
    foreach ($liked_hashtags as $tag => $weight) {
        $weighted_hashtags[$tag] = $weight * 0.7;
    }
    
    // Following hashtags have lower weight (0.3)
    foreach ($following_hashtags as $tag => $weight) {
        if (!isset($weighted_hashtags[$tag])) {
            $weighted_hashtags[$tag] = 0;
        }
        $weighted_hashtags[$tag] += $weight * 0.3;
    }
    
    // Sort by weight
    arsort($weighted_hashtags);
    
    return $weighted_hashtags;
}

/**
 * Get trending posts across the platform
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Current username
 * @param int $limit Maximum posts to return
 * @return array Trending posts
 */
function get_trending_posts($conn, $user_name, $limit) {
    $trending_posts = [];
    
    // Get posts with high engagement in the last 48 hours
    // - Sort by engagement score (likes + reposts * 2 + replies)
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
            ((SELECT COUNT(*) FROM likes WHERE post_id = p.id) + 
             (SELECT COUNT(*) FROM reposts WHERE post_id = p.id) * 2 + 
             (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id)) AS engagement_score
        FROM posts p
        JOIN users u ON p.author_user_name = u.user_name
        WHERE p.created_at > DATE_SUB(NOW(), INTERVAL 48 HOUR)
        AND p.author_user_name != ?
        AND NOT EXISTS (SELECT 1 FROM follows WHERE user_name = ? AND following_user_name = p.author_user_name)
        ORDER BY engagement_score DESC
        LIMIT ?
    ");
    
    $stmt->bind_param("ssssi", $user_name, $user_name, $user_name, $user_name, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $post = format_post_data($row);
        $post['relevance_score'] = $row['engagement_score'] * 0.8; // Trending weight
        $post['recommendation_reason'] = 'trending';
        $trending_posts[] = $post;
    }
    
    return $trending_posts;
}

/**
 * Get posts based on weighted hashtags
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Current username
 * @param array $weighted_hashtags Weighted hashtags
 * @param int $limit Maximum posts to return
 * @return array Personalized posts
 */
function get_hashtag_weighted_posts($conn, $user_name, $weighted_hashtags, $limit) {
    if (empty($weighted_hashtags)) {
        return [];
    }
    
    $personalized_posts = [];
    $top_hashtags = array_slice($weighted_hashtags, 0, 5, true);
    
    foreach ($top_hashtags as $tag => $weight) {
        $search_term = "%#$tag%";
        
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
                (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count
            FROM posts p
            JOIN users u ON p.author_user_name = u.user_name
            WHERE p.text_content LIKE ?
            AND p.author_user_name != ?
            AND NOT EXISTS (SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?)
            ORDER BY p.created_at DESC
            LIMIT ?
        ");
        
        $posts_per_tag = ceil($limit / count($top_hashtags));
        $stmt->bind_param("sssssi", $user_name, $user_name, $search_term, $user_name, $user_name, $posts_per_tag);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $post = format_post_data($row);
            $post['relevance_score'] = $weight * 1.2; // Personalized content has highest weight
            $post['recommendation_reason'] = 'hashtag';
            $personalized_posts[] = $post;
        }
    }
    
    // Sort by relevance score
    usort($personalized_posts, function($a, $b) {
        return $b['relevance_score'] <=> $a['relevance_score'];
    });
    
    return array_slice($personalized_posts, 0, $limit);
}

/**
 * Get posts based on collaborative filtering
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Current username
 * @param array $following_users Following usernames
 * @param int $limit Maximum posts to return
 * @return array Collaborative filtered posts
 */
function get_collaborative_filtered_posts($conn, $user_name, $following_users, $limit) {
    if (empty($following_users)) {
        return [];
    }
    
    $collaborative_posts = [];
    $placeholders = str_repeat('?,', count($following_users) - 1) . '?';
    
    // Get posts liked by people the user follows, that the user hasn't seen
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
            COUNT(DISTINCT l.user_name) AS follower_like_count
        FROM posts p
        JOIN likes l ON p.id = l.post_id
        JOIN users u ON p.author_user_name = u.user_name
        WHERE l.user_name IN ($placeholders)
        AND p.author_user_name != ?
        AND NOT EXISTS (SELECT 1 FROM likes WHERE post_id = p.id AND user_name = ?)
        GROUP BY p.id
        ORDER BY follower_like_count DESC, p.created_at DESC
        LIMIT ?
    ");
    
    $params = array_merge([$user_name, $user_name], $following_users, [$user_name, $user_name, $limit]);
    $types = str_repeat('s', count($params) - 1) . 'i';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $post = format_post_data($row);
        $post['relevance_score'] = $row['follower_like_count'] * 1.0; // Collaborative weight
        $post['recommendation_reason'] = 'collaborative';
        $collaborative_posts[] = $post;
    }
    
    return $collaborative_posts;
}

/**
 * Remove duplicate posts from merged arrays
 *
 * @param array $posts Array of posts
 * @return array Unique posts
 */
function remove_duplicate_posts($posts) {
    $unique_posts = [];
    $seen_ids = [];
    
    foreach ($posts as $post) {
        if (!in_array($post['id'], $seen_ids)) {
            $seen_ids[] = $post['id'];
            $unique_posts[] = $post;
        }
    }
    
    return $unique_posts;
}
?>