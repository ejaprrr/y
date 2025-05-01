<?php
function start_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Redirects and exits
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fetch user from session or redirect to login
function get_user_from_session($conn) {
    start_session();
    if (!empty($_SESSION["user_name"])) {
        $user_name = $_SESSION["user_name"];
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_name = ? LIMIT 1");
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    redirect("login.php");
    // No return needed, redirect exits
}

// Find user by username
function find_user($conn, $user_name) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_name = ? LIMIT 1");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Register new user
function register_user($conn, $user_name, $password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (user_name, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $user_name, $password_hash);
    return $stmt->execute();
}

// Username validation with clear regex
function validate_username($user_name) {
    return preg_match('/^[a-zA-Z][a-zA-Z0-9_]{2,18}[a-zA-Z0-9]$/', $user_name) &&
           strpos($user_name, '__') === false &&
           substr($user_name, -1) !== '_';
}

// Password validation: strong password
function validate_password($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,64}$/', $password);
}
?>