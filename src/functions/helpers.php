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

?>