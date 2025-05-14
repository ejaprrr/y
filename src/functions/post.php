<?php
function post_exists($conn, $id) {
    $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    
    return $exists;
}

function add_post($conn, $username, $content) {
    // First get the user_id from the username
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_row = $user_result->fetch_assoc()) {
        $user_id = $user_row["id"];
        
        // Now insert the post with user_id
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);
        
        // Execute the insertion
        if ($stmt->execute()) {
            $post_id = $stmt->insert_id;
            $stmt->close();
            
            // Save hashtags for the post
            save_hashtags($conn, $post_id, $content);
            
            return true;
        }
    }

    return false;
}

// Add this function to get total post count
function get_total_posts($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM posts");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["count"] ?? 0);
}

function get_total_following_posts($conn, $user_id) {
    $sql = "SELECT COUNT(*) as count
            FROM posts
            JOIN follows ON posts.user_id = follows.followed_id
            WHERE follows.follower_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["count"] ?? 0);
}

// Modify get_posts to support pagination
function get_posts($conn, $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;
    
    $stmt = $conn->prepare("SELECT posts.*, users.username, users.display_name, users.profile_picture,
                           (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
                           EXISTS(SELECT 1 FROM likes WHERE post_id = posts.id AND user_id = ?) AS is_liked
                           FROM posts 
                           JOIN users ON posts.user_id = users.id 
                           ORDER BY posts.created_at DESC
                           LIMIT ? OFFSET ?");
    
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0;
    $stmt->bind_param("iii", $user_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }

    $stmt->close();
    return $posts;
}

// Modify get_following_posts to support pagination
function get_following_posts($conn, $user_id, $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;
    
    $sql = "SELECT posts.*, users.username, users.display_name, users.profile_picture,
            (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
            EXISTS(SELECT 1 FROM likes WHERE post_id = posts.id AND user_id = ?) AS is_liked
            FROM posts
            JOIN users ON posts.user_id = users.id
            JOIN follows ON posts.user_id = follows.followed_id
            WHERE follows.follower_id = ?
            ORDER BY posts.created_at DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $user_id, $user_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    return $posts;
}
?>