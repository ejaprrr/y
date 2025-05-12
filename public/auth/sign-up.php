<?php

require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/user.php";
require_once "../../src/components/layout.php";

start_session();
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
        session_regenerate_id(true); // Regenerate session ID to prevent fixation
        $_SESSION['user_id'] = $user_id;
        redirect('../app/index.php');
    } else {
        echo "Error creating user.";
        exit();
    }
}

?>

<?php render_header("sign up"); ?>

<style>
    :root {
        --background-color: #212529; /* Tmavé pozadí */
        --text-color: #f8f9fa; /* Světlejší text */
        --card-background: #343a40; /* Tmavší šedá pro kartu */
        --card-header-background: #495057; /* Ještě tmavší šedá pro hlavičku */
        --border-color: #6c757d; /* Jemný šedý okraj */
        --focus-border-color: #adb5bd; /* Světlejší šedá při focusu */
        --button-background: #e9ecef; /* Světle šedé pozadí pro tlačítko */
        --button-text-color: #212529; /* Tmavý text pro tlačítko */
        --button-hover-background: #dee2e6; /* Světlejší šedé pozadí při hoveru */
        --link-color: #adb5bd; /* Světlejší šedá pro odkazy */
        --link-hover-color: #f8f9fa; /* Bílá při hoveru */
    }

    body {
        background-color: var(--background-color);
        color: var(--text-color);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
    }
    .card {
        background-color: var(--card-background);
        border: none;
        color: var(--text-color);
    }
    .card-header {
        background-color: var(--card-header-background);
        border-bottom: 1px solid var(--border-color);
    }
    .form-control {
        background-color: var(--card-header-background);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    .form-control::placeholder {
        color: var(--link-color); /* Světlejší šedá pro placeholder text */
    }
    .form-control:focus {
        background-color: var(--card-header-background);
        color: var(--text-color);
        border-color: var(--focus-border-color);
        box-shadow: none;
    }
    .btn-primary {
        background-color: var(--button-background);
        color: var(--button-text-color);
        border: none;
    }
    .btn-primary:hover {
        background-color: var(--button-hover-background);
        color: var(--button-text-color);
    }
    a {
        color: var(--link-color);
        text-decoration: none;
        transition: color 0.3s ease;
    }
    a:hover {
        color: var(--link-hover-color);
        text-decoration: underline;
    }
    .logo {
        display: block;
        margin: 0 auto 20px auto; /* Centrované logo s mezerou pod ním */
        max-width: 150px;
    }
    .full-height {
        height: 100vh; /* Výška celé obrazovky */
    }
</style>

<div class="container full-height d-flex flex-column justify-content-center align-items-center">
    <!-- Logo umístěné nad kartou -->
    <img src="../assets/logo.png" alt="Logo" class="logo">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h3>Sign Up</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Username</label>
                        <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Enter your username (e.g., johndoe)" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter a strong password" required>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign Up</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <small>Already have an account? 
                    <a href="log-in.php" class="text-decoration-none">Log in</a>
                </small>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

