<?php

function redirect($path) {
    header("Location: $path");
    exit();
}

function check_login() {
    session_start();
    if (!isset($_SESSION['user_name'])) {
        redirect('../auth/log-in.php');
    }
}
?>