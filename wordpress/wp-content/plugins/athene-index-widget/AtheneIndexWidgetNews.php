<?php
class AtheneIndexWidgetNews extends WP_Widget {
  	function __construct() {
  		// widget actual processes
  		parent::__construct(false, $name = 'Athene Index Widget for News');
  	}

  	function form($instance) {
  	  $pageId = $instance ? $instance['page'] : 0;
  	  $categoryId = $instance ? $instance['category'] : 0;
  	  $show_time = sizeof($instance) > 0 ? $instance['show_time'] : 'on';
  	  $show_date = sizeof($instance) > 0 ? $instance['show_date'] : 'on';
  	  $template = $instance['template'];
  	  $title = $instance['title'];
  	  $items = $instance['items'];
  	  if (empty($items)) {
  	    $items = 2;
  	  }
  	  ?>
  	  <!-- <?php print_r($instance) ?> -->
  	  <p>
  	  <label for="<?php echo $this->get_field_id('page'); ?>">Page</label>
  	  <select id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>">
  	    <?php $pagedepths = array(); ?>
  	    <?php foreach(get_pages(array()) as $page) { ?>
          <?php 
  	        if ($page->post_parent > 0) {
  	          $pagedepths[$page->ID] = $pagedepths[$page->post_parent]+1;
  	        } else {
  	          $pagedepths[$page->ID] = 0;
  	        }
  	      ?>
  	      <option value="<?php echo $page->ID ?>" 
  	        <?php echo $pageId == $page->ID ? 'selected="selected"' : ""; ?>>
  	        <?php echo str_repeat("&nbsp;", $pagedepths[$page->ID]*2); ?>
  	        <?php echo $page->post_title ?></option>
  	    <?php } ?>
  	  </select>
  	  </p>
  	  <p>
  	  <label for="<?php echo $this->get_field_id('category'); ?>">News category</label>
  	  <?php wp_dropdown_categories(array('hide_empty' => 0, 'id' => $this->get_field_id('category'), 'name' => $this->get_field_name('category'), 'hierarchical' => true, 'selected' => $categoryId)); ?>
  	  </p>
  	  <p>
	      <label for="<?php echo $this->get_field_id('title'); ?>" title="To use page title, leave empty">Override title</label>
  	    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>">
  	  </p>
  	  <p>
  	  <input type="checkbox" id="<?php echo $this->get_field_id('show_time'); ?>" name="<?php echo $this->get_field_name('show_time'); ?>" <?php echo $show_time == 'on' ? 'checked="checked"' : '' ?>>
  	  <label for="<?php echo $this->get_field_id('show_time'); ?>">Show times</label>
  	  </p>
  	  <p>
  	  <input type="checkbox" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" <?php echo $show_date == 'on' ? 'checked="checked"' : '' ?>>
  	  <label for="<?php echo $this->get_field_id('show_date'); ?>">Show dates</label>
  	  </p>
  	  <p>
	      <label for="<?php echo $this->get_field_id('items'); ?>">Items to show</label><br/>
  	    <input type="number" id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" value="<?php echo $items; ?>">
  	  </p>
  	  <p>
	      <label for="<?php echo $this->get_field_id('template'); ?>">Template file</label>
  	    <input type="text" id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>" value="<?php echo $template; ?>">
  	  </p>
  	  <?php
  		// outputs the options form on admin
  	}

  	function update($new_instance, $old_instance) {
  		// processes widget options to be saved
  		return $new_instance;
  	}

  	function widget($args, $instance) {
  	  $items = $instance['items'];
  	  if (empty($items)) {
  	    $items = 2;
  	  }
      $page = get_post_complete($instance['page']);
      $news = get_posts(array('cat' => $instance['category'], 'numberposts' => $items));

        $dateString = "";
        if ($instance['show_date'] == 'on') {
          $dateString .= get_option('date_format').' ';
        }
        if ($instance['show_time'] == 'on') {
          $dateString .= get_option('time_format');
        }
        $dateString = trim($dateString);
  	  
  	  // load the template
  	  $template = TEMPLATEPATH.'/'.$instance['template'];
  	  if (strlen($instance['template']) > 0 && is_readable($template)) {
  	    include($template);
  	  } else {
  	    include('templates/widget-news.php');
  	  }
  	}

}
  add_action('widgets_init', create_function('', 'return register_widget("AtheneIndexWidgetNews");'));

?>