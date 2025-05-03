<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Store referrer to redirect back after action
$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'feed.php';

// Handle follow all action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usernames'])) {
    $usernames = json_decode($_POST['usernames'], true);
    $success_count = 0;
    
    if (is_array($usernames)) {
        foreach ($usernames as $username) {
            // Double-check that we're not following this user (defensive programming)
            if ($username !== $user['user_name'] && !is_following($conn, $user['user_name'], $username)) {
                if (follow_user($conn, $user['user_name'], $username)) {
                    $success_count++;
                }
            }
        }
    }
    
    // Set success message
    if ($success_count > 0) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => "Successfully followed $success_count new " . ($success_count === 1 ? "user" : "users")
        ];
    } else {
        $_SESSION['message'] = [
            'type' => 'info',
            'text' => 'No new users were followed'
        ];
    }
}

// Redirect back
redirect($redirect_url);
?>