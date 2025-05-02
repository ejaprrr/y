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
    <title>Y | log in</title>
    <style>
        body { font-family: sans-serif; }
        .error { color: red; }
        form { margin-top: 1em; }
    </style>
</head>
<body>
    <a href="signup.php">sign up</a>
    <h1>Log in</h1>
    <?php if ($message): ?>
        <div class="error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="on" novalidate>
        <label for="username">username:</label>
        <input type="text" id="username" name="user_name" required pattern="[a-zA-Z][a-zA-Z0-9_]{2,18}[a-zA-Z0-9]" minlength="4" maxlength="20" autocomplete="username">
        <br>
        <label for="password">password:</label>
        <input type="password" id="password" name="password" required minlength="8" maxlength="64" autocomplete="current-password">
        <br>
        <input type="submit" value="log in">
    </form>
    <script>
document.querySelector('form').onsubmit = function(e) {
    const u = document.getElementById('username').value;
    const p = document.getElementById('password').value;
    const usernameOk = /^[a-zA-Z][a-zA-Z0-9_]{2,18}[a-zA-Z0-9]$/.test(u) && !u.includes('__') && !u.endsWith('_');
    const passwordOk = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,64}$/.test(p);
    if (!usernameOk || !passwordOk) {
        alert('username: 4-20 chars, start with letter, only letters/numbers/underscore, no consecutive or trailing underscores.\nPassword: 8-64 chars, at least 1 uppercase, 1 lowercase, 1 digit, 1 special char.');
        e.preventDefault();
    }
};
    </script>
</body>
</html>