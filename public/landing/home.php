<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../../src/components/layout.php";
?>

<?php render_header("welcome"); ?>

<link rel="stylesheet" href="../assets/css/pages/about.css">

<div class="splash-container">
    <div class="text-center mb-4">
        <img src="../assets/images/logo.svg" alt="Y logo" class="splash-logo">
        <h1 class="display-4 fw-bold mb-2">welcome to <span class="gradient-text">this <span class="underline">awesome</span> platform.</span></h1>
        <p class="tagline">it's ahead of &#x1D54F;.</p>
    </div>
    
    <div class="splash-card p-4">
        <div class="text-center mb-4">
            <h2 class="fs-4 mb-3">join the conversation today</h2>
            <p>connect with friends, share your thoughts, and explore what's happening <br> right now.</p>
        </div>
        
        <div class="d-flex justify-content-center mb-4">
            <div class="features-list">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-chat-text-fill me-3"></i>
                    <span>share your thoughts in short posts</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-heart-fill me-3"></i>
                    <span>like and interact with content</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-people-fill me-3"></i>
                    <span>follow people and build your network</span>
                </div>
            </div>
        </div>
        
        <div class="action-buttons d-flex flex-column flex-md-row justify-content-center gap-3 mt-4">
            <a href="../auth/sign-up.php" class="btn btn-outline-light fw-semibold rounded-3 p-2">sign up</a>
            <a href="../auth/log-in.php" class="btn btn-outline-light fw-semibold rounded-3 p-2">log in</a>
        </div>
    </div>
    
    <div class="links-wrapper d-flex mt-4 gap-3 justify-content-center">
        <a href="#" class="d-block mb-3">home</a>
        <a href="about-us.php" class="d-block mb-3">about us</a>
        <span class="mb-3">&copy; Y, 2025</span>
    </div>
</div>

<?php render_footer(); ?>