<?php
function render_sidebar() {
?>
<div class="sidebar d-flex w-25 flex-column justify-content-between align-items-center text-center">
    <div class="logo-wrapper d-flex flex-column justify-content-center align-items-center flex-grow-1 gap-3">
        <img src="../assets/logo.svg" alt="logo" class="logo-svg w-75">
        <span class="fs-5 text-lowercase">it's ahead of &#x1D54F;.</span>
    </div>
    <div class="links-wrapper d-flex w-100 mt-auto gap-3 justify-content-center">
        <a href="index.php" class="d-block mb-3 text-lowercase">home</a>
        <a href="#" class="d-block mb-3 text-lowercase">about y</a>
        <span class="mb-3 text-lowercase">&copy; y, 2025</span>
    </div>
</div>
<?php
}
?>