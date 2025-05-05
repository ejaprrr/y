<?php
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

function get_user_from_session() {
    return $_SESSION['user_name'] ?? null;
}

function verify_user($username, $password) {
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        return password_verify($password, $hashed_password);
    }
}

?>