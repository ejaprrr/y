<?php 
// Set page title if not already set
if (!isset($page_title)) {
    $page_title = 'Y';
}
?>

<?php include __DIR__ . '/header.php'; ?>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Left Sidebar -->
            <?php include __DIR__ . '/sidebar.php'; ?>
            
            <!-- Main content -->
            <div class="col-md-6 px-0 border-end">
                <?php if (isset($page_header)): ?>
                <div class="fw-bold fs-4 p-3 border-bottom"><?php echo htmlspecialchars($page_header); ?></div>
                <?php endif; ?>
                
                <!-- Display messages -->
                <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show mx-3 mt-3">
                    <?php echo $_SESSION['message']['text']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                    // Clear the message after showing it
                    unset($_SESSION['message']);
                ?>
                <?php endif; ?>
                
                <?php echo $content; ?>
            </div>
            
            <!-- Right Sidebar -->
            <?php include __DIR__ . '/right_sidebar.php'; ?>
        </div>
    </div>
    
    <!-- Delete Post Modal (SHARED) -->
    <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePostModalLabel">Delete Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this post? This action cannot be undone.</p>
                    <div class="border rounded p-3 mb-3">
                        <p class="mb-0" id="deletePostContent"></p>
                    </div>
                    <div id="replyWarning" class="alert alert-warning d-none">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="replyCountMessage"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="delete_post.php" method="post">
                        <input type="hidden" name="post_id" id="deletePostId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core app scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set up delete post modal functionality
            document.querySelectorAll('.delete-post-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    const postId = this.getAttribute('data-post-id');
                    const postContent = this.getAttribute('data-post-content');
                    const replyCount = parseInt(this.getAttribute('data-reply-count') || '0');
                    
                    // Set the values in the modal
                    document.getElementById('deletePostId').value = postId;
                    document.getElementById('deletePostContent').textContent = postContent;
                    
                    // Show/hide reply warning
                    const replyWarning = document.getElementById('replyWarning');
                    if (replyCount > 0) {
                        document.getElementById('replyCountMessage').textContent = 
                            `This post has ${replyCount} replies that will also be deleted.`;
                        replyWarning.classList.remove('d-none');
                    } else {
                        replyWarning.classList.add('d-none');
                    }
                    
                    // Open the modal
                    const modal = new bootstrap.Modal(document.getElementById('deletePostModal'));
                    modal.show();
                });
            });
        });
    </script>
    
    <?php if (isset($extra_scripts)): ?>
    <script>
        <?php echo $extra_scripts; ?>
    </script>
    <?php endif; ?>
</body>
</html>