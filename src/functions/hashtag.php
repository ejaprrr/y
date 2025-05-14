<?php

// Extract hashtags from content
function extract_hashtags($content) {
    $hashtags = [];
    preg_match_all('/#(\w+)/', $content, $matches);
    if (!empty($matches[1])) {
        $hashtags = array_filter(array_unique($matches[1]), function($hashtag) {
            return strlen($hashtag) <= 24; // Limit hashtags to 24 characters
        });
    }
    return $hashtags;
}

// Save hashtags for a post
function save_hashtags($conn, $post_id, $content) {
    $hashtags = extract_hashtags($content);
    
    foreach ($hashtags as $hashtag) {
        if (strlen($hashtag) <= 24) { // Ensure hashtag length is valid
            $stmt = $conn->prepare("INSERT IGNORE INTO hashtags (post_id, hashtag) VALUES (?, ?)");
            $stmt->bind_param("is", $post_id, $hashtag);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    return count($hashtags);
}

// Get trending hashtags
function get_trending_hashtags($conn, $limit = 3) {
    $past_week = date('Y-m-d H:i:s', strtotime('-7 days'));
    
    $sql = "SELECT hashtag, COUNT(*) as count 
            FROM hashtags 
            WHERE created_at > ? 
            GROUP BY hashtag 
            ORDER BY count DESC, created_at DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $past_week, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trending = [];
    while ($row = $result->fetch_assoc()) {
        $trending[] = $row;
    }
    
    $stmt->close();
    return $trending;
}

// Get posts by hashtag
function get_posts_by_hashtag($conn, $hashtag) {
    $sql = "SELECT posts.*, users.username, users.display_name, users.profile_picture,
            (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = posts.id AND user_id = ?) AS is_liked
            FROM posts
            JOIN users ON posts.user_id = users.id
            JOIN hashtags ON posts.id = hashtags.post_id
            WHERE hashtags.hashtag = ?
            ORDER BY posts.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $stmt->bind_param("is", $user_id, $hashtag);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    $stmt->close();
    return $posts;
}

// Get hashtag post count
function get_hashtag_post_count($conn, $hashtag) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM hashtags WHERE hashtag = ?");
    $stmt->bind_param("s", $hashtag);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['count'] ?? 0;
}

// Format post content to highlight hashtags
function format_content_with_hashtags($content) {
    return preg_replace_callback('/#(\w+)/', function ($matches) {
        if (strlen($matches[1]) <= 24) { // Only highlight hashtags <= 24 chars
            return '<a href="../app/hashtag.php?tag=' . htmlspecialchars($matches[1]) . '" class="hashtag">#' . htmlspecialchars($matches[1]) . '</a>';
        }
        return '#' . htmlspecialchars($matches[1]); // Leave longer hashtags as plain text
    }, $content);
}
?>