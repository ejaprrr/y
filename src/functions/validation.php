<?php
// Validation functions
function validate_username($username) {
    if (empty($username)) {
        return "Username is required.";
    }
    if (strlen($username) < 3 || strlen($username) > 24) {
        return "Username must be between 3 and 24 characters.";
    }
    if (!preg_match('/^[a-z0-9_]+$/', $username)) {
        return "Username can only contain lowercase letters, numbers, and underscores.";
    }
    if ($username[0] === '_') {
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

function validate_post_content($content) {
    if (empty($content)) {
        return "Post content cannot be empty.";
    }
    if (strlen($content) > 256) {
        return "Post content cannot exceed 256 characters.";
    }
    return true;
}

// Sanitization functions
function sanitize_input($input) {
    return htmlspecialchars(trim($input));
}

function sanitize_post_content($content) {
    return sanitize_input($content);
}

function sanitize_username($username) {
    return strtolower(sanitize_input($username));
}

?>
