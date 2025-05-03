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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Y | Log In</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom styles -->
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background-color: #f7f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
        }
        .logo {
            font-size: 3rem;
            font-weight: 700;
            color: #1da1f2;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #1da1f2;
        }
        .btn-primary {
            background-color: #1da1f2;
            border-color: #1da1f2;
        }
        .btn-primary:hover {
            background-color: #1a91da;
            border-color: #1a91da;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card my-5">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <div class="logo">Y</div>
                            <h2 class="fw-bold">Log in to Y</h2>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" autocomplete="on" novalidate>
                            <div class="mb-4">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="username" name="user_name" 
                                           placeholder="Enter your username" required autocomplete="username">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" 
                                           placeholder="Enter your password" required autocomplete="current-password">
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary py-2 fw-semibold">Log in</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p>Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('form').onsubmit = function(e) {
            const u = document.getElementById('username').value;
            const p = document.getElementById('password').value;
            const usernameOk = /^[a-zA-Z][a-zA-Z0-9_]{2,18}[a-zA-Z0-9]$/.test(u) && !u.includes('__') && !u.endsWith('_');
            const passwordOk = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,64}$/.test(p);
            
            if (!usernameOk || !passwordOk) {
                const alertHTML = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Invalid format:</strong><br>
                        Username: 4-20 chars, start with letter, only letters/numbers/underscore, no consecutive or trailing underscores.<br>
                        Password: 8-64 chars, at least 1 uppercase, 1 lowercase, 1 digit, 1 special char.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                document.querySelector('.logo').insertAdjacentHTML('afterend', alertHTML);
                e.preventDefault();
            }
        };
    </script>
</body>
</html>