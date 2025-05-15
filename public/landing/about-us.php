<?php
require_once "../../src/components/layout.php";

render_header("about us");
?>

<link rel="stylesheet" href="../assets/css/pages/about.css">

<div class="splash-container">
    <div class="text-center mb-4">
        <img src="../assets/images/logo.svg" alt="Y logo" class="splash-logo">
        <h1 class="display-4 fw-bold mb-2">about <span class="gradient-text">us, the <span class="underline">creators</span>.</span></h1>
        <p class="tagline">meet the team behind</p>
    </div>
    
    <div class="splash-card p-4">
        <div class="text-center mb-4">
            <p class="fs-5">
                social platform inspired by &#x1D54F; (formerly twitter) <br>
                developed as a school project at <a href="https://www.spseplzen.cz">VOŠ a SPŠE Plzeň</a>.
            </p>
        </div>
        
        <!-- dev team -->
        <div class="d-flex justify-content-center mb-4">
            <div class="team-list">
                <div class="team-member">
                    <div class="text-start">
                        <h6 class="mb-0 text-white">Eliáš Jan Procházka</h6>
                        <small class="text-white-50">backend development & database</small>
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="text-start">
                        <h6 class="mb-0 text-white">Filip Nagy</h6>
                        <small class="text-white-50">css & ui styling</small>
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="text-start">
                        <h6 class="mb-0 text-white">Petr Novák</h6>
                        <small class="text-white-50">html implementation</small>
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="text-start">
                        <h6 class="mb-0 text-white">github repository</h6>
                        <small>
                            <a href="https://github.com/ejaprrr/y">https://github.com/ejaprrr/y</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- key features -->
        <div class="d-flex flex-column gap-2 text-center">
            <p class="text-white-50 mb-0">features include secure authentication,</p>
            <p class="text-white-50 mb-3">interactive posts, and user customization.</p>
            <p class="text-white-50 mt-2"><small>academic year 2024/2025</small></p>
        </div>
    </div>
    
    <div class="links-wrapper d-flex mt-4 gap-3 justify-content-center">
        <a href="home.php" class="d-block mb-3">home</a>
        <a href="#" class="d-block mb-3">about us</a>
        <span class="mb-3">&copy; Y, 2025</span>
    </div>
</div>

<?php
 render_footer(); ?>