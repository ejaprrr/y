<?php

require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/user.php";
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
$error_field = "";

// handle sign up
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valid = check_csrf_token();
    if (!$valid) {
        $error = "invalid CSRF token";
    } else {
        $username = sanitize_username($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        $username_validation = validate_username($username);
        if ($username_validation !== true) {
            $error = $username_validation;
            $error_field = "username";
        } else {
            $user_exists = username_exists($conn, $username);
            if ($user_exists) {
                $error = "username already exists";
                $error_field = "username";
            } else {
                $password_validation = validate_password($password);
                if ($password_validation !== true) {
                    $error = $password_validation;
                    $error_field = "password";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $user_id = add_user($conn, $username, $hashed_password);
                    if ($user_id) {
                        session_regenerate_id(true);
                        $_SESSION["user_id"] = $user_id;
                        redirect("../app/feed.php");
                    } else {
                        $error = "error creating user";
                    }
                }
            }
        }
    }
}

?>

<?php render_header("sign up"); ?>

<link rel="stylesheet" href="../assets/css/pages/auth.css">
<link rel="stylesheet" href="../assets/css/components/form-container.css">
<link rel="stylesheet" href="../assets/css/components/sidebar.css">

<div class="d-flex h-100">
    <?php render_sidebar(); ?>
    <?php render_form_container_start("hey there!", "start by creating an account."); ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger m-3"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label for="username" class="mb-2">username</label>
            <input type="text" class="form-control rounded-3 p-2 <?= ($error_field == "username") ? "is-invalid" : "" ?>" 
                  id="username" name="username" placeholder="enter your username" required 
                  value="<?= isset($_POST["username"]) ? htmlspecialchars($_POST["username"]) : "" ?>">
            <div id="username-feedback" class="form-text"></div>
        </div>
        <div class="mb-4">
            <label for="password" class="mb-2">password</label>
            <input type="password" class="form-control rounded-3 p-2 <?= ($error_field == "password") ? "is-invalid" : "" ?>" 
                  id="password" name="password" placeholder="enter a strong password" required>
            <div id="password-strength-bar" class="progress mt-2" style="height: 5px; background-color: var(--bs-gray-700);">
                <div id="password-strength-progress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div id="password-feedback" class="form-text mt-1"></div>
        </div>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary rounded-3 p-2 fw-semibold" disabled>sign up</button>
        </div>
    </form>
    <span class="mt-4 d-block w-100 text-center">already have an account? <a href="log-in.php">log in</a></span>
    
    <?php render_form_container_end(); ?>
</div>

<script src="../assets/js/pages/sign-up.js"></script>

<?php render_footer(); ?>

