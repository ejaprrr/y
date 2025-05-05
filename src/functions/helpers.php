<?php

function redirect($path) {
    header("Location: $path");
    exit();
}

function check_login() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        redirect('../auth/log-in.php');
    }
}
?>