<?php

require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/user.php";
require_once "../../src/functions/hashtag.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/post.php";
require_once "../../src/components/empty-state.php";
require_once "../../src/components/app/left-sidebar.php";
require_once "../../src/components/app/right-sidebar.php";
require_once "../../src/components/app/page-header.php";

// Authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

// Set CSRF token
set_csrf_token();

// Get user information
$user = get_user($conn, $_SESSION['user_id']);

// Get users that current user follows (for the dropdown)
$followed_users = get_followed_users($conn, $user['id']);

// Initialize variables
$search_results = [];
$error = '';
$message = '';
$total_results = 0;

// Default search values
$default_values = [
    'keywords' => '',
    'exclude_keywords' => '',
    'case_sensitive' => false,
    'whole_word' => false,
    'from_date' => '',
    'to_date' => '',
    'sort_by' => 'recent',
    'limit' => 25,
    'followed_users' => []
];

// Handle search
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['keywords'])) {
    // Quick search from right sidebar
    $keywords = sanitize_input($_GET['keywords']);
    if (!empty($keywords)) {
        $search_results = perform_search($conn, [
            'keywords' => $keywords,
            'sort_by' => 'recent',
            'limit' => 25
        ], $user['id']);
        $total_results = count($search_results);
    }
    
    // Store the search term for form
    $default_values['keywords'] = $keywords;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check CSRF token
    $valid = check_csrf_token();
    if (!$valid) {
        $error = "invalid CSRF token";
    } else {
        // Get search parameters
        $search_params = [
            'keywords' => sanitize_input($_POST['keywords'] ?? ''),
            'exclude_keywords' => sanitize_input($_POST['exclude_keywords'] ?? ''),
            'case_sensitive' => isset($_POST['case_sensitive']),
            'whole_word' => isset($_POST['whole_word']),
            'from_date' => sanitize_input($_POST['from_date'] ?? ''),
            'to_date' => sanitize_input($_POST['to_date'] ?? ''),
            'sort_by' => sanitize_input($_POST['sort_by'] ?? 'recent'),
            'limit' => intval($_POST['limit'] ?? 25),
            'followed_users' => isset($_POST['followed_users']) ? $_POST['followed_users'] : []
        ];
        
        // Store the form values for repopulation
        $default_values = $search_params;
        
        // Perform search if keywords provided
        if (!empty($search_params['keywords']) || !empty($search_params['followed_users'])) {
            $search_results = perform_search($conn, $search_params, $user['id']);
            $total_results = count($search_results);
        } else {
            $error = "please enter at least keywords or select users";
        }
    }
}

/**
 * Perform a search based on the provided parameters
 */
