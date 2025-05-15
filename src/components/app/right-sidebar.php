<?php
require_once "../../src/functions/hashtag.php";
require_once "../../src/functions/user.php";
require_once "../../src/components/app/empty-state.php";

function render_right_sidebar($conn) {
    // Get trending hashtags
    $trending_hashtags = get_trending_hashtags($conn, 3);
    
    // Get suggested users for the new section
    $suggested_users = get_suggested_users($conn, $_SESSION["user_id"], 3);
?>
<div class="right-sidebar d-flex flex-column">
    <div class="p-3">
        <!-- Quick search bar -->
        <div class="quick-search-container mb-3">
            <form action="search.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control rounded-start-3" name="keyword" placeholder="search..." aria-label="search">
                    <button class="btn btn-primary rounded-end-3" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Suggested users section -->
        <div class="card rounded-4 mb-4">
            <div class="p-3">
                <h3 class="fs-5 mb-3">suggested users</h3>
                <div>
                    <?php
 if (!empty($suggested_users)): ?>
                        <?php
 foreach ($suggested_users as $index => $suggested_user): ?>
                            <div class="hover-highlight p-3 rounded-3 d-flex <?= $index < count($suggested_users) - 1 ? "mb-1" : "" ?>">
                                <div class="d-flex align-items-center w-100">
                                    <!-- user avatar -->
                                    <a href="profile.php?username=<?= htmlspecialchars($suggested_user["username"]) ?>&origin=<?= get_clean_url() ?>" class="text-decoration-none d-flex align-items-center">
                                        <?php
 render_profile_picture($suggested_user); ?>
                                        <div class="ms-2">
                                            <!-- clickable display name -->
                                            <a href="profile.php?username=<?= htmlspecialchars($suggested_user["username"]) ?>&origin=<?= get_clean_url() ?>" class="text-decoration-none">
                                                <div class="user-link fw-bold"><?= htmlspecialchars($suggested_user["display_name"] ?? $suggested_user["username"]) ?></div>
                                            </a>
                                            <!-- clickable user name -->
                                            <small class="user-handle">
                                                @<?= htmlspecialchars($suggested_user["username"]) ?>
                                            </small>
                                        </div>
                                    </a>
                                    <!-- Follow button -->
                                    <button 
                                        class="btn btn-primary rounded-3 fw-semibold follow-btn ms-auto" 
                                        data-user-id="<?= $suggested_user["id"] ?>">
                                        follow
                                    </button>
                                </div>
                            </div>
                        <?php
 endforeach; ?>
                    <?php
 else: ?>
                        <?php
 render_empty_state("bi bi-at", "follow some users to see suggestions here."); ?>
                    <?php
 endif; ?>
                </div>
            </div>
        </div>

        <!-- Trending hashtags section -->
        <div class="card rounded-4 mb-4">
            <div class="p-3">
                <h3 class="fs-5 mb-3">trending hashtags</h3>
                <div>
                    <?php
 if (!empty($trending_hashtags)): ?>
                        <?php
 foreach ($trending_hashtags as $index => $trend): ?>
                            <a href="../app/hashtag.php?tag=<?= htmlspecialchars($trend["hashtag"]) ?>&origin=<?= get_clean_url() ?>" class="hashtag-link text-decoration-none">
                                <div class="hover-highlight p-3 rounded-3 <?= $index < count($trending_hashtags) - 1 ? "mb-1" : "" ?>">
                                    <div class="fw-bold">
                                        #<?= htmlspecialchars($trend["hashtag"]) ?>
                                    </div>
                                    <small><?= number_format($trend["count"]) ?> posts</small>
                                </div>
                            </a>
                        <?php
 endforeach; ?>
                    <?php
 else: ?>
                        <?php
 render_empty_state("bi bi-hash", "no trending hashtags at the moment."); ?>
                    <?php
 endif; ?>
                </div>
            </div>
        </div>
        
        <div class="links-wrapper d-flex w-100 mt-auto gap-3 justify-content-center">
        <a href="../landing/home.php" class="d-block mb-3 text-lowercase">home</a>
        <a href="../landing/about-us.php" class="d-block mb-3 text-lowercase">about us</a>
        <a href="../assets/downloads/y-project-info.zip" download class="d-block mb-3">download info</a>
        <span class="mb-3 text-lowercase">&copy; y, 2025</span>
    </div>
    </div>
</div>
<?php
}
?>
