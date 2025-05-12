<?php
function render_composer() {
?>
<div class="composer">
  <div class="card rounded-4 mx-3 my-3">
    <div class="card-body p-3">
      <form method="POST">
        <textarea name="content" placeholder="what's happening?" class="form-control bg-transparent text-white border-0 mb-3 text-lowercase" required></textarea>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary rounded-3 px-4 text-lowercase fw-semibold">post</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
}
?>