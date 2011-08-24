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
  	  $template = $instance['template']
  	  ?>
  	  <!-- <?php print_r($instance) ?> -->
  	  <p>
  	  <label for="<?php echo $this->get_field_id('page'); ?>">Page</label>
  	  <select id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>">
  	    <?php foreach(get_pages(array()) as $page) { ?>
  	      <option value="<?php echo $page->ID ?>" <?php echo $pageId == $page->ID ? 'selected="selected"' : ""; ?>><?php echo $page->post_title ?></option>
  	    <?php } ?>
  	  </select>
  	  </p>
  	  <p>
  	  <label for="<?php echo $this->get_field_id('category'); ?>">News category</label>
  	  <select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
  	    <?php foreach(get_categories(array('hide_empty' => 0)) as $category) { ?>
  	      <!-- <?php print_r($category) ?> -->
  	      <option value="<?php echo $category->cat_ID ?>" <?php echo $categoryId == $category->cat_ID ? 'selected="selected"' : ""; ?>><?php echo $category->cat_name ?></option>
  	    <?php } ?>
  	  </select>
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