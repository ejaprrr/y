<?php
function username_exists($conn, $username) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

function add_user($conn, $username, $hashed_password) {
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $stmt->close();
        return $new_id;
    }

    $stmt->close();
    return false;
}

function get_user($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id, username, display_name, bio, profile_picture, cover_image, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return null; 
    }

    return $user;
}

function get_user_by_username($conn, $username) {
    $stmt = $conn->prepare("SELECT id, username, display_name, bio, profile_picture, cover_image, created_at FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

function update_user_profile($conn, $user_id, $display_name, $bio) {
    $stmt = $conn->prepare("UPDATE users SET display_name = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssi", $display_name, $bio, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function update_profile_picture($conn, $user_id, $profile_picture) {
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $profile_picture, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function update_cover_image($conn, $user_id, $cover_image) {
    $stmt = $conn->prepare("UPDATE users SET cover_image = ? WHERE id = ?");
    $stmt->bind_param("si", $cover_image, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function get_user_posts($conn, $user_id, $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;
    
    $stmt = $conn->prepare("SELECT posts.*, users.username, users.display_name, users.profile_picture,
                          (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
                          EXISTS(SELECT 1 FROM likes WHERE post_id = posts.id AND user_id = ?) AS is_liked
                          FROM posts 
                          JOIN users ON posts.user_id = users.id
                          WHERE posts.user_id = ? 
                          ORDER BY posts.created_at DESC
                          LIMIT ? OFFSET ?");
    
    $current_user = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0;
    $stmt->bind_param("iiii", $current_user, $user_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    $stmt->close();
    return $posts;
}

function get_user_liked_posts($conn, $user_id, $page = 1, $per_page = 10) {
    $offset = ($page - 1) * $per_page;
    
    $stmt = $conn->prepare("SELECT posts.*, users.username, users.display_name, users.profile_picture,
                           (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
                           EXISTS(SELECT 1 FROM likes WHERE post_id = posts.id AND user_id = ?) AS is_liked
                           FROM posts 
                           JOIN users ON posts.user_id = users.id
                           JOIN likes ON posts.id = likes.post_id
                           WHERE likes.user_id = ?
                           ORDER BY likes.created_at DESC
                           LIMIT ? OFFSET ?");
    
    $current_user = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0;
    $stmt->bind_param("iiii", $current_user, $user_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    $stmt->close();
    return $posts;
}

function get_total_user_posts($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["count"] ?? 0);
}

function get_total_user_liked_posts($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["count"] ?? 0);
}

function get_follower_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM follows WHERE followed_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["count"] ?? 0);
}

function get_following_count($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM follows WHERE follower_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["count"] ?? 0);
}

function is_following($conn, $follower_id, $followed_id) {
    $stmt = $conn->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $follower_id, $followed_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_following = $result->num_rows > 0;
    $stmt->close();
    return $is_following;
}

function get_total_likes_received($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count
                            FROM likes JOIN posts
                            ON likes.post_id = posts.id 
                            WHERE posts.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result["count"] ?? 0);
}

function get_search_results_count($conn, $params, $user_id) {
    // Build search query based on parameters - similar to perform_search but count only
    $sql_params = [];
    $sql_types = "";
    
    // Base query to count matching posts
    $base_sql = "SELECT COUNT(*) as count
                FROM posts
                JOIN users ON posts.user_id = users.id";
    
    // Where conditions array
    $where_conditions = [];
    
    // keyword search (in post content, username, display name, or hashtags)
    if (!empty($params["keyword"])) {
        $keyword_search = $params["keyword"];
        
        // Handle case sensitivity
        $like_operator = isset($params["case_sensitive"]) && $params["case_sensitive"] ? "LIKE BINARY" : "LIKE";
        
        // Handle whole word search
        if (isset($params["whole_word"]) && $params["whole_word"]) {
            // For whole word search code...
            $keyword = explode(" ", $keyword_search);
            $keyword_conditions = [];
            
            foreach ($keyword as $word) {
                if (empty($word)) continue;
                
                // For whole word search
                $search_pattern = "[[:<:]]" . preg_quote($word) . "[[:>:]]";
                $keyword_conditions[] = "(posts.content REGEXP ? OR users.username REGEXP ? OR users.display_name REGEXP ? OR 
                                        EXISTS(SELECT 1 FROM hashtags WHERE hashtags.post_id = posts.id AND hashtags.hashtag REGEXP ?))";
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_types .= "ssss";
            }
            
            if (!empty($keyword_conditions)) {
                $where_conditions[] = "(" . implode(" OR ", $keyword_conditions) . ")";
            }
        } else {
            // For partial word search
            $search_pattern = "%" . $keyword_search . "%";
            $where_conditions[] = "(posts.content $like_operator ? OR users.username $like_operator ? OR users.display_name $like_operator ? OR
                                 EXISTS(SELECT 1 FROM hashtags WHERE hashtags.post_id = posts.id AND hashtags.hashtag $like_operator ?))";
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_types .= "ssss";
        }
    }
    
    // Exclude keyword
    if (!empty($params["exclude_keyword"])) {
        $exclude_search = $params["exclude_keyword"];
        $like_operator = isset($params["case_sensitive"]) && $params["case_sensitive"] ? "LIKE BINARY" : "LIKE";
        
        // For exclude, we want posts that don't contain these terms
        $search_pattern = "%" . $exclude_search . "%";
        $where_conditions[] = "(posts.content NOT $like_operator ? AND users.username NOT $like_operator ? AND users.display_name NOT $like_operator ? AND
                             NOT EXISTS(SELECT 1 FROM hashtags WHERE hashtags.post_id = posts.id AND hashtags.hashtag $like_operator ?))";
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_types .= "ssss";
    }
    
    // Date range (from)
    if (!empty($params["from_date"])) {
        $where_conditions[] = "posts.created_at >= ?";
        $sql_params[] = $params["from_date"] . " 00:00:00";
        $sql_types .= "s";
    }
    
    // Date range (to)
    if (!empty($params["to_date"])) {
        $where_conditions[] = "posts.created_at <= ?";
        $sql_params[] = $params["to_date"] . " 23:59:59";
        $sql_types .= "s";
    }
    
    // Filter by followed users
    if (!empty($params["followed_users"])) {
        $placeholders = implode(",", array_fill(0, count($params["followed_users"]), "?"));
        $where_conditions[] = "users.id IN ($placeholders)";
        foreach ($params["followed_users"] as $followed_id) {
            $sql_params[] = $followed_id;
            $sql_types .= "i";
        }
    }
    
    // Combine conditions
    $sql = $base_sql;
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Execute query
    $stmt = $conn->prepare($sql);
    if ($stmt && !empty($sql_params)) {
        $stmt->bind_param($sql_types, ...$sql_params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($result["count"] ?? 0);
    } else if ($stmt) {
        // Handle case with no parameters
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($result["count"] ?? 0);
    }
    
    return 0;
}

function perform_search($conn, $params, $user_id) {
    // Add pagination parameters with defaults
    $page = isset($params["page"]) ? max(1, intval($params["page"])) : 1;
    $per_page = isset($params["per_page"]) ? max(1, intval($params["per_page"])) : 25;
    $offset = ($page - 1) * $per_page;
    
    // Build search query based on parameters
    $sql_params = [];
    $sql_types = "";
    
    // Base query to get posts with user info and like counts
    $base_sql = "SELECT posts.*, users.username, users.display_name, users.profile_picture,
                (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
                EXISTS(SELECT 1 FROM likes WHERE post_id = posts.id AND user_id = ?) AS is_liked
                FROM posts
                JOIN users ON posts.user_id = users.id";
    
    // Add user_id parameter
    $sql_params[] = $user_id;
    $sql_types .= "i";
    
    // Where conditions array
    $where_conditions = [];
    
    // keyword search (in post content, username, display name, or hashtags)
    if (!empty($params["keyword"])) {
        $keyword_search = $params["keyword"];
        
        // Handle case sensitivity
        $like_operator = isset($params["case_sensitive"]) && $params["case_sensitive"] ? "LIKE BINARY" : "LIKE";
        
        // Handle whole word search
        if (isset($params["whole_word"]) && $params["whole_word"]) {
            // For whole word search, we"ll split into words and search each one
            $keyword = explode(" ", $keyword_search);
            $keyword_conditions = [];
            
            foreach ($keyword as $word) {
                if (empty($word)) continue;
                
                // For whole word search
                $search_pattern = "[[:<:]]" . preg_quote($word) . "[[:>:]]";
                $keyword_conditions[] = "(posts.content REGEXP ? OR users.username REGEXP ? OR users.display_name REGEXP ? OR 
                                        EXISTS(SELECT 1 FROM hashtags WHERE hashtags.post_id = posts.id AND hashtags.hashtag REGEXP ?))";
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_types .= "ssss";
            }
            
            if (!empty($keyword_conditions)) {
                $where_conditions[] = "(" . implode(" OR ", $keyword_conditions) . ")";
            }
        } else {
            // For partial word search
            $search_pattern = "%" . $keyword_search . "%";
            $where_conditions[] = "(posts.content $like_operator ? OR users.username $like_operator ? OR users.display_name $like_operator ? OR
                                 EXISTS(SELECT 1 FROM hashtags WHERE hashtags.post_id = posts.id AND hashtags.hashtag $like_operator ?))";
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_types .= "ssss";
        }
    }
    
    // Exclude keyword
    if (!empty($params["exclude_keyword"])) {
        $exclude_search = $params["exclude_keyword"];
        $like_operator = isset($params["case_sensitive"]) && $params["case_sensitive"] ? "LIKE BINARY" : "LIKE";
        
        // For exclude, we want posts that don'T contain these terms
        $search_pattern = "%" . $exclude_search . "%";
        $where_conditions[] = "(posts.content NOT $like_operator ? AND users.username NOT $like_operator ? AND users.display_name NOT $like_operator ? AND
                             NOT EXISTS(SELECT 1 FROM hashtags WHERE hashtags.post_id = posts.id AND hashtags.hashtag $like_operator ?))";
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_types .= "ssss";
    }
    
    // Date range (from)
    if (!empty($params["from_date"])) {
        $where_conditions[] = "posts.created_at >= ?";
        $sql_params[] = $params["from_date"] . " 00:00:00";
        $sql_types .= "s";
    }
    
    // Date range (to)
    if (!empty($params["to_date"])) {
        $where_conditions[] = "posts.created_at <= ?";
        $sql_params[] = $params["to_date"] . " 23:59:59";
        $sql_types .= "s";
    }
    
    // Filter by followed users
    if (!empty($params["followed_users"])) {
        $followed_users = $params["followed_users"];
        // Make sure we're working with an array
        if (!is_array($followed_users)) {
            $followed_users = [$followed_users];
        }
        
        $placeholders = implode(",", array_fill(0, count($followed_users), "?"));
        $where_conditions[] = "users.id IN ($placeholders)";
        
        foreach ($followed_users as $followed_id) {
            $sql_params[] = $followed_id;
            $sql_types .= "i";
        }
    }
    
    // Combine conditions
    $sql = $base_sql;
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Sorting
    switch ($params["sort_by"]) {
        case "popular":
            $sql .= " ORDER BY like_count DESC, posts.created_at DESC";
            break;
        case "recent":
        default:
            $sql .= " ORDER BY posts.created_at DESC";
            break;
    }
    
    // Add pagination to the final SQL query
    $sql .= " LIMIT ? OFFSET ?";
    $sql_params[] = $per_page;
    $sql_params[] = $offset;
    $sql_types .= "ii";
    
    // Execute query
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($sql_types, ...$sql_params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        
        $stmt->close();
        return $posts;
    }
    
    return [];
}

function get_followed_users($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT users.id, users.username, users.display_name, users.profile_picture
        FROM users
        JOIN follows ON users.id = follows.followed_id
        WHERE follows.follower_id = ?
        ORDER BY users.username
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    $stmt->close();
    return $users;
}

function get_suggested_users($conn, $user_id, $limit = 3) {
    // Get users the current user is already following
    $sql = "SELECT followed_id FROM follows WHERE follower_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $followed_ids = [];
    $followed_ids[] = $user_id; // Include current user in exclusion list
    
    while ($row = $result->fetch_assoc()) {
        $followed_ids[] = $row["followed_id"];
    }
    $stmt->close();
    
    // Handle case where user follows no one yet
    if (count($followed_ids) === 1) {
        // Just exclude the current user
        $exclude_sql = "WHERE users.id != ?";
        $params = [$user_id];
        $types = "i";
    } else {
        // Exclude all followed users
        $placeholders = implode(",", array_fill(0, count($followed_ids), "?"));
        $exclude_sql = "WHERE users.id NOT IN ($placeholders)";
        $params = $followed_ids;
        $types = str_repeat("i", count($followed_ids));
    }
    
    // Find popular users not followed by current user
    $sql = "SELECT users.id, users.username, users.display_name, users.profile_picture,
            (SELECT COUNT(*) FROM follows WHERE followed_id = users.id) as follower_count
            FROM users
            $exclude_sql
            ORDER BY follower_count DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $params[] = $limit;
    $types .= "i";
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $suggested_users = [];
    while ($row = $result->fetch_assoc()) {
        $suggested_users[] = $row;
    }
    
    $stmt->close();
    return $suggested_users;
}

?>