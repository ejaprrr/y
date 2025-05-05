<?php
session_start();
require "/src/functions/connection.php";
require "/src/functions/auth.php";
require "/src/functions/validation.php";
require "/src/functions/helpers.php";

set_csrf_token();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    check_csrf_token();

    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    $username_validation = validate_username($username);
    if ($username_validation !== true) {
        echo $username_validation;
        exit();
    }

    $password_validation = validate_password($password);
    if ($password_validation !== true) {
        echo $password_validation;
        exit();
    }

    $verified = verify_user($username, $password);

    if ($verified) {
        session_regenerate_id(true);
        $_SESSION['user_name'] = $username;
        redirect('/app/index.php');
    }
    $stmt->close();
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
    <input type="text" name="username">
    <input type="password" name="password">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="submit" name="login">
</form>    
</body>
</html>

