<?php
// Validation functions
function validate_username($username) {
    if (empty($username)) {
        return "username is required";
    }
    if (strlen($username) < 3 || strlen($username) > 24) {
        return "username must be between 3 and 24 characters";
    }
    if (!preg_match("/^[a-z0-9_]+$/", $username)) {
        return "username can only contain lowercase letters, numbers, and underscores";
    }
    if ($username[0] === "_") {
        return "username cannot start with an underscore";
    }
    return true;
}

function validate_password($password) {
    if (empty($password)) {
        return "password is required";
    }
    if (strlen($password) < 8) {
        return "password must be at least 8 characters long";
    }
    if (!preg_match("/[a-z]/", $password)) {
        return "password must contain at least one lowercase letter";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        return "password must contain at least one uppercase letter";
    }
    if (!preg_match("/[0-9]/", $password)) {
        return "password must contain at least one number";
    }
    if (!preg_match("/[\W_]/", $password)) {
        return "password must contain at least one special character";
    }
    return true;
}

function validate_post_content($content) {
    if (empty($content)) {
        return "post content cannot be empty";
    }
    if (strlen($content) > 256) {
        return "post content cannot exceed 256 characters";
    }
    return true;
}

// Sanitization functions
function sanitize_input($input) {
    return strip_tags(trim($input));
}

function sanitize_post_content($content) {
    return sanitize_input($content);
}

function sanitize_username($username) {
    return strtolower(sanitize_input($username));
}

?>
