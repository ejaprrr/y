<?php
/**
 * Reusable tab navigation component
 * 
 * Expected variables:
 * $tabs = [
 *   [
 *     'id' => 'tab_id',         // Unique ID for the tab
 *     'label' => 'Tab Label',   // Display text
 *     'url' => 'url_to_tab',    // Full URL for the tab
 *     'icon' => 'bi-icon-name'  // Bootstrap icon class without the 'bi-' prefix
 *   ],
 * ]
 * $active_tab = 'current_tab_id' // Currently active tab ID
 */
?>

<div class="feed-tabs mb-3">
    <?php foreach ($tabs as $tab): ?>
        <a href="<?php echo htmlspecialchars($tab['url']); ?>" 
           class="feed-tab d-flex align-items-center py-2 px-3 text-decoration-none rounded-3 me-2 <?php echo $active_tab === $tab['id'] ? 'active bg-light' : 'text-muted'; ?>">
            <i class="bi bi-<?php echo htmlspecialchars($tab['icon']); ?> text-primary me-2"></i>
            <span class="<?php echo $active_tab === $tab['id'] ? 'text-primary fw-semibold' : ''; ?>">
                <?php echo htmlspecialchars($tab['label']); ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>

<style>
    .feed-tabs {
        display: flex;
        overflow-x: auto;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }
    
    .feed-tabs::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }
    
    .feed-tab {
        border-radius: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    .feed-tab:hover:not(.active) {
        background-color: rgba(0, 0, 0, 0.03);
    }
    
    .feed-tab.active .bi {
        opacity: 1;
    }
</style>