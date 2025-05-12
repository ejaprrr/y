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
require_once "../../src/components/app/profile-header.php";

// Authentication check
if (!check_login()) {
    redirect("../auth/log-in.php");
}

// Create upload directories if they don't exist
$upload_base = realpath(__DIR__ . "/../uploads");
if (!file_exists($upload_base)) {
    mkdir($upload_base, 0777, true);
}
if (!file_exists($upload_base . "/profile")) {
    mkdir($upload_base . "/profile", 0777, true);
}
if (!file_exists($upload_base . "/cover")) {
    mkdir($upload_base . "/cover", 0777, true);
}

set_csrf_token();
$user = get_user($conn, $_SESSION['user_id']);

$message = '';
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $valid = check_csrf_token();
    if (!$valid) {
        $error = "Invalid CSRF token.";
    } else {
        $display_name = sanitize_input($_POST['display_name'] ?? '');
        $bio = sanitize_input($_POST['bio'] ?? '');
        
        // Basic validation
        if (strlen($display_name) > 50) {
            $error = "Display name is too long (maximum 50 characters).";
        } elseif (strlen($bio) > 160) {
            $error = "Bio is too long (maximum 160 characters).";
        } else {
            // Update profile info
            $success = update_user_profile($conn, $_SESSION['user_id'], $display_name, $bio);
            
            // Handle profile picture upload
            if (!empty($_FILES['profile_picture']['name'])) {
                $validation = validate_image($_FILES['profile_picture']);
                if ($validation === true) {
                    // Create simpler filenames
                    $filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . 
                                pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    
                    $target_path = $upload_base . "/profile/" . $filename;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                        $profile_path = "/y/public/uploads/profile/" . $filename;
                        update_profile_picture($conn, $_SESSION['user_id'], $profile_path);
                    } else {
                        $error = "Failed to upload profile picture. Check file permissions.";
                    }
                } else {
                    $error = $validation;
                }
            }
            
            // Handle cover image upload
            if (!empty($_FILES['cover_image']['name'])) {
                $validation = validate_image($_FILES['cover_image']);
                if ($validation === true) {
                    // Create simpler filenames
                    $filename = 'cover_' . $_SESSION['user_id'] . '_' . time() . '.' . 
                               pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                    
                    $target_path = $upload_base . "/cover/" . $filename;
                    
                    if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_path)) {
                        $cover_path = "/y/public/uploads/cover/" . $filename;
                        update_cover_image($conn, $_SESSION['user_id'], $cover_path);
                    } else {
                        $error = "Failed to upload cover image. Check file permissions.";
                    }
                } else {
                    $error = $validation;
                }
            }
            
            if (empty($error)) {
                $message = "Profile updated successfully.";
                // Refresh user data
                $user = get_user($conn, $_SESSION['user_id']);
            }
        }
    }
}

?>

<?php render_header("Edit Profile"); ?>

<link rel="stylesheet" href="../assets/css/pages/app.css">
<link rel="stylesheet" href="../assets/css/pages/profile.css">
<link rel="stylesheet" href="../assets/css/components/left-sidebar.css">
<link rel="stylesheet" href="../assets/css/components/right-sidebar.css">

