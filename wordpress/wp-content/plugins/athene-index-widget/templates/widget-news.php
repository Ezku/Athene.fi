<h1><a href="<?php echo get_permalink($instance['page']) ?>">
  <?php echo !empty($instance['title']) ? $instance['title'] : $page->post_title ?>
</a></h1>
<?php foreach($news as $news_item) { ?>
  <!-- <?php print_r($news_item) ?>-->
  <?php $news_date = date($dateString,strtotime($news_item->post_date)) ?>
  <div class="news-item">
    <p class="title">
      <a href="<?php echo get_permalink($news_item->ID) ?>"><?php echo $news_item->post_title ?></a>
    </p>
    <p class="date">
      <?php echo $news_date ?>
    </p>
    <p class="excerpt">
      <?php echo $this->excerpt($news_item->post_content, 60); ?>
    </p>
  </div>
<?php } ?>