<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Y | Connect with the world</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        /* Card styles to match app */
        .card {
            transition: box-shadow 0.2s ease;
            border: none !important;
        }
        
        .card:hover {
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important;
        }
        
        /* Button styles to match */
        .btn-primary {
            background-color: #1da1f2;
            border-color: #1da1f2;
        }
        
        .btn-primary:hover {
            background-color: #1a91da;
            border-color: #1a91da;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* Y logo styling */
        .logo-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1da1f2, #0c66a0);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-text {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            line-height: 1;
        }
        
        /* Feature icon styling - UPDATED for perfect circles */
        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0;
            margin-bottom: 1rem;
            flex-shrink: 0; /* Prevents the icon from shrinking */
            position: relative; /* Ensures proper positioning */
            aspect-ratio: 1/1; /* Forces a 1:1 aspect ratio */
        }

        /* Ensure the icon containers in the feature section are properly spaced */
        .col-md-6 .d-flex .feature-icon,
        .col-md-4 .feature-icon {
            margin-right: 1rem;
            margin-bottom: 0;
        }

        /* Ensure content is centered perfectly in the circle */
        .feature-icon i {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        /* Feature card styles */
        .feature-card {
            height: 100%;
        }
        
        /* Footer styling */
        .footer-link {
            font-size: 0.85rem;
            color: #6c757d;
            text-decoration: none;
            margin: 0 0.5rem;
        }
        
        .footer-link:hover {
            color: #1da1f2;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header with logo -->
                <div class="text-center mb-5">
                    <div class="logo-wrapper mx-auto mb-4">
                        <div class="logo-text">y</div>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">Welcome to Y</h1>
                    <p class="fs-4 text-muted mb-5">A modern platform to express yourself, connect with others, and discover what matters to you</p>
                    
                    <!-- Main action buttons -->
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="/y/public/app/feed.php" class="btn btn-primary rounded-pill px-5 py-3 fw-medium fs-5">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Enter Y
                        </a>
                        <a href="/y/public/auth/signup.php" class="btn btn-outline-secondary rounded-pill px-5 py-3 fw-medium fs-5">
                            <i class="bi bi-person-plus me-2"></i>Create an account
                        </a>
                    </div>
                </div>
                
                <!-- Top features section -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h2 class="card-title fw-bold text-center mb-4">Core Features</h2>
                        
                        <div class="row g-4">
                            <!-- Feature 1: Share -->
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="feature-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-chat-square-text fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="fs-5 fw-bold">Express Yourself</h3>
                                        <p class="text-muted">Share your thoughts, ideas, and experiences with posts. Add hashtags to join conversations around topics you care about.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Feature 2: Connect -->
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="feature-icon bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-people-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="fs-5 fw-bold">Build Your Network</h3>
                                        <p class="text-muted">Follow influencers, friends, and thought leaders. Connect with like-minded people and build your personal community.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Feature 3: Notifications -->
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="feature-icon bg-danger bg-opacity-10 text-danger me-3">
                                        <i class="bi bi-bell-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="fs-5 fw-bold">Stay Updated</h3>
                                        <p class="text-muted">Notifications keep you informed about likes, reposts, follows, and mentions. Smart grouping prevents notification overload.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Feature 4: Discover -->
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="feature-icon bg-info bg-opacity-10 text-info me-3">
                                        <i class="bi bi-search fs-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="fs-5 fw-bold">Discover Content</h3>
                                        <p class="text-muted">Powerful search helps you find topics, hashtags, and people that match your interests with context-aware results.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Expanded features section -->
                <div class="row g-4 mb-5">
                    <!-- Feature card: Engagement -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 feature-card">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-hand-thumbs-up fs-4"></i>
                                </div>
                                <h3 class="fs-5 fw-bold mt-3">Meaningful Engagement</h3>
                                <p class="text-muted">Like, repost, and reply to content that resonates with you. Bookmark posts to revisit your favorite content later.</p>
                                <ul class="text-muted ps-4">
                                    <li>One-click reactions</li>
                                    <li>Content sharing</li>
                                    <li>Personal bookmarks collection</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feature card: Personalization -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 feature-card">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-person-badge fs-4"></i>
                                </div>
                                <h3 class="fs-5 fw-bold mt-3">Profile Customization</h3>
                                <p class="text-muted">Express who you are through your profile. Add a profile picture, bio, and customize your experience.</p>
                                <ul class="text-muted ps-4">
                                    <li>Custom profile pictures</li>
                                    <li>Personalized bio</li>
                                    <li>Display name flexibility</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feature card: Trends -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 feature-card">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-hash fs-4"></i>
                                </div>
                                <h3 class="fs-5 fw-bold mt-3">Trending Topics</h3>
                                <p class="text-muted">Discover what's happening right now. Explore trending hashtags and join conversations that matter.</p>
                                <ul class="text-muted ps-4">
                                    <li>Real-time trending hashtags</li>
                                    <li>Topic exploration</li>
                                    <li>Content discovery</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced features -->
                <div class="row g-4 mb-5">
                    <!-- Feature card: Conversation threading -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 feature-card">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-purple bg-opacity-10" style="color: #6f42c1;">
                                    <i class="bi bi-chat-square-dots fs-4"></i>
                                </div>
                                <h3 class="fs-5 fw-bold mt-3">Conversation Threading</h3>
                                <p class="text-muted">Follow organized conversations with nested replies. Easily track discussion context and participate meaningfully.</p>
                                <ul class="text-muted ps-4">
                                    <li>Nested replies</li>
                                    <li>Context preservation</li>
                                    <li>Efficient discussions</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feature card: Content filtering -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 feature-card">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-funnel fs-4"></i>
                                </div>
                                <h3 class="fs-5 fw-bold mt-3">Content Filtering</h3>
                                <p class="text-muted">Customize your feed with content filters. Toggle between latest posts and posts from people you follow.</p>
                                <ul class="text-muted ps-4">
                                    <li>Content curation</li>
                                    <li>Filter toggles</li>
                                    <li>Relevant recommendations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feature card: Security -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 feature-card">
                            <div class="card-body p-4">
                                <div class="feature-icon bg-secondary bg-opacity-10 text-secondary">
                                    <i class="bi bi-shield-check fs-4"></i>
                                </div>
                                <h3 class="fs-5 fw-bold mt-3">Security & Privacy</h3>
                                <p class="text-muted">Your security matters. Y implements robust password protections and privacy controls for your peace of mind.</p>
                                <ul class="text-muted ps-4">
                                    <li>Strong password enforcement</li>
                                    <li>Secure authentication</li>
                                    <li>Data protection</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Call to action -->
                <div class="card border-0 shadow-sm rounded-4 mb-5 bg-primary bg-opacity-10">
                    <div class="card-body p-5 text-center">
                        <h2 class="fs-3 fw-bold mb-3">Ready to experience Y?</h2>
                        <p class="mb-4 fs-5">Join our community and start sharing what matters to you.</p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="/y/public/auth/signup.php" class="btn btn-outline-primary rounded-pill px-5 py-2 fw-medium">
                                Get started now
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <footer class="text-center mt-4 mb-3">
                    <div class="mb-3">
                        <a href="#" class="footer-link">About</a>
                        <a href="#" class="footer-link">Help Center</a>
                        <a href="#" class="footer-link">Terms</a>
                        <a href="#" class="footer-link">Privacy Policy</a>
                        <a href="#" class="footer-link">Cookie Policy</a>
                        <a href="#" class="footer-link">Accessibility</a>
                    </div>
                    <div class="text-muted small">
                        © 2025 Y. All rights reserved.
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>