<div class="app-container d-flex vh-100">
    <?php render_left_sidebar($user); ?>

    <div class="main-content">
        <?php
        // Render the profile header component with no tabs
        render_profile_header(
            'Edit profile',
            '', // No subtitle
            'profile.php', // Back link goes to profile page
            [], // No tabs
            false // Not sticky
        );
        ?>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success m-3"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger m-3"><?= $error ?></div>
        <?php endif; ?>

        <div class="edit-profile-container p-0">
            <form method="POST" enctype="multipart/form-data">
                <!-- Profile Preview Section -->
                <div class="profile-header-preview">
                    <!-- Cover Image -->
                    <div class="cover-container position-relative">
                        <div class="cover-image-preview" id="cover-preview" 
                            <?php if ($user['cover_image']): ?>
                                style="background-image: url('<?= htmlspecialchars($user['cover_image']) ?>')"
                            <?php endif; ?>>
                        </div>
                        <div class="cover-overlay d-flex align-items-center justify-content-center">
                            <label for="cover_image" class="btn btn-dark rounded-pill px-3">
                                <i class="bi bi-camera-fill me-2"></i>Change cover
                            </label>
                        </div>
                    </div>
                    
                    <!-- Profile Picture -->
                    <div class="profile-picture-edit">
                        <div class="profile-picture-container-edit">
                            <?php if ($user['profile_picture']): ?>
                                <img src="<?= htmlspecialchars($user['profile_picture']) ?>" id="profile-pic-preview" alt="Profile" class="profile-picture-edit-img">
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

                <!-- Hidden file inputs -->
                <input type="file" id="profile_picture" name="profile_picture" class="d-none" accept="image/*">
                <input type="file" id="cover_image" name="cover_image" class="d-none" accept="image/*">
                
                <!-- Form Fields -->
                <div class="profile-form-fields px-3 pt-5 pb-3">
                    <div class="mb-3">
                        <label for="display_name" class="form-label fw-bold">Display Name</label>
                        <input type="text" class="form-control rounded-3" id="display_name" name="display_name" 
                               value="<?= htmlspecialchars($user['display_name'] ?? $user['username']) ?>" 
                               maxlength="50">
                        <div>your name as displayed on your profile (24 characters max)</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="bio" class="form-label fw-bold">Bio</label>
                        <textarea class="form-control rounded-3" id="bio" name="bio" rows="3" maxlength="160"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span>tell the world about yourself</span>
                            <span id="bio-counter" ><?= strlen($user['bio'] ?? '') ?>/160</span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="d-flex justify-content-between mt-4 pt-2 border-top" style="border-color: var(--gray-700) !important;">
                        <a href="profile.php" class="btn btn-outline-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Sidebar -->
    <?php render_right_sidebar(); ?> 
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const profileForm = document.querySelector('form');
    const displayNameInput = document.getElementById('display_name');
    const bioTextarea = document.getElementById('bio');
    const bioCounter = document.getElementById('bio-counter');
    const saveButton = profileForm.querySelector('button[type="submit"]');
    
    // Max allowed characters
    const MAX_DISPLAY_NAME_LENGTH = 24;
    const MAX_BIO_LENGTH = 128;
    
    // Function to validate the form and update button state
    function validateForm() {
        const displayNameLength = displayNameInput.value.length;
        const bioLength = bioTextarea.value.length;
        
        let isValid = true;
        
        // Validate display name
        if (displayNameLength > MAX_DISPLAY_NAME_LENGTH) {
            isValid = false;
        }
        
        // Validate bio
        if (bioLength > MAX_BIO_LENGTH) {
            isValid = false;
        }
        
        // Update button state
        saveButton.disabled = !isValid;
    }
    
    // Profile Picture Preview (existing code)
    const profilePictureInput = document.getElementById('profile_picture');
    const profilePicPreview = document.getElementById('profile-pic-preview');
    
    profilePictureInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Check if preview is an img element or the default div
                if (profilePicPreview.tagName === 'IMG') {
                    // Update existing image
                    profilePicPreview.src = e.target.result;
                } else {
                    // Replace div with new image
                    const newImg = document.createElement('img');
                    newImg.src = e.target.result;
                    newImg.id = 'profile-pic-preview';
                    newImg.alt = 'Profile';
                    newImg.className = 'profile-picture-edit-img';
                    
                    profilePicPreview.parentNode.replaceChild(newImg, profilePicPreview);
                }
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Cover Image Preview (existing code)
    const coverImageInput = document.getElementById('cover_image');
    const coverPreview = document.getElementById('cover-preview');
    
    coverImageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                coverPreview.style.backgroundImage = `url(${e.target.result})`;
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Enhanced Bio Character Counter with validation
    bioTextarea.addEventListener('input', function() {
        const count = this.value.length;
        bioCounter.textContent = `${count}/128`;
        
        // Color coding based on character count
        if (count > 128-15) {
            bioCounter.classList.remove('text-danger');
            bioCounter.classList.add('text-warning');
        } else {
            bioCounter.classList.remove('text-warning', 'text-danger');
        }
        
        if (count > 128) {
            bioCounter.classList.remove('text-warning');
            bioCounter.classList.add('text-danger');
        }
        
        // Run form validation
        validateForm();
    });
    
    // Display name validation
    displayNameInput.addEventListener('input', validateForm);
    
    // Initial validation on page load
    validateForm();
});
</script>

<?php render_footer(); ?>