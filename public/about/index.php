<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../../src/components/layout.php";
?>

<?php render_header("welcome"); ?>

<style>
    .splash-container {
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .splash-logo {
        width: 100px;
        height: 100px;
        margin-bottom: 2rem;
    }
    
    .splash-card {
        background-color: var(--gray-800);
        border-radius: 1rem;
        max-width: 600px;
        width: 100%;
        border: 1px solid var(--gray-700);
    }
    
    .action-buttons .btn {
        min-width: 120px;
    }
    
    .features-list {
        color: var(--gray-300);
        display: inline-block;
        margin: 0 auto;
    }
    
    .tagline {
        font-size: 1.5rem;
        font-weight: 300;
        margin-bottom: 2rem;
        color: var(--gray-200);
    }
    
    .gradient-text {
        background: linear-gradient(45deg, var(--blue), #8c52ff);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        font-weight: 600;
    }

    .underline {
        text-decoration-line: underline;
        text-underline-offset: 0.5rem;
        text-decoration-style: wavy;
    }
</style>

<div class="splash-container">
    <div class="text-center mb-4">
        <img src="../assets/logo.svg" alt="Y Logo" class="splash-logo">
        <h1 class="display-4 fw-bold mb-2">welcome to <span class="gradient-text">this <span class="underline">awesome</span> platform.</span></h1>
        <p class="tagline">it's ahead of &#x1D54F;.</p>
    </div>
    
    <div class="splash-card p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fs-4 mb-3">join the conversation today</h2>
            <p>connect with friends, share your thoughts, and explore what's happening right now.</p>
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
        <a href="about-y.php" class="d-block mb-3">about y</a>
        <span class="mb-3">&copy; Y, 2025</span>
    </div>
</div>

<?php render_footer(); ?>