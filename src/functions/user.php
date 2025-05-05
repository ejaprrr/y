<?php
function user_name_exists($conn, $user_name) {
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

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $stmt->close();
        return $new_id;
    }

    $stmt->close();
    return false;
}

function get_user($conn, $user_id) {
    $stmt = $conn->prepare("SELECT user_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return null; 
    }

    return $user;
    
}

?>