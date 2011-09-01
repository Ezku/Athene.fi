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
  
  $output = '
    <script type="text/javascript" charset="utf-8">
      flickrbrowser.api_key = "'. $options['apikey'].'";
      flickrbrowser.user_id = "'.$options['username'].'";
    </script>
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
function flickr_photosets_add_css_js($posts, $force = FALSE){
  global $flickr_gallery_shortcode; // im bad. punish me
	if (!$force && empty($posts)) return $posts;
 
	$shortcode_found = $force; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) {
		if (stripos($post->post_content, '['.$flickr_gallery_shortcode.']') !== FALSE) {
			$shortcode_found = TRUE; // bingo!
			break;
		}
	}
 
	if ($shortcode_found) {
		// enqueue here
		wp_enqueue_style('jquery-fancybox-css', plugins_url('fancybox/jquery.fancybox-1.3.4.css', __FILE__));
		wp_enqueue_style('flickr-photosets-css', plugins_url('flickr.css', __FILE__));
		wp_enqueue_style('flickr-photosets-css', plugins_url('flickr.css', __FILE__));
		wp_enqueue_script('jquery-easing', plugins_url('fancybox/jquery.easing-1.3.pack.js', __FILE__), array('jquery'));
		wp_enqueue_script('jquery-mousewheel', plugins_url('fancybox/jquery.mousewheel-3.0.4.pack.js', __FILE__), array('jquery'));
		wp_enqueue_script('jquery-hashchange', plugins_url('jquery.ba-hashchange.min.js', __FILE__), array('jquery'));
		wp_enqueue_script('jquery-fancybox', plugins_url('fancybox/jquery.fancybox-1.3.4.js', __FILE__), array('jquery', 'jquery-easing', 'jquery-mousewheel'));
		wp_enqueue_script('flickr-browser', plugins_url('flickrbrowser.js', __FILE__), array('jquery-fancybox','jquery-hashchange'));
		wp_enqueue_script('flickr-page', plugins_url('page.js', __FILE__), array('flickr-browser'));
		wp_enqueue_style('flickr-photosets-external-css', get_bloginfo('template_url').'/flickr.css');
	} else {
	}
 
	return $posts;
}

include('FlickrWidget.php'); // newest photos widget to front page
?>