<?php
require_once "../resources/connection.php";
require_once "../resources/functions.php";
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
    <title>Y | sign up</title>
    <style>
        body { font-family: sans-serif; }
        .error { color: red; }
        form { margin-top: 1em; }
        #password-strength { height:8px; width:200px; background:#eee; margin-top:4px; }
        #password-bar { height:100%; width:0; background:#f00; transition:width 0.2s; }
    </style>
</head>
<body>
    <a href="login.php">log in</a>
    <h1>Sign up</h1>
    <?php if ($message): ?>
        <div class="error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" novalidate autocomplete="on">
        <label for="username">username:</label>
        <input type="text" id="username" name="user_name" required minlength="4" maxlength="20" autocomplete="username">
        <div id="username-msg" class="error" style="font-size:0.9em;"></div>
        <br>
        <label for="password">password:</label>
        <input type="password" id="password" name="password" required minlength="8" maxlength="64" autocomplete="new-password">
        <div id="password-msg" class="error" style="font-size:0.9em;"></div>
        <div id="password-strength">
            <div id="password-bar"></div>
        </div>
        <br>
        <input type="submit" value="sign up">
    </form>
    <script>
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const usernameMsg = document.getElementById('username-msg');
const passwordMsg = document.getElementById('password-msg');
const passwordBar = document.getElementById('password-bar');

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
    if (/[a-z]/.test(p)) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/\d/.test(p)) score++;
    if (/[^a-zA-Z0-9]/.test(p)) score++;
    return score;
}

usernameInput.addEventListener('input', () => {
    usernameMsg.textContent = validateUsername(usernameInput.value);
});

passwordInput.addEventListener('input', () => {
    passwordMsg.textContent = validatePassword(passwordInput.value);
    // Strength bar
    const score = passwordStrength(passwordInput.value);
    passwordBar.style.width = (score * 40) + "px";
    if (score <= 2) passwordBar.style.background = "#f00";
    else if (score === 3) passwordBar.style.background = "#fa0";
    else if (score === 4) passwordBar.style.background = "#cc0";
    else passwordBar.style.background = "#0a0";
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