<div class="col-md-3 d-none d-lg-block position-sticky vh-100" style="top: 0;">
    <div class="right-sidebar-content p-3 h-100 d-flex flex-column">
        <!-- Search bar -->
        <div class="search-container mb-4 mt-2">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 rounded-pill-start">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="search" class="form-control border-start-0 bg-light rounded-pill-end" placeholder="Search Y" aria-label="Search">
            </div>
        </div>
        
        <!-- Who to follow section -->
        <div class="who-to-follow-container mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h5 class="card-title fs-5 fw-bold">Who to follow</h5>
                </div>
                <div class="card-body pt-2">
                    <!-- Example user suggestion -->
                    <div class="suggested-user d-flex align-items-center p-2 rounded-3 mb-2 hover-bg-light">
                        <div class="user-avatar rounded-circle overflow-hidden me-3" style="width: 48px; height: 48px;">
                            <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                                <i class="bi bi-person text-secondary"></i>
                            </div>
                        </div>
                        <div class="user-info flex-grow-1 overflow-hidden">
                            <div class="fw-bold text-truncate">Jane Smith</div>
                            <div class="text-muted text-truncate">@janesmith</div>
                        </div>
                        <button class="btn btn-dark btn-sm rounded-pill">Follow</button>
                    </div>
                    
                    <!-- Example user suggestion -->
                    <div class="suggested-user d-flex align-items-center p-2 rounded-3 mb-2 hover-bg-light">
                        <div class="user-avatar rounded-circle overflow-hidden me-3" style="width: 48px; height: 48px;">
                            <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                                <i class="bi bi-person text-secondary"></i>
                            </div>
                        </div>
                        <div class="user-info flex-grow-1 overflow-hidden">
                            <div class="fw-bold text-truncate">John Doe</div>
                            <div class="text-muted text-truncate">@johndoe</div>
                        </div>
                        <button class="btn btn-dark btn-sm rounded-pill">Follow</button>
                    </div>
                    
                    <a href="#" class="text-decoration-none d-block mt-3 text-primary">Show more</a>
                </div>
            </div>
        </div>
        
        <!-- Trends section -->
        <div class="trends-container">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h5 class="card-title fs-5 fw-bold">Trends for you</h5>
                </div>
                <div class="card-body pt-2">
                    <!-- Example trend -->
                    <div class="trend-item p-2 rounded-3 hover-bg-light mb-3">
                        <div class="text-muted small">Trending in Technology</div>
                        <div class="fw-bold">#WebDevelopment</div>
                        <div class="text-muted small">5,123 posts</div>
                    </div>
                    
                    <!-- Example trend -->
                    <div class="trend-item p-2 rounded-3 hover-bg-light mb-3">
                        <div class="text-muted small">Trending in Design</div>
                        <div class="fw-bold">#UXDesign</div>
                        <div class="text-muted small">3,456 posts</div>
                    </div>
                    
                    <!-- Example trend -->
                    <div class="trend-item p-2 rounded-3 hover-bg-light">
                        <div class="text-muted small">Trending in Development</div>
                        <div class="fw-bold">#PHP</div>
                        <div class="text-muted small">2,789 posts</div>
                    </div>
                    
                    <a href="#" class="text-decoration-none d-block mt-3 text-primary">Show more</a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-4 small text-muted">
            <div class="d-flex flex-wrap gap-2">
                <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
                <a href="#" class="text-muted text-decoration-none">Cookie Policy</a>
            </div>
            <div class="mt-2">
                © 2025 Y, Inc.
            </div>
        </footer>
    </div>
</div>

<style>
    /* Right sidebar specific styles */
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
    
    .hover-bg-light:hover {
        background-color: rgba(0, 0, 0, 0.03);
        cursor: pointer;
    }
    
    .card {
        transition: box-shadow 0.2s ease;
    }
    
    .card:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important;
    }
    
    .card-header {
        background: transparent;
    }
</style>