<?php
// Core system functions (sessions, redirects, etc)
require_once __DIR__ . '/functions/core_functions.php';

// User-related functions
require_once __DIR__ . '/functions/user_functions.php';

// Post-related functions
require_once __DIR__ . '/functions/post_functions.php';

// Authentication functions
require_once __DIR__ . '/functions/auth_functions.php';

// Search functions
require_once __DIR__ . '/functions/search_functions.php';

// Tag functions (hashtags and mentions)
require_once __DIR__ . '/functions/tag_functions.php';

// Bookmark functions
require_once __DIR__ . '/functions/bookmark_functions.php';

// Notification functions
require_once __DIR__ . '/functions/notification_functions.php';

/**
 * Generate or retrieve CSRF token
 * 
 * @return string CSRF token
 */
function get_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Check if the current request is an AJAX request
 * 
 * @return bool True if AJAX request
 */
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>