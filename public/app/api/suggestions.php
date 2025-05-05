<?php
require_once __DIR__ . '/../../../resources/connection.php';
require_once __DIR__ . '/../../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Security check
if (!$user) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get query parameters
$type = $_GET['type'] ?? '';
$query = $_GET['query'] ?? '';

// Validate parameters
if (!in_array($type, ['hashtag', 'mention']) || empty($query)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$results = [];

// Process based on type
if ($type === 'hashtag') {
    // Search for hashtags
    $stmt = $conn->prepare("
        SELECT tag_name AS name, usage_count AS count 
        FROM hashtags 
        WHERE tag_name LIKE CONCAT(?, '%')
        ORDER BY usage_count DESC
        LIMIT 5
    ");
    $stmt->bind_param('s', $query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
} else {
    // Search for users - Remove reference to non-existent followers_count column
    $stmt = $conn->prepare("
        SELECT 
            user_name AS username, 
            display_name, 
            profile_picture_url AS profile_picture
        FROM users
        WHERE user_name LIKE CONCAT(?, '%') OR display_name LIKE CONCAT('%', ?, '%')
        ORDER BY 
            CASE WHEN user_name = ? THEN 0
                 WHEN user_name LIKE CONCAT(?, '%') THEN 1
                 ELSE 2
            END
        LIMIT 5
    ");
    $stmt->bind_param('ssss', $query, $query, $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($results);