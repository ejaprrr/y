<?php
/**
 * User-related functions
 */

/**
 * Find user by username
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username to find
 * @return array|null User data or null if not found
 */
function find_user($conn, $user_name) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_name = ? LIMIT 1");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
}

/**
 * Register new user
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param string $password Password (plain text)
 * @return bool Success status
 */
function register_user($conn, $user_name, $password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (user_name, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $user_name, $password_hash);
    return $stmt->execute();
}

/**
 * Username validation with clear regex
 *
 * @param string $user_name Username to validate
 * @return bool True if valid
 */
function validate_username($user_name) {
    return preg_match('/^[a-zA-Z][a-zA-Z0-9_]{2,18}[a-zA-Z0-9]$/', $user_name) &&
           strpos($user_name, '__') === false &&
           substr($user_name, -1) !== '_';
}

/**
 * Follow a user
 *
 * @param mysqli $conn Database connection
 * @param string $user_name User doing the following
 * @param string $target_user_name User to follow
 * @return bool Success status
 */
function follow_user($conn, $user_name, $target_user_name) {
    // Don't allow self-follows
    if ($user_name === $target_user_name) {
        return false;
    }
    
    $stmt = $conn->prepare("INSERT IGNORE INTO follows (user_name, following_user_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $user_name, $target_user_name);
    return $stmt->execute();
}

/**
 * Unfollow a user
 *
 * @param mysqli $conn Database connection
 * @param string $user_name User doing the unfollowing
 * @param string $target_user_name User to unfollow
 * @return bool Success status
 */
function unfollow_user($conn, $user_name, $target_user_name) {
    $stmt = $conn->prepare("DELETE FROM follows WHERE user_name = ? AND following_user_name = ?");
    $stmt->bind_param("ss", $user_name, $target_user_name);
    return $stmt->execute();
}

/**
 * Check if a user follows another user
 *
 * @param mysqli $conn Database connection
 * @param string $user_name User to check
 * @param string $target_user_name Target user
 * @return bool True if following
 */
function is_following($conn, $user_name, $target_user_name) {
    $stmt = $conn->prepare("SELECT 1 FROM follows WHERE user_name = ? AND following_user_name = ? LIMIT 1");
    $stmt->bind_param("ss", $user_name, $target_user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result && $result->num_rows > 0);
}

/**
 * Get user statistics (followers, following, posts)
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @return array Statistics
 */
function get_user_stats($conn, $user_name) {
    // Count followers
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM follows WHERE following_user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $followers_count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

    // Count following
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM follows WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $following_count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

    // Count posts
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE author_user_name = ? AND target_post_id IS NULL");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $posts_count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    return [
        'followers' => $followers_count,
        'following' => $following_count,
        'posts' => $posts_count
    ];
}

/**
 * Update user profile
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param array $data Profile data to update
 * @return bool Success status
 */
function update_user_profile($conn, $user_name, $data) {
    // Only allow certain fields to be updated
    $allowed_fields = ['profile_bio_content', 'display_name', 'profile_picture_url'];
    
    $fields_to_update = [];
    $types = '';
    $values = [];
    
    foreach ($data as $field => $value) {
        if (in_array($field, $allowed_fields)) {
            $fields_to_update[] = "`$field` = ?";
            $types .= 's';  // All are strings
            $values[] = $value;
        }
    }
    
    if (empty($fields_to_update)) {
        return false;
    }
    
    // Add username to values and types
    $types .= 's';
    $values[] = $user_name;
    
    $sql = "UPDATE users SET " . implode(', ', $fields_to_update) . " WHERE user_name = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
}

/**
 * Get recommended users to follow
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Current user's username
 * @param int $limit Maximum number of recommendations to return
 * @return array Array of recommended users
 */
function get_recommended_users($conn, $user_name, $limit = 3) {
    // First, get users with mutual connections that the user doesn't already follow
    $mutual_query = "
        SELECT u.*, 
               COUNT(DISTINCT f_mutual.user_name) AS mutual_count,
               0 AS is_following
        FROM users u
        JOIN follows f_others ON u.user_name = f_others.following_user_name
        JOIN follows f_mutual ON f_others.user_name = f_mutual.following_user_name
        WHERE f_mutual.user_name IN (
            SELECT following_user_name 
            FROM follows 
            WHERE user_name = ?
        )
        AND u.user_name != ?
        AND NOT EXISTS (
            SELECT 1 
            FROM follows 
            WHERE user_name = ? 
            AND following_user_name = u.user_name
        )
        GROUP BY u.user_name
        ORDER BY mutual_count DESC, u.user_name
        LIMIT ?
    ";
    
    $stmt = $conn->prepare($mutual_query);
    $stmt->bind_param("sssi", $user_name, $user_name, $user_name, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recommendations = [];
    while ($row = $result->fetch_assoc()) {
        // Add follower count
        $follower_count = get_follower_count($conn, $row['user_name']);
        $row['follower_count'] = $follower_count;
        $recommendations[] = $row;
    }
    
    // If we don't have enough recommendations with mutual connections, add popular users
    if (count($recommendations) < $limit) {
        $needed = $limit - count($recommendations);
        $excluded_users = [$user_name]; // Start with excluding the current user
        
        // Exclude users we already recommended
        foreach ($recommendations as $rec) {
            $excluded_users[] = $rec['user_name'];
        }
        
        $placeholders = str_repeat('?,', count($excluded_users) - 1) . '?';
        
        $popular_query = "
            SELECT u.*, 
                   (SELECT COUNT(*) FROM follows WHERE following_user_name = u.user_name) AS follower_count,
                   0 AS is_following
            FROM users u
            WHERE u.user_name NOT IN ($placeholders)
            AND NOT EXISTS (
                SELECT 1 
                FROM follows 
                WHERE user_name = ? 
                AND following_user_name = u.user_name
            )
            ORDER BY follower_count DESC, RAND()
            LIMIT ?
        ";
        
        // Create the parameter types string with an extra 's' for the user_name in NOT EXISTS
        $types = str_repeat('s', count($excluded_users) + 1) . 'i';
        // Add the user_name parameter for the NOT EXISTS clause
        $params = array_merge($excluded_users, [$user_name, $needed]);
        
        $stmt = $conn->prepare($popular_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
    }
    
    return $recommendations;
}

/**
 * Get follower count for a user
 *
 * @param mysqli $conn Database connection
 * @param string $user_name Username to get follower count for
 * @return int Number of followers
 */
function get_follower_count($conn, $user_name) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM follows WHERE following_user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['count'] ?? 0;
}
?>