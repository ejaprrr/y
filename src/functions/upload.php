<?php
function validate_image($file) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "Upload failed with error code " . $file['error'];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return "File is too large. Maximum size is 5MB.";
    }
    
    // Check file type
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);
    
    if (!in_array($extension, $allowed_extensions)) {
        return "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
    }
    
    return true;
}

?>