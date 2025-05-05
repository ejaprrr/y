<?php
require 'src/functions/auth.php';

function get_user() {
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        return $hashed_password;
    }
}
?>