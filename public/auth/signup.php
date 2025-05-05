<?php
require_once __DIR__ . "/../../resources/connection.php";
require_once __DIR__ . "/../../resources/functions.php";
start_session();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_name = trim($_POST["user_name"] ?? "");
    $password = $_POST["password"] ?? "";

    if (validate_username($user_name) && validate_password($password)) {
        if (!find_user($conn, $user_name)) {
            if (register_user($conn, $user_name, $password)) {
                redirect("login.php");
            } else {
                $message = "Error registering user.";
            }
        } else {
            $message = "User already exists.";
        }
    } else {
        $message = "Username or password format is invalid";
    }
}

// Set page title
$page_title = "Y | Sign Up";

// Start output buffering to capture content for layout
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <!-- Logo and header -->
        <div class="text-center mb-4">
            <div class="logo-wrapper mb-3">
                <div class="logo-text">Y</div>
            </div>
            <h1 class="fs-3 fw-bold">Create your account</h1>
        </div>

        <!-- Alert for error messages -->
        <?php if ($message): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 mb-4">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Signup form -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="post" autocomplete="on" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start">
                                <i class="bi bi-at"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 rounded-end" id="username" name="user_name" 
                                  placeholder="Choose a username" required autocomplete="username">
                        </div>
                        <div id="username-msg" class="form-text text-danger small"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 rounded-end" id="password" name="password" 
                                  placeholder="Create a password" required autocomplete="new-password">
                        </div>
                        <div id="password-msg" class="form-text text-danger small"></div>
                        
                        <!-- Password Strength Bar -->
                        <div class="password-strength-container mt-2">
                            <div id="password-strength-bar" class="password-strength-bar"></div>
                        </div>
                        <div class="d-flex justify-content-between small mt-1">
                            <span class="form-text">Password strength:</span>
                            <span id="password-strength-text" class="form-text">None</span>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-medium">Sign up</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Login link card -->
        <div class="card border-0 shadow-sm rounded-4 text-center">
            <div class="card-body p-4">
                <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Log in</a></p>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        min-height: 100vh;
        display: flex;
        align-items: center;
    }
    
    .logo-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1da1f2, #0c66a0);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    .logo-text {
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
    }
    
    /* Password strength indicator */
    .password-strength-container {
        height: 6px;
        background-color: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .password-strength-bar {
        height: 100%;
        width: 0;
        transition: width 0.3s, background-color 0.3s;
    }
</style>

<script>
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const usernameMsg = document.getElementById('username-msg');
    const passwordMsg = document.getElementById('password-msg');
    const passwordStrengthBar = document.getElementById('password-strength-bar');
    const passwordStrengthText = document.getElementById('password-strength-text');

    function validateUsername(u) {
        if (u.length < 4 || u.length > 20) return "Username must be 4-20 characters.";
        if (!/^[a-zA-Z]/.test(u)) return "Username must start with a letter.";
        if (!/^[a-zA-Z0-9_]+$/.test(u)) return "Username can only contain letters, numbers, and underscores.";
        if (u.includes('__')) return "Username cannot have consecutive underscores.";
        if (u.endsWith('_')) return "Username cannot end with an underscore.";
        return "";
    }

    function validatePassword(p) {
        if (p.length < 8 || p.length > 64) return "Password must be 8-64 characters.";
        if (!/(?=.*[a-z])/.test(p)) return "Password needs at least one lowercase letter.";
        if (!/(?=.*[A-Z])/.test(p)) return "Password needs at least one uppercase letter.";
        if (!/(?=.*\d)/.test(p)) return "Password needs at least one number.";
        if (!/(?=.*[^a-zA-Z0-9])/.test(p)) return "Password needs at least one special character.";
        return "";
    }

    function passwordStrength(p) {
        let score = 0;
        if (p.length >= 8) score++;
        if (p.length >= 12) score++;
        if (/(?=.*[a-z])/.test(p)) score++;
        if (/(?=.*[A-Z])/.test(p)) score++;
        if (/(?=.*\d)/.test(p)) score++;
        if (/(?=.*[^a-zA-Z0-9])/.test(p)) score++;
        return score;
    }

    function updatePasswordStrength() {
        const p = passwordInput.value;
        const score = passwordStrength(p);
        
        // Update progress bar width based on score
        const percentage = (score / 6) * 100;
        passwordStrengthBar.style.width = `${percentage}%`;
        
        // Update color and text based on strength
        if (score <= 2) {
            passwordStrengthBar.style.backgroundColor = '#dc3545'; // Red
            passwordStrengthText.textContent = 'Weak';
            passwordStrengthText.className = 'form-text text-danger';
        } else if (score <= 4) {
            passwordStrengthBar.style.backgroundColor = '#ffc107'; // Yellow
            passwordStrengthText.textContent = 'Moderate';
            passwordStrengthText.className = 'form-text text-warning';
        } else {
            passwordStrengthBar.style.backgroundColor = '#198754'; // Green
            passwordStrengthText.textContent = 'Strong';
            passwordStrengthText.className = 'form-text text-success';
        }
    }

    usernameInput.addEventListener('input', () => {
        usernameMsg.textContent = validateUsername(usernameInput.value);
    });

    passwordInput.addEventListener('input', () => {
        passwordMsg.textContent = validatePassword(passwordInput.value);
        updatePasswordStrength();
    });

    document.querySelector('form').onsubmit = function(e) {
        const uMsg = validateUsername(usernameInput.value);
        const pMsg = validatePassword(passwordInput.value);
        
        if (uMsg) {
            usernameMsg.textContent = uMsg;
            usernameInput.focus();
            e.preventDefault();
            return;
        }
        
        if (pMsg) {
            passwordMsg.textContent = pMsg;
            passwordInput.focus();
            e.preventDefault();
            return;
        }
    };
</script>

<?php
$content = ob_get_clean();

// Custom layout for auth pages
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        /* Card styles to match main app */
        .card {
            transition: box-shadow 0.2s ease;
        }
        
        .card:hover {
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important;
        }
        
        /* Button styles to match */
        .btn-primary {
            background-color: #1da1f2;
            border-color: #1da1f2;
        }
        
        .btn-primary:hover {
            background-color: #1a91da;
            border-color: #1a91da;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(29, 161, 242, 0.25);
            border-color: #1da1f2;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <?php echo $content; ?>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>