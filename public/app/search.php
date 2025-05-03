<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Get search query
$query = $_GET['q'] ?? '';
$query = trim($query);

// Get active tab
$active_tab = $_GET['tab'] ?? 'top';
if (!in_array($active_tab, ['top', 'latest', 'people', 'hashtags'])) {
    $active_tab = 'top';
}

// Only search if query has 2 or more characters
$search_results = [];
$has_results = false;
$min_query_length = 2;

if (strlen($query) >= $min_query_length) {
    // Process search based on active tab
    if ($active_tab === 'people') {
        $search_results = search_users($conn, $query, $user['user_name'], 20);
    } elseif ($active_tab === 'hashtags') {
        $search_results = search_trending_hashtags($conn, $query, 20);
    } else {
        $search_results = search_posts($conn, $query, $user['user_name'], $active_tab, 20);
    }
    
    $has_results = !empty($search_results);
}

// Handle like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like' && isset($_POST['tweet_id'])) {
    $post_id = (int)$_POST['tweet_id'];
    
    if (has_liked($conn, $user['user_name'], $post_id)) {
        unlike_post($conn, $user['user_name'], $post_id);
    } else {
        like_post($conn, $user['user_name'], $post_id);
    }
    
    // Redirect to prevent form resubmission
    redirect("search.php?q=".urlencode($query)."&tab=$active_tab");
}

// Handle repost/unrepost action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repost' && isset($_POST['tweet_id'])) {
    $repost_post_id = (int)$_POST['tweet_id'];
    
    if (has_reposted($conn, $user['user_name'], $repost_post_id)) {
        unrepost_post($conn, $user['user_name'], $repost_post_id);
    } else {
        repost_post($conn, $user['user_name'], $repost_post_id);
    }
    
    // Redirect to prevent form resubmission
    redirect("search.php?q=".urlencode($query)."&tab=$active_tab");
}

// Handle bookmark action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bookmark' && isset($_POST['tweet_id'])) {
    $post_id = (int)$_POST['tweet_id'];
    
    if (has_bookmarked($conn, $user['user_name'], $post_id)) {
        unbookmark_post($conn, $user['user_name'], $post_id);
    } else {
        bookmark_post($conn, $user['user_name'], $post_id);
    }
    
    // Redirect to prevent form resubmission
    redirect("search.php?q=".urlencode($query)."&tab=$active_tab");
}

// Handle follow/unfollow action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'follow' && isset($_POST['username'])) {
    $target_username = $_POST['username'];
    
    if (is_following($conn, $user['user_name'], $target_username)) {
        unfollow_user($conn, $user['user_name'], $target_username);
    } else {
        follow_user($conn, $user['user_name'], $target_username);
    }
    
    // Redirect to prevent form resubmission
    redirect("search.php?q=".urlencode($query)."&tab=$active_tab");
}

// Set up page variables
$page_title = 'Y | Search';
$page_header = 'Search';

// Highlight search terms in text
function highlight_terms($text, $search_terms) {
    $terms = preg_split('/\s+/', $search_terms);
    $pattern = [];
    
    foreach ($terms as $term) {
        if (strlen($term) >= 2) {
            $pattern[] = '/' . preg_quote($term, '/') . '/i';
        }
    }
    
    if (!empty($pattern)) {
        return preg_replace($pattern, '<mark class="bg-warning bg-opacity-50">$0</mark>', $text);
    }
    
    return $text;
}

// Search tabs configuration
$tabs = [
    [
        'id' => 'top',
        'label' => 'Top',
        'url' => "?q=" . urlencode($query) . "&tab=top",
        'icon' => 'star'
    ],
    [
        'id' => 'latest',
        'label' => 'Latest',
        'url' => "?q=" . urlencode($query) . "&tab=latest",
        'icon' => 'clock'
    ],
    [
        'id' => 'people',
        'label' => 'People',
        'url' => "?q=" . urlencode($query) . "&tab=people",
        'icon' => 'people'
    ],
    [
        'id' => 'hashtags',
        'label' => 'Hashtags',
        'url' => "?q=" . urlencode($query) . "&tab=hashtags",
        'icon' => 'hash'
    ]
];

// Capture content in a buffer
ob_start();
?>

