<div class="p-3 border-bottom <?php echo isset($bg_light) ? 'bg-light' : ''; ?>">
    <?php if (isset($is_reply) && $is_reply): ?>
    <h5>Post a reply</h5>
    <?php endif; ?>
    
    <form method="post" action="" class="composer-form">
        <div class="position-relative">
            <!-- Hidden actual input that gets submitted -->
            <input type="hidden" name="tweet_content" id="hidden-content">
            
            <!-- Editable content area that will display formatting -->
            <div class="form-control modern-composer" 
                 contenteditable="true"
                 data-placeholder="<?php echo isset($placeholder) ? $placeholder : 'What\'s happening?'; ?>"
                 data-max-length="280"></div>
                 
            <!-- Character counter with visual indicator -->
            <div class="character-counter d-flex align-items-center mt-2">
                <div class="progress flex-grow-1" style="height: 4px;">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="ms-2 small text-muted">
                    <span class="chars-used">0</span>/280
                </div>
            </div>
            
            <!-- Suggestions panel for hashtags and mentions -->
            <div class="suggestions-panel card border-0 shadow-sm d-none">
                <div class="card-body p-0"></div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary rounded-pill px-4 submit-btn" disabled>