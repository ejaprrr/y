<?php
require_once __DIR__ . '/../../config/app.php';


function redirect($path) {
    header("Location: $path");
    exit();
}

function check_login() {
    start_session();
    return isset($_SESSION['user_id']);
}

function log_out() {
    session_unset(); 
    session_destroy(); 

    redirect('../auth/log-in.php');
}

function set_endpoint_header() {
    header('Content-Type: application/json');
}

function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();

        if (empty($_SESSION['created'])) {
            $_SESSION['created'] = time();
            $_SESSION['last_regenerate'] = time();
        }

        if (empty($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    } else {
        if (isset($_SESSION['last_activity']) &&
            time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            log_out();
        }

        $_SESSION['last_activity'] = time();

        if (isset($_SESSION['last_regenerate']) &&
            (time() - $_SESSION['last_regenerate'] > SESSION_TIMEOUT)) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();
        }
    }
}

function format_time_ago($date_string) {
    $timestamp = strtotime($date_string);
    $current_time = time();
    $diff = $current_time - $timestamp;
    
    // Just now
    if ($diff < 10) {
        return 'just now';
    }
    
    // Seconds ago
    if ($diff < 60) {
        return $diff . 's ago';
    }
    
    // Minutes ago
    if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . 'm ago';
    }
    
    // Hours ago
    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . 'h ago';
    }
    
    // Check if same year
    $current_year = date('Y', $current_time);
    $post_year = date('Y', $timestamp);
    
    if ($current_year == $post_year) {
        // Same year - show day and month
        return date('j M', $timestamp);
    }
    
    // Different year - show day, month and year
    return date('j M Y', $timestamp);
}

?>