<div class="d-flex gap-4">
    <form method="post" class="d-inline">
        <input type="hidden" name="action" value="like">
        <input type="hidden" name="tweet_id" value="<?php echo $post['id']; ?>">
        <button type="submit" class="btn btn-sm text-muted p-0 border-0">
            <?php echo $post['user_liked'] ? '<i class="bi bi-heart-fill text-danger"></i>' : '<i class="bi bi-heart"></i>'; ?> 
            <span class="small"><?php echo $post['like_count']; ?></span>
        </button>
    </form>
    
    <form method="post" class="d-inline">
        <input type="hidden" name="action" value="repost">
        <input type="hidden" name="tweet_id" value="<?php echo $post['id']; ?>">
        <button type="submit" class="btn btn-sm text-muted p-0 border-0">
            <?php echo $post['user_reposted'] ? '<i class="bi bi-repeat text-success"></i>' : '<i class="bi bi-repeat"></i>'; ?>
            <span class="small"><?php echo $post['repost_count']; ?></span>
        </button>
    </form>
    
    <form method="post" action="post.php?id=<?php echo $post['id']; ?>" class="d-inline">
        <button type="submit" class="btn btn-sm text-muted p-0 border-0">
            <i class="bi bi-chat"></i>
            <span class="small"><?php if ($post['reply_count'] > 0): ?><?php echo $post['reply_count']; ?><?php endif; ?></span>
        </button>
    </form>
</div>