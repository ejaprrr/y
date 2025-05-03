<div class="col-md-3 d-none d-lg-block position-sticky vh-100" style="top: 0;">
    <div class="right-sidebar-content p-3 h-100 d-flex flex-column">
        <!-- Search bar -->
        <div class="search-container mb-4 mt-2">
            <form action="/y/public/app/search.php" method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-pill-start">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="search" name="q" class="form-control border-start-0 bg-light rounded-pill-end" 
                        placeholder="Search Y" aria-label="Search"
                        value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                </div>
            </form>
        </div>
        
        <!-- Who to follow section -->
        <div class="who-to-follow-container mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h5 class="card-title fs-5 fw-bold">Who to follow</h5>
                </div>
                <div class="card-body pt-2">
                    <?php
                    // Get recommended users
                    $recommended_users = get_recommended_users($conn, $user['user_name'], 3);
                    $recommended_usernames = []; // To store usernames for "Follow all" button
                    
                    if (!empty($recommended_users)):
                    ?>
                        <?php foreach ($recommended_users as $recommended_user): 
                            // Double-check we're not following this user (defensive programming)
                            if (is_following($conn, $user['user_name'], $recommended_user['user_name'])) {
                                continue; // Skip to the next user if we're already following
                            }
                            $recommended_usernames[] = $recommended_user['user_name'];
                        ?>
                            <div class="suggested-user d-flex align-items-center p-2 rounded-3 mb-2 hover-bg-light">
                                <a href="/y/public/app/profile.php?username=<?php echo htmlspecialchars($recommended_user['user_name']); ?>" class="text-decoration-none text-reset me-3">
                                    <div class="rounded-circle overflow-hidden" style="width: 48px; height: 48px; background-color: #f8f9fa;">
                                        <?php if (!empty($recommended_user['profile_picture_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($recommended_user['profile_picture_url']); ?>" 
                                                alt="<?php echo htmlspecialchars($recommended_user['user_name']); ?>" 
                                                class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                <i class="bi bi-person-circle text-secondary" style="font-size: 1.8rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <div class="user-info flex-grow-1 overflow-hidden">
                                    <a href="/y/public/app/profile.php?username=<?php echo htmlspecialchars($recommended_user['user_name']); ?>" class="text-decoration-none text-reset">
                                        <div class="fw-bold text-truncate"><?php echo htmlspecialchars($recommended_user['display_name'] ?? $recommended_user['user_name']); ?></div>
                                        <div class="text-muted text-truncate">@<?php echo htmlspecialchars($recommended_user['user_name']); ?></div>
                                    </a>
                                </div>
                                <form method="post" action="/y/public/app/profile.php">
                                    <input type="hidden" name="action" value="follow">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($recommended_user['user_name']); ?>">
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill">Follow</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($recommended_usernames) > 1): ?>
                            <!-- Follow All Button -->
                            <div class="text-center mt-3">
                                <form method="post" action="/y/public/app/follow_all.php">
                                    <input type="hidden" name="usernames" value='<?php echo json_encode($recommended_usernames); ?>'>
                                    <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="bi bi-people-fill me-1"></i> Follow All
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <a href="/y/public/app/search.php?tab=people" class="text-decoration-none d-block mt-3 text-primary">
                            Show more
                        </a>
                    <?php else: ?>
                        <div class="text-muted p-2">No recommendations available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Trending hashtags section -->
        <div class="trending-hashtags-container mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h5 class="card-title fs-5 fw-bold">Trending hashtags</h5>
                </div>
                <div class="card-body pt-2">
                    <?php $trending_hashtags = get_top_hashtags($conn, 5); ?>
                    <?php if (!empty($trending_hashtags)): ?>
                        <?php foreach ($trending_hashtags as $hashtag): ?>
                            <a href="/y/public/app/hashtag.php?tag=<?php echo urlencode($hashtag['tag_name']); ?>" 
                               class="trend-item d-block p-2 rounded-3 hover-bg-light mb-2 text-decoration-none">
                                <div class="fw-bold text-dark">#<?php echo htmlspecialchars($hashtag['tag_name']); ?></div>
                                <div class="text-muted small"><?php echo number_format($hashtag['usage_count']); ?> posts</div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted p-2">No trending hashtags yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    
        
        <!-- Footer -->
        <footer class="mt-4 small text-muted">
            <div class="d-flex flex-wrap gap-2">
                <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
                <a href="#" class="text-muted text-decoration-none">Cookie Policy</a>
            </div>
            <div class="mt-2">
                © 2025 Y, Inc.
            </div>
        </footer>
    </div>
</div>

<style>
    /* Right sidebar specific styles */
    .rounded-pill-start {
        border-top-left-radius: 50rem !important;
        border-bottom-left-radius: 50rem !important;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    
    .rounded-pill-end {
        border-top-right-radius: 50rem !important;
        border-bottom-right-radius: 50rem !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    
    .hover-bg-light:hover {
        background-color: rgba(0, 0, 0, 0.03);
        cursor: pointer;
    }
    
    .card {
        transition: box-shadow 0.2s ease;
    }
    
    .card:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important;
    }
    
    .card-header {
        background: transparent;
    }
</style>