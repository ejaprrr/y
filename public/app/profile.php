<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
require_once "../../src/functions/connection.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/post.php";
require_once "../../src/functions/user.php";
require_once "../../src/functions/validation.php";
require_once "../../src/components/app/post.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/app/left-sidebar.php";
require_once "../../src/components/app/right-sidebar.php";
require_once "../../src/components/app/page-header.php";
require_once "../../src/components/app/empty-state.php";

// authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

// set CSRF token
set_csrf_token();

// get user information
$current_user = get_user($conn, $_SESSION["user_id"]);

// get username from URL or default to current user
$username = isset($_GET["username"]) ? sanitize_input($_GET["username"]) : $current_user["username"];
$profile_user = get_user_by_username($conn, $username);

if (!$profile_user) {
    // user not found
    header("Location: feed.php");
    exit();
}

// get user"s posts
$posts = get_user_posts($conn, $profile_user["id"]);

// get user"s liked posts
$liked_posts = get_user_liked_posts($conn, $profile_user["id"]);

// determine if this is the current user"s profile
$is_own_profile = ($profile_user["id"] == $_SESSION["user_id"]);

// fetch stats
$followers_count = get_follower_count($conn, $profile_user["id"]);
$following_count = get_following_count($conn, $profile_user["id"]);
$likes_received = get_total_likes_received($conn, $profile_user["id"]);
$is_following = is_following($conn, $current_user["id"], $profile_user["id"]);

// determine which tab is active (default to "posts")
$active_tab = isset($_GET["tab"]) ? sanitize_input($_GET["tab"]) : "posts";

?>

<?php render_header($profile_user["display_name"] ?? $profile_user["username"]); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/components/post.css">
<link rel="stylesheet" href="../assets/css/components/empty-state.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/page-header.css">

<div class="d-flex">
    <?php render_left_sidebar($current_user); ?>

    <div class="main-content">
        <?php 
        // create tabs
        $profile_tabs = [
            [
                "label" => "posts",
                "url" => "?username=" . htmlspecialchars($profile_user["username"]) . "&tab=posts",
                "active" => $active_tab === "posts"
            ],
            [
                "label" => "likes",
                "url" => "?username=" . htmlspecialchars($profile_user["username"]) . "&tab=likes",
                "active" => $active_tab === "likes"
            ]
        ];

        // render the header component
        render_page_header(
            $profile_user["display_name"] ?? $profile_user["username"],
            count($posts) . " posts",
            $_GET["origin"] ?? "feed.php",
            $profile_tabs
        );
        ?>

        <!-- scrollable content -->
        <div class="height-auto">
            <!-- profile header and posts -->
            <div class="profile-header">
                <!-- cover image -->
                <?php if ($profile_user["cover_image"]): ?>
                    <div class="cover-image">
                        <img src="<?= htmlspecialchars($profile_user["cover_image"]) ?>" alt="cover image" class="w-100 h-100 object-fit-cover">
                    </div>
                <?php else: ?>
                    <div class="cover-image default-cover"></div>
                <?php endif; ?>
                
                <!-- profile info -->
                <div class="px-3 mb-3 pt-5 position-relative">
                    <div class="d-flex">
                        <!-- profile picture -->
                        <div class="position-absolute profile-picture-container">
                            <?php if ($profile_user["profile_picture"]): ?>
                                <img src="<?= htmlspecialchars($profile_user["profile_picture"]) ?>" alt="profile picture" class="avatar">
                            <?php else: ?>
                                <div class="avatar default-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- profile header actions -->
                        <div class="position-absolute translate-middle-y profile-header-actions end-0">
                            <?php if ($is_own_profile): ?>
                                <a href="edit-profile.php?origin=<?= urlencode(get_clean_url()) ?>" class="btn btn-primary rounded-3 px-4 fw-semibold me-3">edit profile</a>
                            <?php else: ?>
                                <button 
                                    class="btn btn-primary rounded-3 fw-semibold follow-btn me-3" 
                                    data-following="<?= $is_following ? "1" : "0" ?>" 
                                    data-user-id="<?= $profile_user["id"] ?>">
                                    <?= $is_following ? "unfollow" : "follow" ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- profile name and handle -->
                    <div class="mt-3">
                        <h2 class="mb-1 fw-bold"><?= htmlspecialchars($profile_user["display_name"] ?? $profile_user["username"]) ?></h2>
                        <p class=" mb-2">@<?= htmlspecialchars($profile_user["username"]) ?></p>
                    </div>
                    
                    <!-- profile bio -->
                    <?php if (!empty($profile_user["bio"])): ?>
                        <div class="mb-3">
                            <p><?= nl2br(htmlspecialchars($profile_user["bio"])) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- profile stats -->
                    <div class="d-flex gap-4 mb-3 small">
                        <div>
                            <span class="fw-bold"><?= $following_count ?></span>
                            <span class="ms-1">following</span>
                        </div>
                        <div >
                            <span class="fw-bold"><?= $followers_count ?></span>
                            <span class="ms-1">followers</span>
                        </div>
                        <div>
                            <span class="fw-bold"><?= $likes_received ?></span>
                            <span class="ms-1">likes</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="posts mx-3">
                <!-- CSRF token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                <!-- render posts / empty state -->
                <?php if ($active_tab === "posts"): ?>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <?php render_post($post, $conn); ?> 
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php 
                        if ($is_own_profile) {
                            render_empty_state(
                                "file-earmark-text", 
                                "you haven't posted anything yet", 
                                "share your first post with the world",
                            );
                        } else {
                            render_empty_state(
                                "file-earmark-text", 
                                "no posts yet", 
                                "this user hasn't shared any posts"
                            );
                        }
                        ?>
                    <?php endif; ?>
                <?php elseif ($active_tab === "likes"): ?>
                    <?php if (!empty($liked_posts)): ?>
                        <?php foreach ($liked_posts as $post): ?>
                            <?php render_post($post, $conn); ?> 
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php 
                        if ($is_own_profile) {
                            render_empty_state(
                                "heart", 
                                "you haven't liked any posts yet", 
                                "like posts to support your favorite creators",
                            );
                        } else {
                            render_empty_state(
                                "heart", 
                                "no liked posts", 
                                "this user hasn't liked any posts yet"
                            );
                        }
                        ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- right sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<script src="../assets/js/interaction.js"></script>

<?php render_footer(); ?>