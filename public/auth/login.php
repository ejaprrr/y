<?php
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_name = trim($_POST["user_name"] ?? "");
    $password = $_POST["password"] ?? "";

    if (validate_username($user_name) && validate_password($password)) {
        $user = find_user($conn, $user_name);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION["user_name"] = $user['user_name'];
            redirect("../app/feed.php");
        } else {
            $message = "Invalid username or password";
        }
    } else {
        $message = "Username or password format is invalid";
    }
}

// Set page title
$page_title = "Y | Log In";

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
            <h1 class="fs-3 fw-bold">Log in to Y</h1>
        </div>

        <!-- Alert for error messages -->
        <?php if ($message): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 mb-4">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Login form -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="post" autocomplete="on" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 rounded-end" id="username" name="user_name" 
                                  placeholder="Enter your username" required autocomplete="username">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 rounded-end" id="password" name="password" 
                                  placeholder="Enter your password" required autocomplete="current-password">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-medium">Log in</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Sign up link card -->
        <div class="card border-0 shadow-sm rounded-4 text-center">
            <div class="card-body p-4">
                <p class="mb-0">Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a></p>
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
    
    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .hover-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
        transform: translateY(-2px);
    }
</style>

<script>
    document.querySelector('form').onsubmit = function(e) {
        const u = document.getElementById('username').value;
        const p = document.getElementById('password').value;
        const usernameOk = /^[a-zA-Z][a-zA-Z0-9_]{2,18}[a-zA-Z0-9]$/.test(u) && !u.includes('__') && !u.endsWith('_');
        const passwordOk = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,64}$/.test(p);
        
        if (!usernameOk || !passwordOk) {
            const alertHTML = `
                <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4">
                    <strong>Invalid format:</strong><br>
                    Username: 4-20 chars, start with letter, only letters/numbers/underscore, no consecutive or trailing underscores.<br>
                    Password: 8-64 chars, at least 1 uppercase, 1 lowercase, 1 digit, 1 special char.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.querySelector('.col-md-5').insertAdjacentHTML('afterbegin', alertHTML);
            e.preventDefault();
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