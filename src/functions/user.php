<?php
function exists_user($conn, $user_name) {
    $stmt = $conn->prepare("SELECT user_name FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return true;
    }
}

function add_user($conn, $user_name, $hashed_password) {
    $stmt = $conn->prepare("INSERT INTO users (user_name, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $user_name, $hashed_password);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        return true;
    }

    return false;
}

?>