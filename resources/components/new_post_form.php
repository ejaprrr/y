<div class="p-3 border-bottom <?php echo isset($bg_light) ? 'bg-light' : ''; ?>">
    <?php if (isset($is_reply) && $is_reply): ?>
    <h5>Post a reply</h5>
    <?php endif; ?>
    
    <form method="post" action="">
        <textarea 
            name="tweet_content" 
            class="form-control <?php echo isset($no_border) ? 'border-0' : ''; ?> mb-3" 
            rows="<?php echo isset($rows) ? $rows : '3'; ?>" 
            maxlength="280" 
            placeholder="<?php echo isset($placeholder) ? $placeholder : 'What\'s happening?'; ?>"
        ></textarea>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4">
                <?php echo isset($button_text) ? $button_text : 'Post'; ?>
            </button>
        </div>
    </form>
</div>