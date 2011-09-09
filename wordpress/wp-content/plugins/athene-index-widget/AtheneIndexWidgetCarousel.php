<?php
class AtheneIndexWidgetCarousel extends AtheneIndexWidget {
  	function __construct() {
  		// widget actual processes
  		parent::__construct(false, $name = 'Athene Index Widget for Photo Carousel');
  	}

  	function form($instance) {
  	  $photos = $instance['photos'];
  	  ?>
  	  <p>This plugin can be configured in the Nivo Slider for WordPress page</p>
  	  <?php
  		// outputs the options form on admin
  	}

  	function update($new_instance, $old_instance) {
  		// processes widget options to be saved
  		return $new_instance;
  	}

  	function widget($args, $instance) {
  	  if (function_exists('nivoslider4wp_show')) { 
  	    nivoslider4wp_show(); 
  	  } else {
  	    echo 'Nivo Slider for WordPress not installed. <a href="http://wordpress.org/extend/plugins/nivo-slider-for-wordpress/">Install it</a>';
  	  }
  	}

}
  add_action('widgets_init', create_function('', 'return register_widget("AtheneIndexWidgetCarousel");'));

?>