<?php
/**
 * Search-related functions
 */

/**
 * Search posts by content, username or display name
 *
 * @param mysqli $conn Database connection
 * @param string $query Search query
 * @param string $current_user Username of the current user
 * @param string $sort Sort type ('top' or 'latest')
 * @param int $limit Maximum results to return
 * @return array Posts matching the search criteria
 */
function search_posts($conn, $query, $current_user, $sort = 'top', $limit = 20) {
    // Fuzzy search with LIKE operator
    $search_term = "%$query%";
    
    // Build the base SQL query
    $sql = "
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
            (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count
        FROM posts p
        JOIN users u ON p.author_user_name = u.user_name
        LEFT JOIN posts target_p ON p.target_post_id = target_p.id
        LEFT JOIN users target_u ON target_p.author_user_name = target_u.user_name
        WHERE (
            p.text_content LIKE ? OR
            u.user_name LIKE ? OR
            u.display_name LIKE ?
        )
    ";
    
    // Add sorting
    if ($sort === 'top') {
        $sql .= " ORDER BY (like_count + repost_count + reply_count) DESC, p.created_at DESC";
    } else { // latest
        $sql .= " ORDER BY p.created_at DESC";
    }
    
    // Add limit
    $sql .= " LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $current_user, $current_user, $search_term, $search_term, $search_term, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = format_post_data($row);
    }
    
    return $posts;
}

/**
 * Search for users by username or display name
 *
 * @param mysqli $conn Database connection
 * @param string $query Search query
 * @param string $current_user Username of the current user 
 * @param int $limit Maximum results to return
 * @return array Users matching the search criteria
 */
function search_users($conn, $query, $current_user, $limit = 20) {
    // Fuzzy search with LIKE operator
    $search_term = "%$query%";
    
    $stmt = $conn->prepare("
        SELECT 
            u.*,
            COUNT(f.following_user_name) as follower_count,
            EXISTS(SELECT 1 FROM follows WHERE user_name = ? AND following_user_name = u.user_name) as is_following
        FROM users u
        LEFT JOIN follows f ON u.user_name = f.following_user_name
        WHERE u.user_name LIKE ? OR u.display_name LIKE ?
        GROUP BY u.user_name
        ORDER BY follower_count DESC, 
                 CASE WHEN u.user_name LIKE ? THEN 0 ELSE 1 END,
                 CASE WHEN u.display_name LIKE ? THEN 0 ELSE 1 END
        LIMIT ?
    ");
    
    // Exact match for sorting precedence
    $exact_match = "$query";
    
    $stmt->bind_param("sssssi", $current_user, $search_term, $search_term, $exact_match, $exact_match, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        // Add post count
        $post_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE author_user_name = ?");
        $post_count_stmt->bind_param("s", $row['user_name']);
        $post_count_stmt->execute();
        $count_result = $post_count_stmt->get_result()->fetch_assoc();
        $row['post_count'] = $count_result['count'];
        
        $users[] = $row;
    }
    
    return $users;
}

/**
 * Search for trending hashtags
 *
 * @param mysqli $conn Database connection
 * @param string $query Search query (without # symbol)
 * @param int $limit Maximum results
 * @return array Hashtags matching the query
 */
function search_trending_hashtags($conn, $query, $limit = 20) {
    $search_term = "%$query%";
    
    $stmt = $conn->prepare("
        SELECT 
            tag_name,
            usage_count
        FROM hashtags
        WHERE tag_name LIKE ?
        ORDER BY usage_count DESC, tag_name ASC
        LIMIT ?
    ");
    
    $stmt->bind_param("si", $search_term, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hashtags = [];
    while ($row = $result->fetch_assoc()) {
        $hashtags[] = $row;
    }
    
    return $hashtags;
}
?>