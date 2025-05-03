<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);
$message = '';
$success = false;

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/../../public/uploads/profile_pics/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'display_name' => trim($_POST['display_name'] ?? ''),
        'profile_bio_content' => trim($_POST['bio'] ?? '')
    ];
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file = $_FILES['profile_picture'];
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            // Generate unique filename
            $filename = $user['user_name'] . '_' . time() . '_' . basename($file['name']);
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update profile with the new image URL
                $relative_path = '/y/public/uploads/profile_pics/' . $filename;
                $data['profile_picture_url'] = $relative_path;

                // Update profile
                if (update_user_profile($conn, $user['user_name'], $data)) {
                    $success = true;
                    $message = "Profile updated successfully!";
                    // Refresh user data
                    $user = find_user($conn, $user['user_name']);
                } else {
                    $message = "Failed to update profile.";
                }
            } else {
                $message = "Failed to move uploaded file.";
            }
        } else {
            $message = "Invalid image. Please upload a JPG or PNG under 2MB.";
        }
    } else {
        // Just update the profile without changing the picture
        if (update_user_profile($conn, $user['user_name'], $data)) {
            $success = true;
            $message = "Profile updated successfully!";
            // Refresh user data
            $user = find_user($conn, $user['user_name']);
        } else {
            $message = "Failed to update profile.";
        }
    }
}

// Set up page variables
$page_title = 'Y | Edit Profile';
$page_header = 'Edit Profile';

// Capture content in a buffer
ob_start();
?>

<div class="edit-profile-container p-3">
    <?php if ($message): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show mb-3">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <form method="post" enctype="multipart/form-data" class="p-0">
            <!-- Profile banner -->
            <div class="profile-banner bg-primary bg-gradient w-100 position-relative" style="height: 150px;">
                <div class="banner-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-flex align-items-center justify-content-center">
                    <!-- Banner edit button could go here in the future -->
                </div>
            </div>
            
            <!-- Profile info form -->
            <div class="profile-form-content px-4 pt-5 pb-4 position-relative">
                <!-- Profile picture upload -->
                <div class="profile-picture-upload position-absolute" style="top: -60px; left: 24px;">
                    <div class="rounded-circle overflow-hidden position-relative bg-white" style="width: 120px; height: 120px; border: 4px solid white;">
                        <div class="profile-pic-preview rounded-circle overflow-hidden w-100 h-100">
                            <?php if (!empty($user['profile_picture_url'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture_url']); ?>" 
                                    alt="Profile" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                                    <i class="bi bi-person-circle text-secondary" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Camera icon overlay -->
                        <div class="camera-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center opacity-0 transition-opacity">
                            <label for="profile-picture" class="mb-0 cursor-pointer text-white">
                                <i class="bi bi-camera fs-4"></i>
                            </label>
                            <input type="file" id="profile-picture" name="profile_picture" accept="image/jpeg,image/png" class="d-none">
                        </div>
                    </div>
                </div>
                
                <!-- Form fields -->
                <div class="mb-4 mt-3">
                    <label for="display-name" class="form-label fw-bold">Display Name</label>
                    <input type="text" class="form-control form-control-lg" id="display-name" name="display_name" 
                        value="<?php echo htmlspecialchars($user['display_name'] ?? $user['user_name']); ?>" 
                        maxlength="50">
                    <div class="form-text">This is how your name will appear on Y.</div>
                </div>
                
                <div class="mb-4">
                    <label for="bio" class="form-label fw-bold">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="4" 
                            maxlength="160"><?php echo htmlspecialchars($user['profile_bio_content'] ?? ''); ?></textarea>
                    <div class="d-flex justify-content-between">
                        <div class="form-text">Share a little about yourself.</div>
                        <div class="form-text character-count">
                            <span id="bio-counter">0</span>/160
                        </div>
                    </div>
                </div>
                
                <!-- Form actions -->
                <div class="d-flex justify-content-between pt-3">
                    <a href="profile.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .profile-banner {
        background: linear-gradient(135deg, #1da1f2, #0c66a0);
    }
    .profile-picture-upload:hover .camera-overlay {
        opacity: 1 !important;
        cursor: pointer;
    }
    .transition-opacity {
        transition: opacity 0.2s ease;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    // For bio character counter
    document.addEventListener('DOMContentLoaded', function() {
        const bioField = document.getElementById('bio');
        const bioCounter = document.getElementById('bio-counter');
        
        function updateCounter() {
            const count = bioField.value.length;
            bioCounter.textContent = count;
            
            if (count > 140) {
                bioCounter.classList.add('text-warning');
            } else {
                bioCounter.classList.remove('text-warning');
            }
            
            if (count >= 160) {
                bioCounter.classList.add('text-danger');
            } else {
                bioCounter.classList.remove('text-danger');
            }
        }
        
        // Initialize counter
        updateCounter();
        
        // Update on input
        bioField.addEventListener('input', updateCounter);
        
        // Preview uploaded image
        document.getElementById('profile-picture').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-pic-preview').innerHTML = 
                        `<img src="${e.target.result}" alt="Preview" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
// Render with layout
include __DIR__ . '/../../resources/components/layout.php';
?>