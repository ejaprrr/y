<?php
function validate_user_name($user_name) {
    if (empty($user_name)) {
        return "Username is required.";
    }
    if (strlen($user_name) < 3 || strlen($user_name) > 24) {
        return "Username must be between 3 and 24 characters.";
    }
    if (!preg_match('/^[a-z0-9_]+$/', $user_name)) {
        return "Username can only contain lowercase letters, numbers, and underscores.";
    }
    if ($user_name[0] === '_') {
        return "Username cannot start with an underscore.";
    }
    return true;
}

function validate_password($password) {
    if (empty($password)) {
        return "Password is required.";
    }
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        return "Password must contain at least one special character.";
    }
    return true;
}
?>
