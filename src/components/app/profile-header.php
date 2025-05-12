<?php

function render_profile_header($title, $subtitle = '', $back_url = 'index.php', $tabs = [], $is_sticky = true) {
    $header_class = $is_sticky ? 'profile-sticky-header' : '';
?>
    <div class="<?= $header_class ?>">
        <div class="profile-header-bar d-flex align-items-center p-3 border-bottom" style="border-color: var(--gray-700) !important;">
            <a href="<?= htmlspecialchars($back_url) ?>" class="text-decoration-none text-white me-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <div>
                <h3 class="m-0 fw-bold"><?= htmlspecialchars($title) ?></h3>
                <?php if (!empty($subtitle)): ?>
                    <small class=""><?= htmlspecialchars($subtitle) ?></small>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($tabs)): ?>
            <div class="profile-modern-tabs">
                <?php foreach ($tabs as $tab): ?>
                    <a href="<?= htmlspecialchars($tab['url']) ?>" 
                       class="tab <?= $tab['active'] ? 'active' : '' ?>">
                        <span><?= htmlspecialchars($tab['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php
}
?>