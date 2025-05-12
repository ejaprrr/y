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
        $user_id = $user_row['id'];
        
        // Now insert the post with user_id
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            return true;
        }
    }

    return false;
}

function get_posts($conn) {
    $stmt = $conn->prepare("SELECT p.id, u.username, p.content, p.created_at 
                           FROM posts p 
                           JOIN users u ON p.user_id = u.id 
                           ORDER BY p.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    return $posts;
}
?>