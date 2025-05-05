<?php

session_start();
require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/user.php";
require_once "../../src/components/layout.php";

set_csrf_token();

// handle sign up
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid = check_csrf_token();
    if (!$valid) {
        echo "Invalid CSRF token.";
        exit();
    }

    $user_name = sanitize_user_name($_POST['user_name'] ?? '');
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

    $user_exists = user_name_exists($conn, $user_name);
    if ($user_exists) {
        echo "User name already exists.";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $user_id = add_user($conn, $user_name, $hashed_password);
    if ($user_id) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user_id;
        redirect('../app/index.php');
    } else {
        echo "Error creating user.";
        exit();
    }
}

?>

<?php render_header("sign up"); ?>

<form method="POST">
    <input type="text" name="user_name">
    <input type="password" name="password">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="submit" value="sign up">
</form>    
<a href="log-in.php">log in</a>  

<?php render_footer(); ?>

