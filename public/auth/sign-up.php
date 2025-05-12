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
        --gray-900: #212529; /* nejtmavší */
        --gray-800: #343a40;
        --gray-700: #495057;
        --gray-600: #6c757d;
        --gray-500: #adb5bd;
        --gray-300: #dee2e6;
        --gray-200: #e9ecef;
        --gray-100: #f8f9fa; /* nejsvětlejší */
    }

    .auth-page {
        display: flex;
        height: 100%;
    }

    .sidebar {
        width: 25%;
        background-color: var(--gray-800);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .logo-svg {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 85%;
        max-width: 200px;
        height: auto;
        fill: var(--gray-100);
    }

    .content-area {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
    }

    .form-wrapper {
        width: 100%;
        max-width: 650px;
    }

    .card {
        background-color: var(--gray-800);
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: var(--gray-100);
    }

    .card-header {
        background-color: var(--gray-700);
        border-bottom: 1px solid var(--gray-600);
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 1.25rem;
    }

    .card-body {
        padding: 2rem;
    }

    .card-footer {
        background-color: transparent;
        border-top: 1px solid var(--gray-600);
        padding: 1.25rem;
    }

    .form-control {
        background-color: var(--gray-700);
        color: var(--gray-100);
        border: 1px solid var(--gray-600);
        padding: 0.75rem;
        border-radius: 5px;
    }

    .form-control::placeholder {
        color: var(--gray-500);
    }

    .form-control:focus {
        background-color: var(--gray-700);
        color: var(--gray-100);
        border-color: var(--gray-500);
        box-shadow: none;
    }

    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .btn-primary {
        background-color: var(--gray-200);
        color: var(--gray-900);
        border: none;
        padding: 0.75rem;
        font-weight: 600;
        border-radius: 5px;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background-color: var(--gray-300);
        transform: translateY(-1px);
        color: var(--gray-800);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    a {
        color: var(--gray-500);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    a:hover {
        color: var(--gray-300);
    }

    /* Responsive design */
    @media (max-width: 991.98px) {
        .auth-page {
            flex-direction: column;
        }
        
        .sidebar {
            width: 100%;
            height: 180px;
            min-height: auto;
        }
        
        .logo-container {
            width: 40%;
            max-width: 200px;
        }
        
        .content-area {
            padding: 2rem 1rem;
        }
    }
</style>

<div class="auth-page">
    <!-- Left sidebar with logo -->
    <div class="sidebar">
        <img src="../assets/logo.svg" alt="Logo" class="logo-svg">
    </div>

    <!-- Right content area with form -->
    <div class="content-area">
        <div class="form-wrapper">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="text-center m-0">Sign Up</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-4">
                            <label for="user_name" class="form-label">Username</label>
                            <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Enter your username" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter a strong password" required>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Sign Up</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small>Already have an account? 
                        <a href="log-in.php">Log in</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

