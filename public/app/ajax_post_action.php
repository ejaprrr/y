<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

// Verify AJAX request and set response header
header('Content-Type: application/json');

$user = get_user_from_session($conn);
$response = ['success' => false];

if (!$user) {
    $response['error'] = 'Authentication required';
    echo json_encode($response);
    exit;
}

// CSRF protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $response['error'] = 'Invalid security token';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    
    if ($post_id <= 0) {
        $response['error'] = 'Invalid post ID';
        echo json_encode($response);
        exit;
    }
    
    switch ($action) {
        case 'like':
            if (has_liked($conn, $user['user_name'], $post_id)) {
                unlike_post($conn, $user['user_name'], $post_id);
                $response['liked'] = false;
            } else {
                like_post($conn, $user['user_name'], $post_id);
                $response['liked'] = true;
            }
            $response['success'] = true;
            $response['like_count'] = get_like_count($conn, $post_id);
            break;
            
        case 'repost':
            if (has_reposted($conn, $user['user_name'], $post_id)) {
                unrepost_post($conn, $user['user_name'], $post_id);
                $response['reposted'] = false;
            } else {
                repost_post($conn, $user['user_name'], $post_id);
                $response['reposted'] = true;
            }
            $response['success'] = true;
            $response['repost_count'] = get_repost_count($conn, $post_id);
            break;
            
        case 'bookmark':
            if (has_bookmarked($conn, $user['user_name'], $post_id)) {
                unbookmark_post($conn, $user['user_name'], $post_id);
                $response['bookmarked'] = false;
            } else {
                bookmark_post($conn, $user['user_name'], $post_id);
                $response['bookmarked'] = true;
            }
            $response['success'] = true;
            break;
            
        default:
            $response['error'] = 'Invalid action';
    }
}

echo json_encode($response);