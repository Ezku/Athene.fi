<?php 
if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed');
/**
* This file (loader.php) is called only when we've checked for any potential 
* conflicts with function names, class names, or constant names. With so many WP 
* plugins available and so many potential conflicts out there, I've attempted to 
* avoid the headaches caused from name pollution as much as possible.
*/

/*
I manually encoded the characters to Special HTML Characters, and created a field containing those characters. Next I headed to /includes/elements/multiselect.php and on line 145 and changed htmlspecialchars($opt) to htmlspecialchars_decode($opt). On my template file I uncluded the field with the following code: $g = get_custom_field('genre', ', '); echo htmlspecialchars_decode($g); and it worked.

I know it's not a very good idea to encode to HTML Characters, but it's the only way to avoid problems with UTF-8 encoding. I believe it's possible to encode the field characters to HTML Characters before they go into database (though I couldn't find where it's done). The problem lies in including the field on the template file, because  get/print_custom_field is not a part of the plugin, but I'm sure it's possible to work this around.

Run tests only upon activation
http://codex.wordpress.org/Function_Reference/register_activation_hook
*/
// Always Required Files
include_once('includes/constants.php'); // needed before anything else
include_once('includes/CCTM.php');

// Admin-only files
if( is_admin()) {
	include_once('includes/StandardizedCustomFields.php');
	include_once('tests/CCTMtests.php');
	
	// Run Tests (add new tests to the CCCTMtests class as req'd)
	// If there are errors, CCTMtests::$errors will get populated.
	CCTMtests::wp_version_gt(CCTM::wp_req_ver);
	CCTMtests::php_version_gt(CCTM::php_req_ver);
	CCTMtests::mysql_version_gt(CCTM::mysql_req_ver);
	CCTMtests::incompatible_plugins( array('Magic Fields','Custom Post Type UI','CMS Press') );
}
// Front-end Only files
else {
	include_once('includes/functions.php');
}

// Get admin ready, print any CCTMtests::$errors in the admin dashboard
add_action( 'admin_notices', 'CCTM::print_notices');

if ( empty(CCTM::$errors) )
{
	// Load up the CCTM data from wp_options, populates CCTM::$data
	CCTM::load_data();
	
	// Run any updates for this version.
	add_action('init', 'CCTM::check_for_updates', 0 );	
	
	// Generate admin menu, bootstrap CSS/JS
	add_action('admin_init', 'CCTM::admin_init');	
	
	// Register any custom post-types (a.k.a. content types)
	add_action('init', 'CCTM::register_custom_post_types', 11 );
	
	// Create custom plugin settings menu
	add_action('admin_menu', 'CCTM::create_admin_menu');
	add_filter('plugin_action_links', 'CCTM::add_plugin_settings_link', 10, 2 );
	
	
	// Standardize Fields
	add_action('do_meta_boxes', 'StandardizedCustomFields::remove_default_custom_fields', 10, 3 );
	add_action('admin_menu', 'StandardizedCustomFields::create_meta_box' );
	add_action('save_post', 'StandardizedCustomFields::save_custom_fields', 1, 2 ); //! TODO: register this action conditionally
	
	// Customize the page-attribute box for custom page hierarchies
	add_filter('wp_dropdown_pages','StandardizedCustomFields::customized_hierarchical_post_types', 100, 1);
	
	// Enable archives for custom post types
	add_filter('getarchives_where', 'CCTM::get_archives_where_filter' , 10 , 2);
	add_filter('request', 'CCTM::request_filter');
	
	// Forces custom post types to sort correctly
	add_filter('posts_orderby', 'CCTM::order_posts');
	
	// Forces front-end searches to return results for all registered post_types
	add_filter('pre_get_posts','CCTM::search_filter');
	
	// FUTURE: Highlght which themes are CCTM-compatible (if any)
	// add_filter('theme_action_links', 'CCTM::highlight_cctm_compatible_themes');
	add_action('admin_notices', 'CCTM::print_warnings');
	
	// Used to modify the large post icon
	add_action('in_admin_header','StandardizedCustomFields::print_admin_header');
	
	// Modifies the "Right Now" widget
	add_action( 'right_now_content_table_end' , 'CCTM::right_now_widget' );
}

/*EOF*/