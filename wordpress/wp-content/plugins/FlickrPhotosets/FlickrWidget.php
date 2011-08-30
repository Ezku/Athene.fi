<?php
class FlickrPhotosetsWidget extends WP_Widget {
  	function __construct() {
  		// widget actual processes
  		parent::__construct(false, $name = 'Flickr Photosets Widget');
  	}

  	function form($instance) {
  		// outputs the options form on admin
  	}

  	function update($new_instance, $old_instance) {
  		// processes widget options to be saved
  		return $new_instance;
  	}

  	function widget($args, $instance) {
  	  
  	  // include scripts & styles
  	  /*wp_enqueue_style('jquery-fancybox-css', plugins_url('fancybox/jquery.fancybox-1.3.4.css', __FILE__));
  		wp_enqueue_script('jquery-easing', plugins_url('fancybox/jquery.easing-1.3.pack.js', __FILE__), array('jquery'));
  		wp_enqueue_script('jquery-mousewheel', plugins_url('fancybox/jquery.mousewheel-3.0.4.pack.js', __FILE__), array('jquery'));
  		wp_enqueue_script('jquery-fancybox', plugins_url('fancybox/jquery.fancybox-1.3.4.pack.js', __FILE__), array('jquery', 'jquery-easing', 'jquery-mousewheel'));
  		wp_enqueue_script('flickr-browser', plugins_url('flickrbrowser.js', __FILE__), array('jquery-fancybox'));
  		*/
  		$options = get_option('flickr_photosets_options');
  		?>
  		<h1><a href="#">Uusimmat kuvat</a></h1>
  		<div id="flickr-widget">
  		  
  		</div>
  		<script type="text/javascript" charset="utf-8" src="<?php echo WP_PLUGIN_URL; ?>/FlickrPhotosets/flickrbrowser.js"></script>
  		<script type="text/javascript" charset="utf-8">
        flickrbrowser.api_key = "<?php echo $options['apikey'] ?>";
        flickrbrowser.user_id = "<?php echo $options['username'] ?>";
        
        jQuery(function() {
          flickrbrowser.showWidget();
        });
      </script>
      <?php
  	}

}
  add_action('widgets_init', create_function('', 'return register_widget("FlickrPhotosetsWidget");'));

?>