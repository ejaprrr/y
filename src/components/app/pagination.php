<?php

function render_pagination($total_items, $items_per_page, $current_page, $base_url) {
    // Calculate total pages
    $total_pages = ceil($total_items / $items_per_page);
    
    // Don't show pagination if only one page
    if ($total_pages <= 1) {
        return;
    }
    
    // Determine page range to display (show up to 5 pages)
    $range = 2; // 2 pages before and after current page
    $start_page = max(1, $current_page - $range);
    $end_page = min($total_pages, $current_page + $range);
    
    // Parse the base url to handle existing query parameters
    $url_parts = parse_url($base_url);
    $query = [];
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $query);
    }
    
    // Base path without query
    $path = $url_parts['path'];
    
    // Function to build URL with page parameter
    $build_url = function($page) use ($path, $query) {
        $query['page'] = $page;
        return $path . '?' . http_build_query($query);
    };
?>
<nav aria-label="posts pagination" class="d-flex justify-content-center my-4">
    <ul class="pagination">
        <!-- previous button -->
        <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= ($current_page > 1) ? $build_url($current_page - 1) : '#' ?>" aria-label="previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <!-- First page (if not in range) -->
        <?php
 if ($start_page > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= $build_url(1) ?>">1</a></li>
            <?php
 if ($start_page > 2): ?>
                <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>
            <?php
 endif; ?>
        <?php
 endif; ?>
        
        <!-- Page numbers -->
        <?php
 for ($i = $start_page; $i <= $end_page; $i++): ?>
            <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                <a class="page-link" href="<?= $build_url($i) ?>"><?= $i ?></a>
            </li>
        <?php
 endfor; ?>
        
        <!-- Last page (if not in range) -->
        <?php
 if ($end_page < $total_pages): ?>
            <?php
 if ($end_page < $total_pages - 1): ?>
                <li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>
            <?php
 endif; ?>
            <li class="page-item">
                <a class="page-link" href="<?= $build_url($total_pages) ?>"><?= $total_pages ?></a>
            </li>
        <?php
 endif; ?>
        
        <!-- Next button -->
        <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= ($current_page < $total_pages) ? $build_url($current_page + 1) : '#' ?>" aria-label="next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php
}
?>