<?php
require_once "connection.php";

function regenerate_csrf_token($check = true) {
    if ($check && $_SERVER["REQUEST_METHOD"] === "POST") {
        return;
    }

    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION["csrf_token"] = $csrf_token;
}

function check_csrf_token() {    
    if (empty($_SESSION["csrf_token"])) {
        return false;
    }
    return hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "");
}

function check_ajax_csrf_token() {
    if (empty($_POST["csrf_token"])) {
        return false;
    }

    return hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "");
}

function get_user_id_from_session() {
    return $_SESSION["user_id"] ?? null;
}

function verify_user($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $hashed_password = $row["password_hash"];
        $user_id = $row["id"];
        $stmt->close();
        if (password_verify($password, $hashed_password)) {
            return $user_id;
        }
    }
    
    return false;
}

?>