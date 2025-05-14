<?php
require_once "../../src/functions/hashtag.php";

function render_right_sidebar() {
    global $conn;
    $trending_hashtags = get_trending_hashtags($conn, 3);
?>
<div class="right-sidebar d-flex flex-column">
    <div class="p-3">
        <!-- Quick search bar -->
        <div class="quick-search-container mb-3">
            <form action="search.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control rounded-start-3" name="keyword" placeholder="search..." aria-label="Search">
                    <button class="btn btn-primary rounded-end-3" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="card rounded-4 mb-4">
            <div class="p-3">
                <h3 class="fs-5 mb-3">trending hashtags</h3>
                <div>
                    <?php if (!empty($trending_hashtags)): ?>
                        <?php foreach ($trending_hashtags as $index => $trend): ?>
                            <a href="../app/hashtag.php?tag=<?= htmlspecialchars($trend['hashtag']) ?>" class="hashtag-link text-decoration-none">
                                <div class="hover-highlight p-3 rounded-3 <?= $index < count($trending_hashtags) - 1 ? 'mb-1' : '' ?>">
                                    <div class="fw-bold">
                                        #<?= htmlspecialchars($trend['hashtag']) ?>
                                    </div>
                                    <small><?= number_format($trend['count']) ?> posts</small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mb-3 pb-2 separator">
                            <div class="fw-bold">#notrendsyet</div>
                            <small>be the first to create a trend!</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="links-wrapper d-flex w-100 mt-auto gap-3 justify-content-center">
            <a href="../landing/home.php" class="d-block mb-3 text-lowercase">home</a>
            <a href="../landing/about-us.php" class="d-block mb-3 text-lowercase">about us</a>
            <span class="mb-3">&copy; Y, 2025</span>
        </div>
    </div>
</div>
<?php
}
?>
