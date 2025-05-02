<?php
require_once __DIR__ . '/../../resources/functions.php';

// Use the abstracted function instead
logout();

// Redirect to login page
redirect("login.php");
?>