function perform_search($conn, $params, $user_id) {
    // Build search query based on parameters
    $sql_parts = [];
    $sql_params = [];
    $sql_types = "";
    
    // Base query to get posts with user info and like counts
    $base_sql = "SELECT p.*, u.username, u.display_name, u.profile_picture,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = ?) AS is_liked
                FROM posts p
                JOIN users u ON p.user_id = u.id";
    
    // Add user_id parameter
    $sql_params[] = $user_id;
    $sql_types .= "i";
    
    // Where conditions array
    $where_conditions = [];
    
    // Keywords search (in post content, username, display name, or hashtags)
    if (!empty($params['keywords'])) {
        $keyword_search = $params['keywords'];
        
        // Handle case sensitivity
        $like_operator = isset($params['case_sensitive']) && $params['case_sensitive'] ? "LIKE BINARY" : "LIKE";
        
        // Handle whole word search
        if (isset($params['whole_word']) && $params['whole_word']) {
            // For whole word search, we'll split into words and search each one
            $keywords = explode(" ", $keyword_search);
            $keyword_conditions = [];
            
            foreach ($keywords as $word) {
                if (empty($word)) continue;
                
                // For whole word search
                $search_pattern = "[[:<:]]" . preg_quote($word) . "[[:>:]]";
                $keyword_conditions[] = "(p.content REGEXP ? OR u.username REGEXP ? OR u.display_name REGEXP ? OR 
                                        EXISTS(SELECT 1 FROM hashtags h WHERE h.post_id = p.id AND h.hashtag REGEXP ?))";
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_params[] = $search_pattern;
                $sql_types .= "ssss";
            }
            
            if (!empty($keyword_conditions)) {
                $where_conditions[] = "(" . implode(" OR ", $keyword_conditions) . ")";
            }
        } else {
            // For partial word search
            $search_pattern = "%" . $keyword_search . "%";
            $where_conditions[] = "(p.content $like_operator ? OR u.username $like_operator ? OR u.display_name $like_operator ? OR
                                 EXISTS(SELECT 1 FROM hashtags h WHERE h.post_id = p.id AND h.hashtag $like_operator ?))";
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_params[] = $search_pattern;
            $sql_types .= "ssss";
        }
    }
    
    // Exclude keywords
    if (!empty($params['exclude_keywords'])) {
        $exclude_search = $params['exclude_keywords'];
        $like_operator = isset($params['case_sensitive']) && $params['case_sensitive'] ? "LIKE BINARY" : "LIKE";
        
        // For exclude, we want posts that DON'T contain these terms
        $search_pattern = "%" . $exclude_search . "%";
        $where_conditions[] = "(p.content NOT $like_operator ? AND u.username NOT $like_operator ? AND u.display_name NOT $like_operator ? AND
                             NOT EXISTS(SELECT 1 FROM hashtags h WHERE h.post_id = p.id AND h.hashtag $like_operator ?))";
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_params[] = $search_pattern;
        $sql_types .= "ssss";
    }
    
    // Date range (from)
    if (!empty($params['from_date'])) {
        $where_conditions[] = "p.created_at >= ?";
        $sql_params[] = $params['from_date'] . " 00:00:00";
        $sql_types .= "s";
    }
    
    // Date range (to)
    if (!empty($params['to_date'])) {
        $where_conditions[] = "p.created_at <= ?";
        $sql_params[] = $params['to_date'] . " 23:59:59";
        $sql_types .= "s";
    }
    
    // Filter by followed users
    if (!empty($params['followed_users'])) {
        $placeholders = implode(',', array_fill(0, count($params['followed_users']), '?'));
        $where_conditions[] = "u.id IN ($placeholders)";
        foreach ($params['followed_users'] as $followed_id) {
            $sql_params[] = $followed_id;
            $sql_types .= "i";
        }
    }
    
    // Combine conditions
    $sql = $base_sql;
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Sorting
    switch ($params['sort_by']) {
        case 'popular':
            $sql .= " ORDER BY like_count DESC, p.created_at DESC";
            break;
        case 'recent':
        default:
            $sql .= " ORDER BY p.created_at DESC";
            break;
    }
    
    // Limit
    $sql .= " LIMIT ?";
    $sql_params[] = $params['limit'];
    $sql_types .= "i";
    
    // Execute query
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($sql_types, ...$sql_params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        
        $stmt->close();
        return $posts;
    }
    
    return [];
}

/**
 * Get users that the current user follows
 */
function get_followed_users($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.display_name, u.profile_picture
        FROM users u
        JOIN follows f ON u.id = f.followed_id
        WHERE f.follower_id = ?
        ORDER BY u.username
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    $stmt->close();
    return $users;
}

?>

<?php render_header("search"); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/components/post.css">
<link rel="stylesheet" href="../assets/css/components/hashtag.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/empty-state.css">
<link rel="stylesheet" href="../assets/css/components/page-header.css">
<link rel="stylesheet" href="../assets/css/components/search.css">

