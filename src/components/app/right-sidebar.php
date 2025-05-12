<?php
function render_right_sidebar() {
?>
<div class="right-sidebar">
    <div class="p-3">
        <div class="card rounded-4 mb-4">
            <div class="card-body p-3">
                <h3 class="fs-5 mb-3 text-lowercase">trends for you</h3>
                <ul class="list-unstyled">
                    <li class="mb-3 pb-2" style="border-bottom: 1px solid var(--gray-700);">
                        <small >trending worldwide</small>
                        <div class="fw-bold">#ExampleTrend1</div>
                        <small >10.5K posts</small>
                    </li>
                    <li class="mb-3 pb-2" style="border-bottom: 1px solid var(--gray-700);">
                        <small >technology</small>
                        <div class="fw-bold">#ExampleTrend2</div>
                        <small >5.2K posts</small>
                    </li>
                    <li class="mb-3">
                        <small >entertainment</small>
                        <div class="fw-bold">#ExampleTrend3</div>
                        <small>3.7K posts</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
}

?>