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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Y | Sign Up</title>
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
        #password-strength {
            height: 5px;
            transition: width 0.2s;
            background-color: #eee;
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
                            <h2 class="fw-bold">Create your account</h2>
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
                                        <i class="bi bi-at"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="username" name="user_name" 
                                           placeholder="Choose a username" required autocomplete="username">
                                </div>
                                <div id="username-msg" class="form-text text-danger"></div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" 
                                           placeholder="Create a password" required autocomplete="new-password">
                                </div>
                                <div id="password-msg" class="form-text text-danger"></div>
                                <div class="mt-2">
                                    <div id="password-strength" class="w-100 rounded"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="form-text">Password strength:</span>
                                    <span id="password-strength-text" class="form-text">None</span>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary py-2 fw-semibold">Sign up</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p>Already have an account? <a href="login.php" class="text-decoration-none">Log in</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const usernameMsg = document.getElementById('username-msg');
        const passwordMsg = document.getElementById('password-msg');
        const passwordStrengthBar = document.getElementById('password-strength');
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
            if (p.length < 8) return "Password must be at least 8 characters.";
            if (p.length > 64) return "Password must be at most 64 characters.";
            if (!/[a-z]/.test(p)) return "Password must contain a lowercase letter.";
            if (!/[A-Z]/.test(p)) return "Password must contain an uppercase letter.";
            if (!/\d/.test(p)) return "Password must contain a digit.";
            if (!/[^a-zA-Z0-9]/.test(p)) return "Password must contain a special character.";
            return "";
        }

        function passwordStrength(p) {
            let score = 0;
            if (p.length >= 8) score++;
            if (p.length >= 12) score++;
            if (/[a-z]/.test(p)) score++;
            if (/[A-Z]/.test(p)) score++;
            if (/\d/.test(p)) score++;
            if (/[^a-zA-Z0-9]/.test(p)) score++;
            return score;
        }

        function updatePasswordStrength() {
            const p = passwordInput.value;
            const score = passwordStrength(p);
            
            // Update strength bar
            const percentage = (score / 6) * 100;
            passwordStrengthBar.style.width = `${percentage}%`;
            
            // Update color and text
            if (score <= 2) {
                passwordStrengthBar.style.backgroundColor = '#dc3545';
                passwordStrengthText.textContent = 'Weak';
                passwordStrengthText.className = 'form-text text-danger';
            } else if (score <= 4) {
                passwordStrengthBar.style.backgroundColor = '#ffc107';
                passwordStrengthText.textContent = 'Moderate';
                passwordStrengthText.className = 'form-text text-warning';
            } else {
                passwordStrengthBar.style.backgroundColor = '#198754';
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
</body>
</html>