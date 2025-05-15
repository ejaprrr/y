<?php
function render_post_composer() {
?>
<div class="composer">
  <div class="card rounded-4 mx-3 my-3 p-3">
    <form method="POST">
      <!-- CSRF token for security -->
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
      <textarea name="content" placeholder="what's happening?" class="form-control bg-transparent border-0" required></textarea>
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="post-counter-wrapper">
          <span id="post-counter">0/256</span>
        </div>
        <button type="submit" class="btn btn-primary rounded-3 fw-semibold">post</button>
      </div>
    </form>
  </div>
</div>
<?php
}
?>