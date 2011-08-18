<?php
/**
 * @package FlickrPhotoSets
 * @version 1.0
 */
/*
Plugin Name: Flickr Photosets
Plugin URI: http://pkroger.org
Description: Shows Flickr photosets from specified user chronologically.
Author: Pyry Kröger
Version: 1.0
Author URI: http://pkroger.org/
*/

$flickr_gallery_shortcode = "flickr_photosets";

function flickr_photosets_show( $attrs ) {
	extract( shortcode_atts( array(
		'foo' => 'something',
		'bar' => 'something else',
	), $atts ) );

  $options = get_option('flickr_photosets_options');
  
  /* <script type="text/javascript" src="jquery-1.5.2.min.js"></script>
  <script type="text/javascript" src="./fancybox/jquery.fancybox-1.3.4.pack.js"></script>
  <script type="text/javascript" src="flickrbrowser.js"></script>
  <script type="text/javascript" charset="utf-8">
    flickrbrowser.api_key: "'. $options['apikey'].'";
    flickrbrowser.user_id: "'.$options['username'].'";
  </script>
  <link rel="stylesheet" type="text/css" href="./fancybox/jquery.fancybox-1.3.4.css" media="screen" /> */
  $output = '
    <script type="text/javascript" charset="utf-8">
      flickrbrowser.api_key = "'. $options['apikey'].'";
      flickrbrowser.user_id = "'.$options['username'].'";
    </script>
    <style type="text/css" media="screen">
      #flickrphotos {
      }
      .photoset {
        background-color: #6c6;
        clear: both;
      }
      .photosettitle:hover {
        background-color: #7d7;
        cursor: pointer;
      }
      .photoset.active {
        background-color: #7d7;
      }
      .photo {
        margin: 5px;
        float: left;
      }
      #spinner {
        position: fixed;
        top: 50%;
        left: 50%;
        width: 100px;
        height: 100px;
        margin: -50px -50px 0 0;
      }

      .hide {
        display: none;
      }
    </style>
    <div id="flickrphotos">
    </div>
    <div class="flickrlink">
      <p>Kaikki kuvat löytyvät myös <a href="http://www.flickr.com/photos/'.$options['username'].'/sets/">Flickr-palvelusta</a>.</p>
    </div>
    <div id="spinner">
      loading....
    </div>
  ';
	return $output;
}
add_shortcode( $flickr_gallery_shortcode, 'flickr_photosets_show' );

/*add_action('wp_enqueue_scripts', 'flickr_add_scripts' );

function flickr_add_scripts() {
  wp_enqueue_script( 'fancybox_jquery_mousewheel', WP_PLUGIN_URL . '/FlickrPhotosets/fancybox/jquery.mousewheel-3.0.4.pack.js', array( 'jquery' ), null, false );
}*/

// ------------------------------------------------------
// Options for Flickr Photosets
// ------------------------------------------------------

add_action('admin_init', 'flickr_photosets_options_init' );

function flickr_photosets_options_init() {
  register_setting( 'flickr_photosets_options', 'flickr_photosets_options' );
  add_settings_section('flickr_photosets_main', 'Main Settings', 'plugin_section_text', 'flickr-photosets');
  add_settings_field('flick_photosets_username', '', 'plugin_setting_string', 'flickr-photosets', 'flickr_photosets_main');
}

function plugin_section_text() {
}

function plugin_setting_string() {
  $options = get_option('flickr_photosets_options'); ?>
  <tr>
		<th scope="row">Flickr User ID</th>
		<td>
		  <input id='flickr_photosets_username' name='flickr_photosets_options[username]' size='40' type='text' value='<?php echo $options['username'] ?>' /><br />
			<span class="description">Flickr user ID (not username) for the user whose photosets you want to display.</span>
		</td>
	</tr>
	<tr>
		<th scope="row">Flickr API Key</th>
		<td>
		  <input id='flickr_photosets_apikey' name='flickr_photosets_options[apikey]' size='40' type='text' value='<?php echo $options['apikey'] ?>' /><br />
			<span class="description">Flickr API key. You need to get one from Flickr developer site.</span>
		</td>
	</tr>
	<?php
}


add_action('admin_menu', 'flickr_photosets_menu');

function flickr_photosets_menu() {
	add_options_page('Flickr Photosets Options', 'Flickr Photosets', 'manage_options', 'flickr-photosets', 'flickr_photosets_options');
}

function flickr_photosets_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	?>
	<div class="wrap">
	  <div id="icon-options-general" class="icon32"><br /></div>

		<h2>Flickr Photosets Options</h2>
	  <form method="post" action="options.php" id="test-form">
	    <?php settings_fields('flickr_photosets_options'); ?>
	    <table class="form-table">
    		<?php do_settings_sections('flickr-photosets'); ?>
    	</table>
    	<p><input type="submit" class="button-primary" value="Save" /></p>
	  </form>
	</div>
<?php
}

// add js and css for the gallery if needed
// see: http://beerpla.net/2010/01/13/wordpress-plugin-development-how-to-include-css-and-javascript-conditionally-and-only-when-needed-by-the-posts/
add_filter('the_posts', 'flickr_photosets_add_css_js'); // the_posts gets triggered before wp_head
function flickr_photosets_add_css_js($posts){
  global $flickr_gallery_shortcode; // im bad. punish me
	if (empty($posts)) return $posts;
 
	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) {
		if (stripos($post->post_content, '['.$flickr_gallery_shortcode.']') !== FALSE) {
			$shortcode_found = true; // bingo!
			break;
		}
	}
 
	if ($shortcode_found) {
		// enqueue here
		wp_enqueue_style('jquery-fancybox-css', plugins_url('fancybox/jquery.fancybox-1.3.4.css', __FILE__));
		wp_enqueue_script('jquery-easing', plugins_url('fancybox/jquery.easing-1.3.pack.js', __FILE__), array('jquery'));
		wp_enqueue_script('jquery-mousewheel', plugins_url('fancybox/jquery.mousewheel-3.0.4.pack.js', __FILE__), array('jquery'));
		wp_enqueue_script('jquery-fancybox', plugins_url('fancybox/jquery.fancybox-1.3.4.pack.js', __FILE__), array('jquery', 'jquery-easing', 'jquery-mousewheel'));
		wp_enqueue_script('flickr-browser', plugins_url('flickrbrowser.js', __FILE__), array('jquery-fancybox'));
	} else {
	}
 
	return $posts;
}
?>