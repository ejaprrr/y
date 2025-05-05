<?php

require_once '../../src/functions/helpers.php';
session_start();

session_unset(); 
session_destroy(); 

redirect('log-in.php');

?>