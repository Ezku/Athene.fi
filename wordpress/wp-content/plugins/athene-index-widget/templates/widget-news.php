<div class="widget widget-news">
    
    <header class="widget-header">
        <h2><a href="<?php echo get_permalink($instance['page']) ?>">
            <?php echo !empty($instance['title']) ? $instance['title'] : $page->post_title ?>
        </a></h2>
    </header>
    <div class="widget-content">
        <?php foreach($news as $news_item): ?>
            <article class="news-item date-indexed clearfix">
                <section class="date">
                    <h5><?php echo date('d.m.',strtotime($news_item->post_date)) ?></h5>
                </section>
                <section class="content">
                    <header class="title">
                        <a href="<?php echo get_permalink($news_item->ID) ?>"><?php echo $news_item->post_title ?></a>
                    </header>
                    <section class="excerpt">
                        <?php echo $this->excerpt($news_item->post_content, 60); ?>
                    </section>
                </section>
            </article>
        <?php endforeach; ?>
        <p><a href="<?php echo get_permalink($instance['page']) ?>">
            lisää...
        </a></p>
    </div>
</div>