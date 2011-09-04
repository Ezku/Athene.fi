<div class="widget widget-pages">
    <header class="widget-header">
        <h2><a href="<?php echo get_permalink($instance['page']) ?>">
            <?php echo !empty($instance['title']) ? $instance['title'] : $page->post_title ?>
        </a></h2>
    </header>
    <div class="widget-content">
        <article class="page-intro">
            <?php echo $page->intro ?>
        </article>
    </div>
</div>