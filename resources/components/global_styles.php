<style>
    /* Common card styles */
    .card {
        transition: box-shadow 0.2s ease;
        border: none !important;
    }
    
    .card:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important;
    }
    
    /* Post hover effects */
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
    
    /* Post item styles */
    .tweet {
        border-bottom: none !important;
    }
    
    /* Navigation and interaction styles */
    .hover-bg-light:hover {
        background-color: rgba(0, 0, 0, 0.03);
        cursor: pointer;
    }
    
    /* Profile picture styles */
    .profile-pic-container {
        width: 48px;
        height: 48px;
        background-color: #f8f9fa;
    }
    
    /* Follow button styles */
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
    
    /* Pill styles */
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
    
    /* Search highlight styles */
    mark {
        padding: 0;
        border-radius: 2px;
        background-color: rgba(255, 193, 7, 0.3);
    }
    
    /* Fix for stretched links */
    .stretched-link::after {
        z-index: 1;
    }
</style>