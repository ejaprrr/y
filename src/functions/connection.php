<?php
require_once "../../config/database.php";

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    exit("connection failed: " . $conn->connect_error);
}

?>