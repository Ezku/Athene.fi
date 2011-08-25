<?php
$page = get_post_complete($instance['page']);
$news = get_posts(array('cat' => $instance['category']));
?>
<h1><?php echo !empty($instance['title']) ? $instance['title'] : $page->post_title ?></h1>
<?php foreach($news as $news_item) { ?>
  <p><?php echo $news_item->post_title ?></p>
  <!-- <?php print_r($news_item) ?> -->
    <?php
      $dateString = "";
      if ($instance['show_date'] == 'on') {
        $dateString .= 'j.n.Y ';
      }
      if ($instance['show_time'] == 'on') {
        $dateString .= 'H:i';
      }
      $dateString = trim($dateString);
      ?>
		  <?php $news_date = date($dateString,strtotime($news_item->post_date)) ?>
      <p><?php echo $news_date ?></p>
<?php } ?>