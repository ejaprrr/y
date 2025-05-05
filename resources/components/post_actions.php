<div class="d-flex gap-4 post-actions" data-post-id="<?php echo $post['id']; ?>">
    <!-- Like button with bubble style -->
    <button type="button" class="btn btn-link p-0 border-0 post-action action-bubble" data-action="like" data-post-id="<?php echo $post['id']; ?>">
        <span class="action-icon-wrapper like-icon-wrapper <?php echo $post['user_liked'] ? 'active' : ''; ?>">
            <i class="bi <?php echo $post['user_liked'] ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
        </span>
        <span class="small action-count like-count"><?php echo $post['like_count']; ?></span>
    </button>
    
    <!-- Repost button with bubble style -->
    <button type="button" class="btn btn-link p-0 border-0 post-action action-bubble" data-action="repost" data-post-id="<?php echo $post['id']; ?>">
        <span class="action-icon-wrapper repost-icon-wrapper <?php echo $post['user_reposted'] ? 'active' : ''; ?>">
            <i class="bi bi-repeat"></i>
        </span>
        <span class="small action-count repost-count"><?php echo $post['repost_count']; ?></span>
    </button>
    
    <!-- Reply button with bubble style -->
    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-link p-0 border-0 action-bubble">
        <span class="action-icon-wrapper reply-icon-wrapper">
            <i class="bi bi-chat"></i>
        </span>
        <span class="small action-count reply-count"><?php if ($post['reply_count'] > 0): ?><?php echo $post['reply_count']; ?><?php endif; ?></span>
    </a>
    
    <!-- Bookmark button with bubble style -->
    <button type="button" class="btn btn-link p-0 border-0 post-action action-bubble" 
            data-action="bookmark" 
            data-post-id="<?php echo $post['id']; ?>" 
            title="<?php echo $post['user_bookmarked'] ? 'Remove from bookmarks' : 'Add to bookmarks'; ?>">
        <span class="action-icon-wrapper bookmark-icon-wrapper <?php echo $post['user_bookmarked'] ? 'active' : ''; ?>">
            <i class="bi <?php echo $post['user_bookmarked'] ? 'bi-bookmark-fill' : 'bi-bookmark'; ?>"></i>
        </span>
    </button>
</div>