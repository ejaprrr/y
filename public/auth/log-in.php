<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

    $verified = verify_user($conn, $user_name, $password);

    if ($verified) {
        session_regenerate_id(true);
        $_SESSION['user_name'] = $user_name;
        redirect('../app/index.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log-in</title>
</head>
<body>
<form method="POST">
    <input type="text" name="user_name">
    <input type="password" name="password">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="submit" value="Log in">
</form>  
<a href="sign-up.php">Sign up</a>  
</body>
</html>

