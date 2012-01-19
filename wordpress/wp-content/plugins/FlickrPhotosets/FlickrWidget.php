<?php
class FlickrPhotosetsWidget extends WP_Widget {
  	function __construct() {
  		// widget actual processes
  		parent::__construct(false, $name = 'Flickr Photosets Widget');
  	}

  	function form($instance) {
  		// outputs the options form on admin
  		$title = $instance['title'];
  		$link_target = $instance['link_target'];
  		?>
  		<p>
	      <label for="<?php echo $this->get_field_id('link_target'); ?>">Link target</label>
  	    <?php wp_dropdown_pages(array('name' => $this->get_field_name('link_target'), 'selected' => $link_target)) ?>
  	  </p>
  	  <p>
	      <label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
  	    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>">
  	  </p>
  		
  		<?php
  	}

  	function update($new_instance, $old_instance) {
  		// processes widget options to be saved
  		return $new_instance;
  	}

  	function widget($args, $instance) {
  		$options = get_option('flickr_photosets_options');
  		$link = get_permalink($instance['link_target'])
  		?>
  		<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL; ?>/FlickrPhotosets/css/flickr.css" type="text/css" media="screen" />
  		<div class="widget widget-flickr">
      		<header class="widget-header">
      		    <h2><a href="<?php echo $link ?>"><?php echo $instance['title']; ?></a></h2>
      		</header>
  		    <div id="flickr-widget" class="widget-content" style="position: relative;">
  		    </div>
  		    <script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/FlickrPhotosets/js/flickrphotosets.js"></script>
  		    <script type="text/javascript">
                flickrphotosets.api_key = "<?php echo $options['apikey'] ?>";
                flickrphotosets.user_id = "<?php echo $options['username'] ?>";
                flickrphotosets.link_url = "<?php echo $link ?>";
                
                flickrphotosets.texts.flickr_link = "Albumi Flickr-palvelussa";
                flickrphotosets.texts.updated = "päivitetty";
                flickrphotosets.texts.photos = "kuvaa";
                
                jQuery(function() {
                    flickrphotosets.initWidget('#flickr-widget');
                });
            </script>
        </div>
    <?php
  	}

}
  add_action('widgets_init', create_function('', 'return register_widget("FlickrPhotosetsWidget");'));

?>