<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Store referrer to redirect back after deletion
$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'feed.php';

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    
    if (delete_post($conn, $post_id, $user['user_name'])) {
        // Set success message in session
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Post deleted successfully'
        ];
    } else {
        // Set error message in session
        $_SESSION['message'] = [
            'type' => 'danger',
            'text' => 'Failed to delete post or you do not have permission'
        ];
    }
}

// Redirect back to where the user came from
redirect($redirect_url);
?>