<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: /public/auth/log-in.php");
    exit();
}

$user_name = htmlspecialchars($_SESSION['user_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body>
    <h1>Welcome, <?= $user_name ?>!</h1>
    <p>We're glad to have you here.</p>
    <a href="../auth/log-out.php">Log out</a>
</body>
</html>