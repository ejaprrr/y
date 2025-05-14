<?php
function render_form_container_start($title, $subtitle) {
?>
<div class="content-area d-flex justify-content-center align-items-center">
    <div class="card form-wrapper w-100 rounded-4 overflow-hidden">
        <div class="card-header p-3">
            <h3 class="text-center m-0"><?= $title ?></h3>
            <span class="text-center w-100 d-block"><?= $subtitle ?></span>
        </div>
        <div class="p-4">
<?php
}

function render_form_container_end() {
?>
        </div>
    </div>
</div>
<?php
}

?>