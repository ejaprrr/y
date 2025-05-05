<?php

require_once '../../config/app.php';

function render_header($title) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | <?= $title ?></title>
</head>
<body>
<?php
}

function render_footer() {
?>
</body>
</html>
<?php
}

?>