<div class="d-flex">
    <?php render_left_sidebar($user); ?>

    <div class="main-content">
        <?php render_page_header('search', 'find posts and users', '', []); ?>
        
        <!-- Search form -->
        <div class="search-form-container p-3">
            <div class="card rounded-3">
                <div class="card-body">
                    <form method="POST" class="search-form">
                        <div class="row g-3">
                            <!-- Keywords -->
                            <div class="col-md-6">
                                <label for="keywords" class="form-label fw-bold">keywords</label>
                                <input type="text" class="form-control rounded-3" id="keywords" name="keywords" 
                                       value="<?= htmlspecialchars($default_values['keywords']) ?>" 
                                       placeholder="search post content, hashtags, users...">
                                <div class="form-text text-muted">search for text, @usernames, #hashtags...</div>
                            </div>
                            
                            <!-- Exclude Keywords -->
                            <div class="col-md-6">
                                <label for="exclude_keywords" class="form-label fw-bold">excluding keywords</label>
                                <input type="text" class="form-control rounded-3" id="exclude_keywords" name="exclude_keywords" 
                                       value="<?= htmlspecialchars($default_values['exclude_keywords']) ?>"
                                       placeholder="exclude these terms...">
                                <div class="form-text text-muted">results won't contain these terms</div>
                            </div>
                            
                            <!-- Keyword Options -->
                            <div class="col-md-6">
                                <div class="fw-bold mb-2">keyword options</div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="case_sensitive" name="case_sensitive" 
                                           <?= $default_values['case_sensitive'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="case_sensitive">
                                        case sensitive
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="whole_word" name="whole_word"
                                           <?= $default_values['whole_word'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="whole_word">
                                        match whole word only
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Time Span -->
                            <div class="col-md-6">
                                <div class="fw-bold mb-2">time span</div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="from_date" class="form-label">from</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary text-light">
                                                <i class="bi bi-calendar3"></i>
                                            </span>
                                            <input type="date" class="form-control rounded-end-3" id="from_date" name="from_date"
                                                   value="<?= htmlspecialchars($default_values['from_date']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label for="to_date" class="form-label">to</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary text-light">
                                                <i class="bi bi-calendar3"></i>
                                            </span>
                                            <input type="date" class="form-control rounded-end-3" id="to_date" name="to_date"
                                                   value="<?= htmlspecialchars($default_values['to_date']) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sort and Limit -->
                            <div class="col-md-6">
                                <label for="sort_by" class="form-label fw-bold">sort by</label>
                                <select class="form-select rounded-3" id="sort_by" name="sort_by">
                                    <option value="recent" <?= $default_values['sort_by'] === 'recent' ? 'selected' : '' ?>>
                                        newest first
                                    </option>
                                    <option value="popular" <?= $default_values['sort_by'] === 'popular' ? 'selected' : '' ?>>
                                        most popular first
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="limit" class="form-label fw-bold">limit results</label>
                                <select class="form-select rounded-3" id="limit" name="limit">
                                    <option value="25" <?= $default_values['limit'] == 25 ? 'selected' : '' ?>>25 results</option>
                                    <option value="50" <?= $default_values['limit'] == 50 ? 'selected' : '' ?>>50 results</option>
                                    <option value="100" <?= $default_values['limit'] == 100 ? 'selected' : '' ?>>100 results</option>
                                </select>
                            </div>
                            
                            <!-- Users I Follow -->
                            <div class="col-12">
                                <label for="followed_users" class="form-label fw-bold">search from users I follow</label>
                                <select class="form-select rounded-3" id="followed_users" name="followed_users[]" multiple style="height: 160px;">
                                    <?php if (!empty($followed_users)): ?>
                                        <?php foreach ($followed_users as $follow_user): ?>
                                            <option value="<?= $follow_user['id'] ?>" 
                                                    <?= in_array($follow_user['id'], $default_values['followed_users']) ? 'selected' : '' ?>>
                                                @<?= htmlspecialchars($follow_user['username']) ?> 
                                                <?php if ($follow_user['display_name']): ?>
                                                    (<?= htmlspecialchars($follow_user['display_name']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>you're not following anyone yet</option>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text text-muted">hold ctrl/cmd to select multiple users</div>
                            </div>
                            
                            <!-- CSRF token -->
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            
                            <!-- Submit button -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary rounded-3 px-4">search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Search results -->
        <div class="mx-3 mb-3">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($search_results)): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">found <?= number_format($total_results) ?> result<?= $total_results != 1 ? 's' : '' ?></h5>
                    <?php if (!empty($default_values['keywords'])): ?>
                        <span class="badge bg-primary rounded-pill">
                            "<?= htmlspecialchars($default_values['keywords']) ?>"
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="posts">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <?php foreach ($search_results as $post): ?>
                        <?php render_post($post, $conn); ?> 
                    <?php endforeach; ?>
                </div>
            <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST" || isset($_GET['keywords'])): ?>
                <?php render_empty_state('search', 'no results found', 'try different search terms or filters'); ?>
            <?php else: ?>
                <?php render_empty_state('search', 'start searching', 'use the form above to find posts'); ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<!-- Include interaction.js for like functionality -->
<script src="../assets/js/interaction.js"></script>

<?php render_footer(); ?>