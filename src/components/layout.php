<?php

require_once '../../config/app.php';

function render_header($title) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Eliáš Jan Procházka, Filip Nagy, Petr Novák">
    <meta name="description" content="a social platform inspired by X, developed as a school project at VOŠ a SPŠE Plzeň.">
    <meta name="keyword" content="social, platform, X, twitter, school project, VOŠ a SPŠE Plzeň">

    <title><?= APP_NAME ?> | <?= $title ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/common.css">


    <style>
        :root {
            --gray-900: #101010; 
            --gray-800: #202020;
            --gray-700: #404040;
            --gray-600: #606060;
            --gray-500: #808080;
            --gray-400: #c0c0c0;
            --gray-300: #e0e0e0;
            --gray-200: #f0f0f0;
            --gray-100: #ffffff;
            --blue: #4895ef;
            --red: #ff6f61;
        }

        body, html {
            font-family: 'Outfit', sans-serif;
            height: 100%;
            margin: 0;
            background-color: var(--gray-900);
            color: var(--gray-100) !important;
        }
    </style>
</head>
<body>
<?php
}

function render_footer() {
?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
<?php
}

?>