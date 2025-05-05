<style>
    :root {
        /* Primary color scheme */
        --y-primary: #1da1f2;
        --y-primary-hover: #1a91da;
        --y-primary-light: rgba(29, 161, 242, 0.1);
        
        /* Action colors */
        --y-like: #dc3545;
        --y-like-light: rgba(220, 53, 69, 0.1);
        --y-repost: #198754;
        --y-repost-light: rgba(25, 135, 84, 0.1);
        --y-reply: #0dcaf0;
        --y-reply-light: rgba(13, 202, 240, 0.1);
        --y-bookmark: #ffc107;
        --y-bookmark-light: rgba(255, 193, 7, 0.1);
        
        /* Background and text colors */
        --y-bg-light: #f8f9fa;
        --y-text-muted: #6c757d;
        
        /* Card and interactive elements */
        --y-card-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12);
        --y-hover-bg: rgba(0, 0, 0, 0.03);
        
        /* Transition speeds */
        --y-transition-speed: 0.2s;
    }
    
    /* Global card styles */
    .card {
        transition: box-shadow var(--y-transition-speed) ease, transform var(--y-transition-speed) ease;
        border: none !important;
    }
    
    .card:hover {
        box-shadow: var(--y-card-shadow) !important;
    }
    
    /* Post hover effects */
    .hover-post:hover {
        box-shadow: var(--y-card-shadow) !important;
        transform: translateY(-2px);
    }
    
    /* Primary button styling with the theme color */
    .btn-primary {
        background-color: var(--y-primary);
        border-color: var(--y-primary);
    }
    
    .btn-primary:hover {
        background-color: var(--y-primary-hover);
        border-color: var(--y-primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .form-control:focus {
        border-color: var(--y-primary);
        box-shadow: 0 0 0 0.25rem rgba(29, 161, 242, 0.25);
    }
    
    /* Typography updates */
    .text-primary {
        color: var(--y-primary) !important;
    }
    
    /* Standardize all hover bg effects */
    .hover-bg-light:hover {
        background-color: var(--y-hover-bg);
        cursor: pointer;
    }
    
    /* Profile picture styles */
    .profile-pic-container {
        width: 48px;
        height: 48px;
        background-color: var(--y-bg-light);
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
        color: var(--y-like);
        border-color: var(--y-like);
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
    
    /* Bookmark button animation */
    .btn i.bi-bookmark,
    .btn i.bi-bookmark-fill {
        transition: all var(--y-transition-speed) ease;
    }
    
    .btn:hover i.bi-bookmark {
        transform: scale(1.2);
        color: var(--y-primary);
    }
    
    .btn i.bi-bookmark-fill {
        animation: bookmark-pulse 0.3s ease-in-out;
    }
    
    @keyframes bookmark-pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.4); }
        100% { transform: scale(1); }
    }
</style>