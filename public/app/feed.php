<?php
require_once "../resources/connection.php";
require_once "../resources/functions.php";
start_session();

$user = get_user_from_session($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Y | feed</title>
    <style>
        body { font-family: sans-serif; }
    </style>
</head>
<body>
    <a href="logout.php">log out</a>
    <h1>this is the feed page</h1>
    <span>Hello, <?php echo htmlspecialchars($user['user_name']); ?>!</span>
</body>
</html>