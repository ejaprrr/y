<div class="d-flex gap-4 post-actions" data-post-id="<?php echo $post['id']; ?>">
    <!-- Like button -->
    <button type="button" class="btn btn-sm text-muted p-0 border-0 post-action" data-action="like" data-post-id="<?php echo $post['id']; ?>">
        <span class="like-icon">
            <?php echo $post['user_liked'] ? '<i class="bi bi-heart-fill text-danger"></i>' : '<i class="bi bi-heart"></i>'; ?>
        </span>
        <span class="small like-count"><?php echo $post['like_count']; ?></span>
    </button>
    
    <!-- Repost button -->
    <button type="button" class="btn btn-sm text-muted p-0 border-0 post-action" data-action="repost" data-post-id="<?php echo $post['id']; ?>">
        <span class="repost-icon">
            <?php echo $post['user_reposted'] ? '<i class="bi bi-repeat text-success"></i>' : '<i class="bi bi-repeat"></i>'; ?>
        </span>
        <span class="small repost-count"><?php echo $post['repost_count']; ?></span>
    </button>
    
    <!-- Reply button (no AJAX, keeps the original behavior) -->
    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm text-muted p-0 border-0">
        <i class="bi bi-chat"></i>
        <span class="small"><?php if ($post['reply_count'] > 0): ?><?php echo $post['reply_count']; ?><?php endif; ?></span>
    </a>
    
    <!-- Bookmark button -->
    <button type="button" class="btn btn-sm text-muted p-0 border-0 post-action" data-action="bookmark" data-post-id="<?php echo $post['id']; ?>" title="<?php echo $post['user_bookmarked'] ? 'Remove from bookmarks' : 'Add to bookmarks'; ?>">
        <span class="bookmark-icon">
            <?php echo $post['user_bookmarked'] ? '<i class="bi bi-bookmark-fill text-primary"></i>' : '<i class="bi bi-bookmark"></i>'; ?>
        </span>
    </button>
</div>