<div class="tweet p-3" id="post-<?php echo $post['id']; ?>">
    <?php if ($post['reposted_by']): ?>
        <div class="text-muted mb-2 small">
            <i class="bi bi-repeat"></i> Reposted by @<?php echo htmlspecialchars($post['reposted_by']); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($post['reply_to_username']): ?>
        <div class="text-muted mb-2 small">
            <i class="bi bi-chat"></i> @<?php echo htmlspecialchars($post['username']); ?> replying to @<?php echo htmlspecialchars($post['reply_to_username']); ?>
            <div class="ms-3 ps-2 border-start text-muted">
                <p class="small text-truncate m-0">"<?php echo htmlspecialchars($post['reply_to_content']); ?>"</p>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="d-flex">
        <div class="me-3">
            <?php 
                $profile_picture_url = $post['profile_picture_url'];
                $username = $post['username'];
                include __DIR__ . '/user_avatar.php'; 
            ?>
        </div>
        
        <div class="flex-grow-1">
            <div class="d-flex gap-2 mb-1 align-items-center">
                <div class="fw-bold"><?php echo htmlspecialchars($post['display_name']); ?></div>
                <div>
                    <a href="profile.php?username=<?php echo $post['username']; ?>" class="text-decoration-none text-muted">
                        @<?php echo htmlspecialchars($post['username']); ?>
                    </a>
                </div>
                <div class="text-muted">·</div>
                <div class="text-muted"><?php echo htmlspecialchars($post['timestamp']); ?></div>
                
                <!-- Add delete option for post owner -->
                <?php if ($post['username'] === $user['user_name']): ?>
                <div class="ms-auto">
                    <div class="dropdown">
                        <button class="btn btn-sm text-muted border-0 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button type="button" 
                                    class="dropdown-item text-danger delete-post-btn" 
                                    data-post-id="<?php echo $post['id']; ?>"
                                    data-post-content="<?php echo htmlspecialchars($post['content']); ?>"
                                    data-reply-count="<?php echo $post['reply_count']; ?>">
                                    <i class="bi bi-trash me-2"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <p class="mb-2">
                <a href="post.php?id=<?php echo $post['id']; ?>" class="text-decoration-none text-dark">
                    <?php echo format_content_with_tags(htmlspecialchars($post['content'])); ?>
                </a>
            </p>
            
            <!-- Remove media gallery include -->
            
            <?php include __DIR__ . '/post_actions.php'; ?>
        </div>
    </div>
</div>

<!-- Delete Post Modal -->
<?php if ($post['username'] === $user['user_name']): ?>
<div class="modal fade" id="deletePostModal<?php echo $post['id']; ?>" tabindex="-1" aria-labelledby="deletePostModalLabel<?php echo $post['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePostModalLabel<?php echo $post['id']; ?>">Delete Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this post? This action cannot be undone.</p>
                <div class="border rounded p-3 mb-3">
                    <p class="mb-0"><?php echo htmlspecialchars($post['content']); ?></p>
                </div>
                <?php if ($post['reply_count'] > 0): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This post has <?php echo $post['reply_count']; ?> replies that will also be deleted.
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="delete_post.php" method="post">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>