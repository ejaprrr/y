<?php
require_once "connection.php";

function set_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function check_csrf_token() {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Nesprávný požadavek.");
    }
}

function get_user_name_from_session() {
    return $_SESSION['user_name'] ?? null;
}

function verify_user($conn, $user_name, $password) {
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $hashed_password = $row['password_hash'];
        $stmt->close();
        return password_verify($password, $hashed_password);
    }
    
    $stmt->close();
    return false;
}

?>