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
  	  /*$photos = explode("\n", $instance['photos']);
  	  $options = get_option('index_carousel_options');
  	  ?>
        <link rel="stylesheet" href="<?php echo plugins_url('slider/nivo-slider.css', __FILE__) ?>" type="text/css" media="screen" />
        <script src="<?php echo plugins_url('slider/jquery.nivo.slider.pack.js', __FILE__) ?>" type="text/javascript"></script>
        <div id="slider" class="nivoSlider">
          <?php foreach($options as $photo): ?>
            <img src="<?php echo trim($photo['url']) ?>" style="width: 960px; height: 200px; background-position: 100px 200px" alt="" />
          <?php endforeach; ?>
        </div>
        <script type="text/javascript">
        jQuery(window).load(function() {
            jQuery('#slider').nivoSlider({controlNav: false});
        });
        </script>
  	  <?php*/
  	  if (function_exists('nivoslider4wp_show')) { 
  	    nivoslider4wp_show(); 
  	  } else {
  	    echo 'Nivo Slider for WordPress not installed. <a href="http://wordpress.org/extend/plugins/nivo-slider-for-wordpress/">Install it</a>';
  	  }
  	}

}
/*
// add pictures
add_action('admin_init', 'index_carousel_options_init' );

function index_carousel_options_init() {
  register_setting( 'index_carousel_options', 'index_carousel_options' );
  add_settings_section('index_carousel_main', 'Main Settings', 'carousel_plugin_section_text', 'index-carousel');
  add_settings_field('flick_photosets_username', '', 'carousel_plugin_setting_string', 'index-carousel', 'index_carousel_main');
}

function carousel_plugin_section_text() {
}

function carousel_plugin_setting_string() {
  $options = get_option('index_carousel_options');
  ?>
  <script src="<?php echo plugins_url('jcrop/jquery.Jcrop.min.js', __FILE__) ?>" type="text/javascript"></script>
	<link rel="stylesheet" href="<?php echo plugins_url('jcrop/jquery.Jcrop.css', __FILE__) ?>" type="text/css" />
  <?php
  if (is_array($options)) {
	  foreach($options as $i => $photo) {
	    if (!empty($photo['x']) && !empty($photo['y']) && !empty($photo['w']) && !empty($photo['h'])) {
	      $dimensions = $photo;
	    } else {
	      $dimensions = NULL;
	    }
	    
	    print_carousel_picture($i, $photo['url'], $dimensions);
	  }
  }
	print_carousel_form('', "", array(0,0,0,0), "Add new");
}

function print_carousel_form($i, $url, $dimensions, $label = "Photo") {
  ?>
  <tr>
		<th scope='row'><?php echo $label ?></th>
		<td id="index_carousel_addnew">
		  <script type="text/javascript" charset="utf-8">
		    function addPicField() {
		      jQuery('#index_carousel_addnew').html("<input id='index_carousel_username' name='index_carousel_options[<?php echo $i ?>][url]' size='40' type='text' value='<?php echo $url ?>' />")
		    }
		  </script>
		  <label for=""></label><input type="button" name="addnew" value="Add new" onclick="addPicField();">
		</td>
	</tr> <!-- TODO delete -->
  <?php
}

function print_carousel_picture($i, $url, $dimensions, $label = "Photo") {
  ?>
  <tr>
		<th scope='row'><?php echo $label ?></th>
		<td>
		  <img src="<?php echo $url ?>" id="carousel-photo-<?php echo $i ?>">
		  <input type="hidden" id="carousel-photo-<?php echo $i ?>-x" name="index_carousel_options[<?php echo $i ?>][x]">
		  <input type="hidden" id="carousel-photo-<?php echo $i ?>-y" name="index_carousel_options[<?php echo $i ?>][y]">
		  <input type="hidden" id="carousel-photo-<?php echo $i ?>-w" name="index_carousel_options[<?php echo $i ?>][w]">
		  <input type="hidden" id="carousel-photo-<?php echo $i ?>-h" name="index_carousel_options[<?php echo $i ?>][h]">
		  <input type="hidden" id="carousel-photo-<?php echo $i ?>-url" name="index_carousel_options[<?php echo $i ?>][url]" value="<?php echo $url ?>">
		  <script type="text/javascript">
      jQuery('#carousel-photo-<?php echo $i ?>').Jcrop({
        onChange:   updateCoords,
        onSelect:   updateCoords,
        aspectRatio: 960/200,
				minSize: [ 960, 200 ]
				<?php if(!empty($dimensions) && count($dimensions) > 0): ?>
				, setSelect: [<?php echo $dimensions['x'].", ".$dimensions['y'].", ".($dimensions['x']+$dimensions['w']).", ".($dimensions['y']+$dimensions['h']) ?>]
				<?php endif; ?>
      });
      
      function updateCoords(c) {
        jQuery('#carousel-photo-<?php echo $i ?>-x').val(c.x);
        jQuery('#carousel-photo-<?php echo $i ?>-y').val(c.y);
        jQuery('#carousel-photo-<?php echo $i ?>-w').val(c.w);
        jQuery('#carousel-photo-<?php echo $i ?>-h').val(c.h);
      }
		  </script>
		</td>
	</tr>
  <?php
}


add_action('admin_menu', 'index_carousel_menu');

function index_carousel_menu() {
	add_options_page('Carousel Widget Options', 'Carousel Widget', 'manage_options', 'index-carousel', 'index_carousel_options');
}

function index_carousel_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	?>
	<div class="wrap">
	  <div id="icon-options-general" class="icon32"><br /></div>

		<h2>Index Carousel Photos</h2>
	  <form method="post" action="options.php" id="test-form">
	    <?php settings_fields('index_carousel_options'); ?>
	    <table class="form-table">
    		<?php do_settings_sections('index-carousel'); ?>
    	</table>
    	<p><input type="submit" class="button-primary" value="Save" /></p>
	  </form>
	</div>
<?php
}
*/
  add_action('widgets_init', create_function('', 'return register_widget("AtheneIndexWidgetCarousel");'));

?>