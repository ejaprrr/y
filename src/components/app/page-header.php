<?php

function render_page_header($title, $subtitle = "", $back_url = "", $tabs = [], $is_sticky = true) {
    $header_class = $is_sticky ? "sticky-header" : "";
    
?>
    <div class="<?= $header_class ?>">
        <div class="profile-header-bar d-flex align-items-center p-3 border-bottom" style="border-color: var(--gray-700) !important;">
            <?php if (!empty($back_url)): ?>
                <a href="<?= htmlspecialchars($back_url) ?>" class="text-decoration-none text-white me-3">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
            <?php endif; ?>
            <div>
                <h3 class="m-0 fw-bold"><?= htmlspecialchars($title) ?></h3>
                <?php if (!empty($subtitle)): ?>
                    <small class=""><?= htmlspecialchars($subtitle) ?></small>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($tabs)): ?>
            <div class="tabs">
                <?php foreach ($tabs as $tab): ?>
                    <a href="<?= htmlspecialchars($tab["url"]) ?>" 
                       class="tab <?= $tab["active"] ? "active" : "" ?>">
                        <span><?= htmlspecialchars($tab["label"]) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php
}
?>