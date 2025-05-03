<?php
/**
 * Functions for handling hashtags and mentions
 */

/**
 * Extract hashtags from post content
 *
 * @param string $content Post content
 * @return array Array of hashtags (without # symbol)
 */
function extract_hashtags($content) {
    // Update regex to better match hashtags - word chars, numbers, dashes, etc.
    preg_match_all('/#([a-zA-Z0-9\-_]+)/u', $content, $matches);
    
    // Convert all tags to lowercase for case insensitivity
    return array_map('strtolower', $matches[1]);
}

/**
 * Extract mentions from post content
 *
 * @param string $content Post content
 * @return array Array of mentions (without @ symbol)
 */
function extract_mentions($content) {
    preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $matches);
    return $matches[1];
}

/**
 * Process post content - store hashtags in database
 *
 * @param mysqli $conn Database connection
 * @param string $content Post content
 * @return void
 */
function process_content_tags($conn, $content) {
    $hashtags = extract_hashtags($content);
    
    if (empty($hashtags)) {
        return;
    }
    
    // Process each hashtag
    foreach ($hashtags as $tag) {
        // Check if hashtag exists
        $stmt = $conn->prepare("SELECT id, usage_count FROM hashtags WHERE tag_name = ? LIMIT 1");
        $stmt->bind_param("s", $tag);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update usage count
            $row = $result->fetch_assoc();
            $new_count = $row['usage_count'] + 1;
            $update_stmt = $conn->prepare("UPDATE hashtags SET usage_count = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_count, $row['id']);
            $update_stmt->execute();
        } else {
            // Insert new hashtag
            $insert_stmt = $conn->prepare("INSERT INTO hashtags (tag_name) VALUES (?)");
            $insert_stmt->bind_param("s", $tag);
            $insert_stmt->execute();
        }
    }
}

/**
 * Format post content - convert hashtags and mentions to links
 *
 * @param string $content Raw post content
 * @return string Formatted content with clickable links
 */
function format_content_with_tags($content) {
    // Format hashtags - case insensitive match but preserve original case for display
    $content = preg_replace_callback('/#([a-zA-Z0-9\-_]+)/iu', function($matches) {
        $tag = $matches[1];
        $lowercase_tag = strtolower($tag);
        return '<a href="/y/public/app/hashtag.php?tag='.urlencode($lowercase_tag).'" class="text-primary fw-medium">#'.$tag.'</a>';
    }, $content);
    
    // Format mentions
    $content = preg_replace('/@([a-zA-Z0-9_]+)/i', '<a href="/y/public/app/profile.php?username=$1" class="text-primary fw-medium">@$1</a>', $content);
    
    return $content;
}

/**
 * Search for hashtags
 *
 * @param mysqli $conn Database connection
 * @param string $query Search query
 * @param int $limit Maximum results
 * @return array Hashtags matching the search
 */
function search_hashtags($conn, $query, $limit = 20) {
    $search_term = "%$query%";
    
    $stmt = $conn->prepare("
        SELECT tag_name, usage_count 
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

/**
 * Get top hashtags
 *
 * @param mysqli $conn Database connection
 * @param int $limit Maximum results
 * @return array Top hashtags
 */
function get_top_hashtags($conn, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT tag_name, usage_count 
        FROM hashtags 
        ORDER BY usage_count DESC
        LIMIT ?
    ");
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hashtags = [];
    while ($row = $result->fetch_assoc()) {
        $hashtags[] = $row;
    }
    
    return $hashtags;
}

/**
 * Get posts for a specific hashtag
 *
 * @param mysqli $conn Database connection
 * @param string $hashtag Hashtag to search for (without # symbol)
 * @param string $current_user Current user viewing the posts
 * @param int $limit Maximum posts to return
 * @return array Posts containing the hashtag
 */
function get_hashtag_posts($conn, $hashtag, $current_user, $limit = 30) {
    $search_term = "%#$hashtag%";
    
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
            (SELECT COUNT(*) FROM posts WHERE target_post_id = p.id) AS reply_count
        FROM posts p
        JOIN users u ON p.author_user_name = u.user_name
        LEFT JOIN posts target_p ON p.target_post_id = target_p.id
        LEFT JOIN users target_u ON target_p.author_user_name = target_u.user_name
        WHERE p.text_content LIKE ?
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    
    $stmt->bind_param("sssi", $current_user, $current_user, $search_term, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = format_post_data($row);
    }
    
    return $posts;
}
?>