<div class="search-container p-3">
    <!-- Search form (large, centered at the top) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="/y/public/app/search.php" method="GET" class="d-flex">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-pill-start">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="search" name="q" class="form-control form-control-lg border-start-0 bg-light rounded-pill-end" 
                        placeholder="Search Y" aria-label="Search"
                        value="<?php echo htmlspecialchars($query); ?>" autofocus>
                    <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                </div>
            </form>
        </div>
    </div>
    
    <?php if (strlen($query) < $min_query_length): ?>
        <div class="text-center text-muted p-5">
            <div class="mb-3">
                <i class="bi bi-search" style="font-size: 3rem;"></i>
            </div>
            <p>Enter at least <?php echo $min_query_length; ?> characters to search</p>
        </div>
    <?php else: ?>
        <!-- Search result tabs - Keep this inclusion -->
        <?php include __DIR__ . '/../../resources/components/tabs_navigation.php'; ?>

        <!-- Search results -->
        <?php if (!$has_results): ?>
            <?php 
                $icon = 'search';
                $title = 'No results found';
                $message = 'We couldn\'t find any results for "' . htmlspecialchars($query) . '".';
                $secondary_message = 'Try different keywords or check for typos';
                include __DIR__ . '/../../resources/components/empty_state.php';
            ?>
        <?php else: ?>
            <?php if ($active_tab === 'hashtags'): ?>
                <!-- Hashtag search results -->
                <div class="hashtags-results">
                    <?php foreach ($search_results as $hashtag): ?>
                        <div class="card border-0 shadow-sm rounded-4 mb-3 hover-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                        <i class="bi bi-hash fs-3 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold fs-5">
                                            <?php echo highlight_terms(htmlspecialchars($hashtag['tag_name']), $query); ?>
                                        </div>
                                        <div class="text-muted">
                                            <?php echo number_format($hashtag['usage_count']); ?> posts
                                        </div>
                                    </div>
                                </div>
                                <a href="/y/public/app/hashtag.php?tag=<?php echo urlencode($hashtag['tag_name']); ?>" class="stretched-link"></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($active_tab === 'people'): ?>
                <!-- User search results -->
                <div class="people-results">
                    <?php foreach ($search_results as $user_result): ?>
                        <div class="card border-0 shadow-sm rounded-4 mb-3 hover-card">
                            <div class="card-body p-3">
                                <div class="d-flex">
                                    <!-- User avatar -->
                                    <div class="me-3">
                                        <div class="rounded-circle overflow-hidden" style="width: 60px; height: 60px; background-color: #f8f9fa;">
                                            <?php if (!empty($user_result['profile_picture_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($user_result['profile_picture_url']); ?>" 
                                                    alt="<?php echo htmlspecialchars($user_result['user_name']); ?>" 
                                                    class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="d-flex justify-content-center align-items-center h-100">
                                                    <i class="bi bi-person-circle text-secondary" style="font-size: 2rem;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- User info -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">
                                                    <?php echo highlight_terms(htmlspecialchars($user_result['display_name'] ?? $user_result['user_name']), $query); ?>
                                                </div>
                                                <div class="text-muted">
                                                    @<?php echo highlight_terms(htmlspecialchars($user_result['user_name']), $query); ?>
                                                </div>
                                            </div>
                                            
                                            <?php if ($user_result['user_name'] !== $user['user_name']): ?>
                                                <form method="post">
                                                    <input type="hidden" name="action" value="follow">
                                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($user_result['user_name']); ?>">
                                                    <button type="submit" class="btn <?php echo $user_result['is_following'] ? 'btn-outline-dark' : 'btn-primary'; ?> rounded-pill follow-button <?php echo $user_result['is_following'] ? 'following' : ''; ?>" style="min-width: 110px;">
                                                        <?php echo $user_result['is_following'] ? '' : 'Follow'; ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($user_result['profile_bio_content'])): ?>
                                            <p class="mt-2 mb-2">
                                                <?php echo highlight_terms(htmlspecialchars($user_result['profile_bio_content']), $query); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="mt-2 d-flex gap-3 text-muted small">
                                            <div><span class="fw-bold text-dark"><?php echo $user_result['post_count']; ?></span> Posts</div>
                                            <div><span class="fw-bold text-dark"><?php echo $user_result['follower_count']; ?></span> Followers</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <a href="/y/public/app/profile.php?username=<?php echo urlencode($user_result['user_name']); ?>" class="stretched-link"></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Post search results -->
                <div class="posts-results">
                    <?php foreach ($search_results as $post): ?>
                        <?php include __DIR__ . '/../../resources/components/post_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .hover-post:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important;
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
    .hover-card:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important;
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
    .tweet {
        border-bottom: none !important;
    }
    mark {
        padding: 0;
        border-radius: 2px;
    }
    .stretched-link::after {
        z-index: 1;
    }
    .follow-button.following:hover::after {
        content: 'Unfollow';
    }
    .follow-button.following::after {
        content: 'Following';
    }
    .follow-button.following:hover {
        background-color: #f8d7da;
        color: #dc3545;
        border-color: #dc3545;
    }
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
    }
</style>

<?php
$content = ob_get_clean();
// Render with layout
include __DIR__ . '/../../resources/components/layout.php';
?>