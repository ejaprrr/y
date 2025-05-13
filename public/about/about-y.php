<?php
require_once "../../src/components/layout.php";
require_once "../../src/components/auth/sidebar.php";

render_header("about us");
?>
<link rel="stylesheet" href="../assets/css/pages/auth.css">
<link rel="stylesheet" href="../assets/css/components/container.css">
<link rel="stylesheet" href="../assets/css/components/sidebar.css">

<div class="d-flex h-100">
    <!-- Sidebar -->
    <?php render_sidebar(); ?>

    <!-- Content Area -->
    <div class="content-area d-flex justify-content-center align-items-center">
        <div class="card form-wrapper rounded-4 overflow-hidden" style="max-width: 450px;">
            <div class="card-header p-3">
                <h3 class="text-center m-0 text-light text-lowercase">about y</h3>
                <span class="text-center w-100 d-block text-light text-lowercase">meet the team behind y</span>
            </div>
            <div class="card-body p-4">
                <!-- Project Description -->
                <div class="text-center mb-4">
                    <p class="fs-6 text-light ">
                        social platform inspired by &#x1D54F; (formerly twitter), developed as a school project 
                        at voš a spše plzeň.
                    </p>
                </div>

                <!-- Development Team -->
                <div class="text-center mb-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-database fs-4 text-white"></i>
                            <div class="text-start">
                                <h6 class="mb-0 text-white">Eliáš Jan Procházka</h6>
                                <small class="text-white-50 ">backend development & database</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-brush fs-4 text-white"></i>
                            <div class="text-start">
                                <h6 class="mb-0 text-white">Filip Nagy</h6>
                                <small class="text-white-50 ">css & ui styling</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-code-slash fs-4 text-white"></i>
                            <div class="text-start">
                                <h6 class="mb-0 text-white">Petr Novák</h6>
                                <small class="text-white-50 ">html implementation</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-box-arrow-up-right fs-4 text-white"></i>
                            <div class="text-start">
                                <h6 class="mb-0 text-white ">github repository</h6>
                                <small>
                                    <a href="https://github.com/ejaprrr/y" 
                                       class="text-white-50 text-decoration-underline "
                                       style="transition: color 0.2s ease-in-out;"
                                       onmouseover="this.classList.replace('text-white-50', 'text-white')"
                                       onmouseout="this.classList.replace('text-white', 'text-white-50')">
                                        github.com/ejaprrr/y
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Features -->
                <div class="d-flex flex-column gap-2 text-center">
                    <small class="text-white-50 text-lowercase">features include secure authentication,</small>
                    <small class="text-white-50 text-lowercase">interactive posts, and responsive design.</small>
                    <small class="text-white-50 mt-2 text-lowercase">academic year 2024/2025</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>