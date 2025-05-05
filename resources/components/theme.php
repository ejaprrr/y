<style>
  :root {
    /* Primary brand colors */
    --bs-primary: #1da1f2;
    --bs-primary-rgb: 29, 161, 242;
    
    /* Action colors - using Bootstrap's color system */
    --bs-like: var(--bs-danger);          /* #dc3545 */
    --bs-repost: var(--bs-success);       /* #198754 */  
    --bs-reply: var(--bs-info);           /* #0dcaf0 */
    --bs-bookmark: var(--bs-warning);     /* #ffc107 */
    
    /* Background variants (10% opacity) */
    --bs-primary-bg-subtle: rgba(var(--bs-primary-rgb), 0.1);
    --bs-like-bg-subtle: rgba(220, 53, 69, 0.1);
    --bs-repost-bg-subtle: rgba(25, 135, 84, 0.1);
    --bs-reply-bg-subtle: rgba(13, 202, 240, 0.1);
    --bs-bookmark-bg-subtle: rgba(255, 193, 7, 0.1);
    
    /* UI constants */
    --bs-hover-bg: rgba(0, 0, 0, 0.03);
    --bs-card-radius: 1rem;
    --bs-card-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --bs-card-shadow-hover: 0 0.5rem 1rem rgba(0, 0, 0, 0.12);
    
    /* Animation speeds */
    --bs-transition-speed: 0.2s;
  }
  
  /* Core element styling */
  .btn-primary {
    --bs-btn-bg: var(--bs-primary);
    --bs-btn-border-color: var(--bs-primary);
    --bs-btn-hover-bg: #1a91da;
    --bs-btn-hover-border-color: #1a91da;
  }
  
  .card {
    --bs-card-border-width: 0;
    --bs-card-border-radius: var(--bs-card-radius);
    box-shadow: var(--bs-card-shadow);
    transition: all var(--bs-transition-speed);
  }
  
  .card:hover {
    box-shadow: var(--bs-card-shadow-hover);
  }
  
  /* Action button styling */
  .action-bubble {
    display: flex;
    align-items: center;
    transition: all var(--bs-transition-speed);
  }
  
  .action-icon-wrapper {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    transition: all var(--bs-transition-speed);
  }
  
  /* Animation keyframes */
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
  }
  
  @keyframes fade-in {
    0% { opacity: 0; }
    100% { opacity: 1; }
  }
  
  /* Action button states with !important to override any conflicts */
  .action-icon-wrapper.bg-like-bg-subtle i.bi-heart-fill {
    color: var(--bs-danger) !important;
  }
  
  .action-icon-wrapper.bg-repost-bg-subtle i.bi-repeat {
    color: var(--bs-success) !important;
  }
  
  .action-icon-wrapper.bg-reply-bg-subtle i.bi-chat {
    color: var(--bs-info) !important;
  }
  
  .action-icon-wrapper.bg-bookmark-bg-subtle i.bi-bookmark-fill {
    color: var(--bs-warning) !important;
  }
</style>