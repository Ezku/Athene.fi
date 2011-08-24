<?php
class AtheneIndexWidgetPages extends WP_Widget {
  	function __construct() {
  		// widget actual processes
  		parent::__construct(false, $name = 'Athene Index Widget for Pages');
  	}

  	function form($instance) {
  	  $pageId = $instance ? $instance['page'] : 0;
  	  $template = $instance['template'];
  	  ?>
  	  <label for="<?php echo $this->get_field_id('page'); ?>">Page</label>
  	  <select id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>">
  	    <?php foreach(get_pages(array()) as $page) { ?>
  	      <option value="<?php echo $page->ID ?>" <?php echo $pageId == $page->ID ? 'selected="selected"' : ""; ?>><?php echo $page->post_title ?></option>
  	    <?php } ?>
  	  </select>
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
  	    include('templates/widget-pages.php');
  	  }
  	}

}
  add_action('widgets_init', create_function('', 'return register_widget("AtheneIndexWidgetPages");'));

?>