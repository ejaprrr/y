<?php
require_once "../../src/functions/connection.php";
require_once "../../src/functions/helpers.php";
require_once "../../src/functions/auth.php";
require_once "../../src/functions/user.php";
require_once "../../src/functions/upload.php";
require_once "../../src/functions/validation.php";
require_once "../../src/components/layout.php";
require_once "../../src/components/app/left-sidebar.php";
require_once "../../src/components/app/right-sidebar.php";
require_once "../../src/components/app/page-header.php";

// authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

// upload base directory
$upload_base = realpath(__DIR__ . "/../uploads");

// set CSRF token
set_csrf_token();

// get user information
$user = get_user($conn, $_SESSION["user_id"]);

// initialize variables
$message = "";
$error = "";

// handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // check CSRF token
    $valid = check_csrf_token();
    if (!$valid) {
        $error = "invalid CSRF token";
    } else {
        // sanitize and validate inputs
        $display_name = sanitize_input($_POST["display_name"] ?? "");
        $bio = sanitize_input($_POST["bio"] ?? "");
        
        // basic validation
        if (strlen($display_name) > 48) {
            $error = "display name is too long (maximum 48 characters)";
        } else {
            // Validate bio
            $bio_validation = validate_bio($bio);
            if ($bio_validation !== true) {
                $error = $bio_validation;
            } else {
                // update profile info
                $success = update_user_profile($conn, $_SESSION["user_id"], $display_name, $bio);
                
                // handle profile picture upload
                if (!empty($_FILES["profile_picture"]["name"])) {
                    // validate the image
                    $valid = validate_image($_FILES["profile_picture"]);
                    if ($valid) {
                        // create simpler file names
                        $filename = "profile_" . $_SESSION["user_id"] . "_" . time() . "." . 
                                    pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
                        
                        // create target path
                        $target_path = $upload_base . "/profile/" . $filename;
                        
                        // move the uploaded file
                        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_path)) {
                            $profile_path = "/y/public/uploads/profile/" . $filename;
                            update_profile_picture($conn, $_SESSION["user_id"], $profile_path);
                        } else {
                            $error = "failed to upload profile picture";
                        }
                    } else {
                        $error = $valid;
                    }
                }
                
                // handle cover image upload
                if (!empty($_FILES["cover_image"]["name"])) {
                    $valid = validate_image($_FILES["cover_image"]);
                    
                    if ($valid) {
                        // create simpler filenames
                        $filename = "cover_" . $_SESSION["user_id"] . "_" . time() . "." . 
                                   pathinfo($_FILES["cover_image"]["name"], PATHINFO_EXTENSION);
                        
                        // create target path
                        $target_path = $upload_base . "/cover/" . $filename;
                        
                        // move the uploaded file
                        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_path)) {
                            $cover_path = "/y/public/uploads/cover/" . $filename;
                            update_cover_image($conn, $_SESSION["user_id"], $cover_path);
                        } else {
                            $error = "failed to upload cover image";
                        }
                    } else {
                        $error = $valid;
                    }
                }
                
                if (empty($error)) {
                    $message = "profile updated successfully";
                    
                    // refresh user data
                    $user = get_user($conn, $_SESSION["user_id"]);
                }
            }
        }
    }
}

?>

<?php render_header("edit profile"); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/pages/edit-profile.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">

<div class="d-flex">
    <?php render_left_sidebar($user); ?>

    <div class="main-content">
        <?php
        // render the profile header component with no tabs
        render_page_header(
            "edit profile",
            "customize your appearance!",
            $_GET["origin"] ?? "profile.php",
            [], 
            false
        );
        ?>

        <!-- messages and errors -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success m-3"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger m-3"><?= $error ?></div>
        <?php endif; ?>

        <!-- edit profile form -->
        <div class="edit-profile-container p-0">
            <form method="POST" enctype="multipart/form-data">
                <!-- profile preview section -->
                <div class="profile-header-preview">
                    <!-- cover image -->
                    <div class="cover-container position-relative">
                        <div class="cover-image-preview" id="cover-preview">
                            <?php if ($user["cover_image"]): ?>
                                <img src="<?= htmlspecialchars($user["cover_image"]) ?>" alt="Cover" class="cover-img">
                            <?php else: ?>
                                <div class="cover-placeholder"></div>
                            <?php endif; ?>
                        </div>
                        <div class="cover-overlay d-flex align-items-center justify-content-center">
                            <label for="cover_image" class="btn btn-dark rounded-pill px-3">
                                <i class="bi bi-camera-fill me-2"></i>change cover
                            </label>
                        </div>
                    </div>
                    
                    <!-- profile picture -->
                    <div class="profile-picture-edit">
                        <div class="profile-picture-container-edit">
                            <?php if ($user["profile_picture"]): ?>
                                <img src="<?= htmlspecialchars($user["profile_picture"]) ?>" id="profile-pic-preview" alt="Profile" class="profile-picture-edit-img">
                            <?php else: ?>
                                <div id="profile-pic-preview" class="profile-picture-edit-default">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            <?php endif; ?>
                            <div class="profile-picture-overlay d-flex align-items-center justify-content-center">
                                <label for="profile_picture" class="btn btn-dark btn-sm rounded-circle">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- hidden file inputs -->
                <input type="file" id="profile_picture" name="profile_picture" class="d-none" accept="image/*">
                <input type="file" id="cover_image" name="cover_image" class="d-none" accept="image/*">
                
                <!-- form fields -->
                <div class="profile-form-fields px-3 pt-5 pb-3">
                    <div class="mb-3">
                        <label for="display_name" class="form-label fw-bold">display name</label>
                        <input type="text" class="form-control rounded-3" id="display_name" name="display_name" 
                               value="<?= htmlspecialchars($user["display_name"] ?? $user["username"]) ?>" 
                               maxlength="48" required>
                        <div class="form-text">your name as displayed on your profile (48 characters max)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label fw-bold">bio</label>
                        <textarea class="form-control rounded-3" id="bio" name="bio" rows="3"><?= htmlspecialchars($user["bio"] ?? "") ?></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="form-text">tell the world about usourself</span>
                            <span id="bio-counter"><?= strlen($user["bio"] ?? "") ?>/128</span>
                        </div>
                    </div>
                    
                    <!-- CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                    
                    <div class="d-flex justify-content-between mt-4 pt-2 border-top">
                        <a href="profile.php" class="btn btn-outline-light rounded-3 px-4">cancel</a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<script src="../assets/js/pages/edit-profile.js"></script>

<?php render_footer(); ?>