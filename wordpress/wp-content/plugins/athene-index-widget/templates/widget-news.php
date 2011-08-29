<h1><a href="<?php echo get_permalink($instance['page']) ?>"><?php echo !empty($instance['title']) ? $instance['title'] : $page->post_title ?></a></h1>
<?php foreach($news as $news_item) { ?>
  <!-- <?php print_r($news_item) ?> -->
  <p><a href="<?php echo get_permalink($news_item->ID) ?>"><?php echo $news_item->post_title ?></a></p>
	  <?php $news_date = date($dateString,strtotime($news_item->post_date)) ?>
      <p><?php echo $news_date ?></p>
<?php } ?>