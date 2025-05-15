<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
require_once "../../src/functions/connection.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/validation.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/user.php";
require_once "../../src/functions/hashtag.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/app/post.php";
require_once "../../src/components/app/empty-state.php";
require_once "../../src/components/app/left-sidebar.php";
require_once "../../src/components/app/right-sidebar.php";
require_once "../../src/components/app/page-header.php";
require_once "../../src/components/app/pagination.php";

// authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

// set CSRF token
regenerate_csrf_token();

// get user information
$user = get_user($conn, $_SESSION["user_id"]);

// get users that current user follows (for the dropdown)
$followed_users = get_followed_users($conn, $user["id"]);

// initialize variables
$search_results = [];
$error = "";
$message = "";
$total_results = 0;

// default search values
$default_values = [
    "keyword" => "",
    "exclude_keyword" => "",
    "case_sensitive" => false,
    "whole_word" => false,
    "from_date" => "",
    "to_date" => "",
    "sort_by" => "recent",
    "limit" => 25,
    "followed_users" => []
];

// Pagination settings
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Load parameters from GET for pagination navigation
// This ensures all search parameters are properly maintained when navigating between pages
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Check if this is a paginated search (not the first visit to search page)
    if (isset($_GET['keyword']) || isset($_GET['followed_users'])) {
        // Transfer all GET parameters to default_values
        $default_values["keyword"] = sanitize_input($_GET["keyword"] ?? "");
        $default_values["exclude_keyword"] = sanitize_input($_GET["exclude_keyword"] ?? "");
        $default_values["case_sensitive"] = isset($_GET["case_sensitive"]) && $_GET["case_sensitive"] == '1';
        $default_values["whole_word"] = isset($_GET["whole_word"]) && $_GET["whole_word"] == '1';
        $default_values["from_date"] = sanitize_input($_GET["from_date"] ?? "");
        $default_values["to_date"] = sanitize_input($_GET["to_date"] ?? "");
        $default_values["sort_by"] = sanitize_input($_GET["sort_by"] ?? "recent");
        $default_values["limit"] = isset($_GET["limit"]) ? intval($_GET["limit"]) : 25;
        
        // Handle array parameter for followed users
        if (isset($_GET["followed_users"])) {
            $default_values["followed_users"] = is_array($_GET["followed_users"]) 
                ? $_GET["followed_users"] 
                : [$_GET["followed_users"]];
        }
        
        // Get the posts per page from the limit parameter
        $posts_per_page = $default_values["limit"];
        
        // Build search parameters from GET values
        $search_params = $default_values;
        $search_params["page"] = $current_page;
        $search_params["per_page"] = $posts_per_page;
        
        // Execute search with all parameters
        $search_results = perform_search($conn, $search_params, $user["id"]);
        $total_results = get_search_results_count($conn, $search_params, $user["id"]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // check CSRF token
    $valid = check_csrf_token();
    if (!$valid) {
        $error = "invalid CSRF token";
    } else {
        // get search parameters
        $search_params = [
            "keyword" => sanitize_input($_POST["keyword"] ?? ""),
            "exclude_keyword" => sanitize_input($_POST["exclude_keyword"] ?? ""),
            "case_sensitive" => isset($_POST["case_sensitive"]),
            "whole_word" => isset($_POST["whole_word"]),
            "from_date" => sanitize_input($_POST["from_date"] ?? ""),
            "to_date" => sanitize_input($_POST["to_date"] ?? ""),
            "sort_by" => sanitize_input($_POST["sort_by"] ?? "recent"),
            "limit" => intval($_POST["limit"] ?? 25),
            "followed_users" => isset($_POST["followed_users"]) ? $_POST["followed_users"] : []
        ];
        
        // Reset page to 1 when submitting new search
        $current_page = 1;
        
        // store the form values for repopulation
        $default_values = $search_params;
        
        // perform search if keyword provided
        if (!empty($search_params["keyword"]) || !empty($search_params["followed_users"])) {
            // Get per_page from the form's limit
            $posts_per_page = $search_params["limit"];
            
            // Add pagination parameters to search params
            $search_params["page"] = $current_page;
            $search_params["per_page"] = $posts_per_page;

            $search_results = perform_search($conn, $search_params, $user["id"]);
            $total_results = get_search_results_count($conn, $search_params, $user["id"]);
        } else {
            $error = "please enter at least keyword or select users";
        }
    }
} else {
    // For regular page loads without search
    $posts_per_page = isset($_GET["limit"]) ? intval($_GET["limit"]) : 25;
}

?>

<?php
 render_header("search"); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/components/post.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/empty-state.css">
<link rel="stylesheet" href="../assets/css/components/page-header.css">
<link rel="stylesheet" href="../assets/css/pages/search.css">

<div class="d-flex">
    <?php
 render_left_sidebar($user); ?>

    <div class="main-content">
        <?php
 render_page_header("search", "find posts and users", $_GET["origin"] ?? "feed.php", []); ?>
        
        <!-- search form -->
        <div class="m-3 p-3 card rounded-3">
            <form method="POST" class="search-form">
                <!-- Add hidden inputs to maintain state for followed_users array when using GET pagination -->
                <?php
 if ($_SERVER["REQUEST_METHOD"] === "GET" && !empty($default_values["followed_users"])): ?>
                    <?php
 foreach ($default_values["followed_users"] as $followed_id): ?>
                        <input type="hidden" name="followed_users[]" value="<?= htmlspecialchars($followed_id) ?>">
                    <?php
 endforeach; ?>
                <?php
 endif; ?>
                <div class="row g-3">
                    <!-- keyword -->
                    <div class="col-6">
                        <label for="keyword" class="fw-bold">keyword</label>
                        <input type="text" class="form-control rounded-3" id="keyword" name="keyword" 
                                value="<?= htmlspecialchars($default_values["keyword"]) ?>" 
                                placeholder="start typing..." required>
                        <div class="form-text">search for text, usernames, hashtags...</div>
                    </div>
                    
                    <!-- exclude keyword -->
                    <div class="col-6">
                        <label for="exclude_keyword" class="fw-bold">excluding keyword</label>
                        <input type="text" class="form-control rounded-3" id="exclude_keyword" name="exclude_keyword" 
                                value="<?= htmlspecialchars($default_values["exclude_keyword"]) ?>"
                                placeholder="start typing...">
                        <div class="form-text">results won"t contain this keyword</div>
                    </div>
                    
                    <!-- keyword options -->
                    <div class="col-6">
                        <div class="fw-bold mb-2">keyword options</div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="case_sensitive" name="case_sensitive" 
                                    <?= $default_values["case_sensitive"] ? "checked" : "" ?>>
                            <label for="case_sensitive">
                                case sensitive
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="whole_word" name="whole_word"
                                    <?= $default_values["whole_word"] ? "checked" : "" ?>>
                            <label for="whole_word">
                                match whole word only
                            </label>
                        </div>
                    </div>
                    
                    <!-- time span -->
                    <div class="col-6">
                        <div class="fw-bold mb-2">time span</div>
                        <div class="d-flex gap-3">
                            <div class="w-100">
                                <label for="from_date">from</label>
                                <div class="input-group">
                                    <span class="date-icon input-group-text border-secondary text-light">
                                        <i class="bi bi-calendar3"></i>
                                    </span>
                                    <input type="date" class="form-control rounded-end-3" id="from_date" name="from_date"
                                            value="<?= htmlspecialchars($default_values["from_date"]) ?>">
                                </div>
                            </div>
                            <div class="w-100">
                                <label for="to_date">to</label>
                                <div class="input-group">
                                    <span class="date-icon input-group-text border-secondary text-light">
                                        <i class="bi bi-calendar3"></i>
                                    </span>
                                    <input type="date" class="form-control rounded-end-3" id="to_date" name="to_date"
                                            value="<?= htmlspecialchars($default_values["to_date"]) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- sort and limit -->
                    <div class="col-6">
                        <label for="sort_by" class="fw-bold">sort by</label>
                        <select class="form-control rounded-3" id="sort_by" name="sort_by">
                            <option value="recent" <?= $default_values["sort_by"] === "recent" ? "selected" : "" ?>>
                                newest first
                            </option>
                            <option value="popular" <?= $default_values["sort_by"] === "popular" ? "selected" : "" ?>>
                                most popular first
                            </option>
                        </select>
                    </div>
                    
                    <div class="col-6">
                        <label for="limit" class="fw-bold">limit results</label>
                        <select class="form-control rounded-3" id="limit" name="limit">
                            <option value="10" <?= $default_values["limit"] == 10 ? "selected" : "" ?>>10 results</option>
                            <option value="25" <?= $default_values["limit"] == 25 ? "selected" : "" ?>>25 results</option>
                            <option value="50" <?= $default_values["limit"] == 50 ? "selected" : "" ?>>50 results</option>
                            <option value="100" <?= $default_values["limit"] == 100 ? "selected" : "" ?>>100 results</option>
                        </select>
                        <div class="form-text">number of results per page</div>
                    </div>
                    
                    <!-- users i follow -->
                    <div class="col-12">
                        <label for="followed_users" class="fw-bold">users i follow</label>
                        <select class="form-control rounded-3" id="followed_users" name="followed_users[]" multiple>
                            <?php
 if (!empty($followed_users)): ?>
                                <?php
 foreach ($followed_users as $follow_user): ?>
                                    <option value="<?= $follow_user["id"] ?>" 
                                            <?= in_array($follow_user["id"], $default_values["followed_users"]) ? "selected" : "" ?>>
                                        @<?= htmlspecialchars($follow_user["username"]) ?> 
                                        <?php
 if ($follow_user["display_name"]): ?>
                                            (<?= htmlspecialchars($follow_user["display_name"]) ?>)
                                        <?php
 endif; ?>
                                    </option>
                                <?php
 endforeach; ?>
                            <?php
 else: ?>
                                <option disabled>you're not following anyone yet</option>
                            <?php
 endif; ?>
                        </select>
                        <div class="form-text">hold ctrl/cmd to select multiple users</div>
                    </div>
                    
                    <!-- CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                    
                    <!-- submit button -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary rounded-3 px-4">search</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- search results -->
        <div class="mx-3 mb-3">
            <?php
 if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php
 endif; ?>
            
            <?php
 if (!empty($message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php
 endif; ?>
            
            <?php
 if (!empty($search_results)): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">
                        found <?= number_format($total_results) ?> results 
                        <?php
 if ($total_results > count($search_results)): ?>
                            (showing page <?= $current_page ?> of <?= ceil($total_results / $posts_per_page) ?>)
                        <?php
 endif; ?>
                    </h5>
                </div>
                
                <div class="posts">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                    <?php
 foreach ($search_results as $post): ?>
                        <?php
 render_post($post, $conn); ?> 
                    <?php
 endforeach; ?>
                </div>
                
                <!-- Add pagination after search results -->
                <?php
 
                    // Build the base URL for pagination, maintaining all search parameters
                    if ($_SERVER["REQUEST_METHOD"] === "POST") {
                        // For POST requests, create a GET URL with all parameters
                        $query_params = [
                            'keyword' => urlencode($default_values["keyword"]),
                            'exclude_keyword' => urlencode($default_values["exclude_keyword"]),
                            'case_sensitive' => $default_values["case_sensitive"] ? '1' : '0',
                            'whole_word' => $default_values["whole_word"] ? '1' : '0',
                            'from_date' => urlencode($default_values["from_date"]),
                            'to_date' => urlencode($default_values["to_date"]),
                            'sort_by' => urlencode($default_values["sort_by"]),
                            'limit' => $default_values["limit"]
                        ];
                        
                        // Handle followed_users array properly
                        if (!empty($default_values["followed_users"])) {
                            foreach ($default_values["followed_users"] as $followed_id) {
                                $query_params['followed_users'][] = $followed_id;
                            }
                        }
                    } else {
                        // For GET requests, start with all current parameters
                        $query_params = $_GET;
                        
                        // Ensure proper encoding of values
                        if (isset($query_params['keyword'])) {
                            $query_params['keyword'] = urlencode($query_params['keyword']);
                        }
                        if (isset($query_params['exclude_keyword'])) {
                            $query_params['exclude_keyword'] = urlencode($query_params['exclude_keyword']);
                        }
                    }
                    
                    // Remove any existing page parameter
                    unset($query_params['page']);
                    
                    // Build the base URL
                    $base_url = 'search.php?' . http_build_query($query_params);
                    
                    // Use the current posts_per_page for pagination
                    render_pagination($total_results, $posts_per_page, $current_page, $base_url);
                ?>
            <?php
 elseif ($_SERVER["REQUEST_METHOD"] === "POST" || isset($_GET["keyword"])): ?>
                <?php
 render_empty_state("search", "no results found", "try different search terms or filters"); ?>
            <?php
 else: ?>
                <?php
 render_empty_state("search", "start searching", "use the form above to find posts"); ?>
            <?php
 endif; ?>
        </div>
    </div>

    <!-- right sidebar -->
    <?php
 render_right_sidebar($conn); ?> 
</div>

<!-- include interaction.js for like functionality -->
<script src="../assets/js/interaction.js"></script>
<script src="../assets/js/pages/search.js"></script>

<?php
 render_footer(); ?>