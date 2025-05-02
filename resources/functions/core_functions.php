<?php
/**
 * Core utility functions used throughout the application
 */

/**
 * Start a new session if one isn't already active
 */
function start_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Redirect to a URL and exit
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Format a timestamp into a human-readable string
 *
 * @param string $timestamp MySQL timestamp
 * @return string Formatted date/time
 */
function format_timestamp($timestamp) {
    $date = new DateTime($timestamp);
    $now = new DateTime();
    $diff = $date->diff($now);
    
    if ($diff->y > 0) {
        return $date->format('M j, Y');
    } elseif ($diff->m > 0 || $diff->d > 6) {
        return $date->format('M j');
    } elseif ($diff->d > 0) {
        return $diff->d . 'd';
    } elseif ($diff->h > 0) {
        return $diff->h . 'h';
    } elseif ($diff->i > 0) {
        return $diff->i . 'm';
    } else {
        return 'just now';
    }
}
?>