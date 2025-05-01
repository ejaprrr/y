<?php

include_once "../config/database.php";

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("database connection failed: " . htmlspecialchars(mysqli_connect_error()));
}
?>