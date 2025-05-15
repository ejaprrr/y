<?php

require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/auth/sidebar.php";
require_once "../../src/components/auth/form-container.php";

start_session();
set_csrf_token();

// check if user is logged in
if (check_login()) {
    redirect("../app/feed.php");
}

$error = "";

// handle log in
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid = check_csrf_token();
    if (!$valid) {
        $error = "invalid CSRF token";
    } else {
        $username = sanitize_username($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        $user_id = verify_user($conn, $username, $password);

        if ($user_id) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user_id;
            redirect("../app/feed.php");
        } else {
            $error = "invalid username or password";
        }
    }
}

?>

<?php render_header("log in"); ?>

<link rel="stylesheet" href="../assets/css/pages/auth.css">
<link rel="stylesheet" href="../assets/css/components/form-container.css">
<link rel="stylesheet" href="../assets/css/components/sidebar.css">

<div class="d-flex h-100">
    <?php render_sidebar(); ?>
    <?php render_form_container_start("welcome back!", "log back into your account."); ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger m-3"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label for="username" class="mb-2">username</label>
            <input type="text" class="form-control rounded-3 p-2" id="username" name="username" placeholder="enter your username" required value="<?= isset($_POST["username"]) ? htmlspecialchars($_POST["username"]) : "" ?>">
        </div>
        <div class="mb-4">
            <label for="password" class="mb-2">password</label>
            <input type="password" class="form-control rounded-3 p-2" id="password" name="password" placeholder="enter your password" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary rounded-3 p-2 fw-semibold" disabled>log in</button>
        </div>
    </form>
    <span class="mt-4 d-block w-100 text-center">don't have an account? <a href="sign-up.php">sign up</a></span>

    <?php render_form_container_end(); ?>
</div>

<script src="../assets/js/log-in.js"></script>

<?php render_footer(); ?>