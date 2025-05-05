<?php
session_start();
require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/helpers.php";

set_csrf_token();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    check_csrf_token();

    $user_name = strtolower(trim($_POST['user_name'] ?? ''));
    $password = $_POST['password'] ?? '';

    $user_name_validation = validate_user_name($user_name);
    if ($user_name_validation !== true) {
        echo $user_name_validation;
        exit();
    }

    $password_validation = validate_password($password);
    if ($password_validation !== true) {
        echo $password_validation;
        exit();
    }

    $user_exists = exists_user($user_name);
    if ($user_exists) {
        echo "User already exists.";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if (add_user($user_name, $hashed_password)) {
        session_regenerate_id(true);
        $_SESSION['user_name'] = $user_name;
        redirect('/app/index.php');
    } else {
        echo "Error creating user.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-up</title>
</head>
<body>
<form method="POST">
    <input type="text" name="user_name">
    <input type="password" name="password">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="submit" value="Sign up">
</form>    
</body>
</html>

