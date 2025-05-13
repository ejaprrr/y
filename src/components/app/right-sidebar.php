<?php
function render_right_sidebar() {
?>
<div class="right-sidebar d-flex flex-column">
    <div class="p-3">
        <div class="card rounded-4 mb-4">
            <div class="p-3">
                <h3 class="fs-5 mb-3">trends for you</h3>
                <div>
                    <div class="mb-3 pb-2 separator">
                        <small>trending worldwide</small>
                        <div class="fw-bold">#ExampleTrend1</div>
                        <small>10.5K posts</small>
                    </div>
                    <div class="mb-3 pb-2 separator">
                        <small>technology</small>
                        <div class="fw-bold">#ExampleTrend2</div>
                        <small>5.2K posts</small>
                    </div>
                    <div class="mb-3"></div>
                        <small>entertainment</small>
                        <div class="fw-bold">#ExampleTrend3</div>
                        <small>3.7K posts</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="links-wrapper d-flex w-100 mt-auto gap-3 justify-content-center">
        <a href="../about/index.php" class="d-block mb-3 text-lowercase">home</a>
        <a href="../about/about-y.php" class="d-block mb-3 text-lowercase">about y</a>
        <span class="mb-3">&copy; Y, 2025</span>
    </div>
    </div>
</div>
<?php
}
?>
