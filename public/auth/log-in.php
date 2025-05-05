<?php

require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/components/layout.php";

start_session();
set_csrf_token();

// handle log in
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid = check_csrf_token();
    if (!$valid) {
        echo "Invalid CSRF token.";
        exit();
    }

    $user_name = sanitize_user_name($_POST['user_name'] ?? '');
    $password = $_POST['password'] ?? '';

    $user_id = verify_user($conn, $user_name, $password);

    if ($user_id) {
        session_regenerate_id(true); // Regenerate session ID to prevent fixation
        $_SESSION['user_id'] = $user_id;
        redirect('../app/index.php');
    } else {
        echo "Invalid username or password.";
        exit();
    }
}

?>

<?php render_header("log in"); ?>

<form method="POST">
    <input type="text" name="user_name">
    <input type="password" name="password">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="submit" value="log in">
</form>  
<a href="sign-up.php">sign up</a>  

<?php render_footer(); ?>