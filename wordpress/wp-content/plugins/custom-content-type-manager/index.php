<?php
/*------------------------------------------------------------------------------
Plugin Name: Custom Content Type Manager
Description: Allows users to create custom content types (also known as post types) and standardize custom fields for each content type, including dropdowns, checkboxes, and images. This gives WordPress CMS functionality making it easier to use WP for eCommerce or content-driven sites.
Author: Everett Griffiths
Version: 0.9.3.3
Author URI: http://www.fireproofsocks.com/
Plugin URI: http://code.google.com/p/wordpress-custom-content-type-manager/

See also Related plugins listed here:
http://code.google.com/p/wordpress-custom-content-type-manager/wiki/SeeAlso
------------------------------------------------------------------------------*/

/*------------------------------------------------------------------------------
CONFIGURATION (for the developer): 

Define the names of functions and classes used by this plugin so we can test 
for conflicts prior to loading the plugin and message the WP admins if there are
any conflicts.

$function_names_used -- add any functions declared by this plugin in the 
	main namespace (e.g. utility functions or theme functions).

$class_names_used -- add any class names that are declared by this plugin.

Warning: the text-domain for the __() localization functions is hardcoded.
------------------------------------------------------------------------------*/
$function_names_used = array('get_custom_field','get_custom_field_meta','get_custom_field_def'
	,'get_all_fields_of_type','get_post_complete','get_posts_sharing_custom_field_value'
	,'get_relation','get_unique_values_this_custom_field','print_custom_field','print_custom_field_meta'
	,'uninstall_cctm');
$class_names_used = array('CCTM', 'ImportExport'
	,'StandardizedCustomFields','CCTMtests','PostSelector');
// Not class constants: constants declared via define():
$constants_used = array('CCTM_PATH','CCTM_URL');

// Used to store errors
$error_items = '';

// No point in localizing this, because we haven't loaded the textdomain yet.
function custom_content_type_manager_cannot_load()
{
	global $error_items;
	print '<div id="custom-post-type-manager-warning" class="error fade"><p><strong>'
	.'The Custom Post Type Manager plugin cannot load correctly!'
	.'</strong> '
	.'Another plugin has declared conflicting class, function, or constant names:'
	.'<ul style="margin-left:30px;">'.$error_items.'</ul>'
	.'</p>'
	.'<p>You must deactivate the plugins that are using these conflicting names.</p>'
	.'</div>';
	
}

/*------------------------------------------------------------------------------
The following code tests whether or not this plugin can be safely loaded.
If there are no conflicts, the loader.php is included and the plugin is loaded,
otherwise, an error is displayed in the manager.
------------------------------------------------------------------------------*/
// Check for conflicting function names
foreach ($function_names_used as $f_name )
{
	if ( function_exists($f_name) )
	{
		/* translators: This refers to a PHP function e.g. my_function() { ... } */
		$error_items .= sprintf('<li>%1$s: %2$s</li>', __('Function', 'custom-content-type-mgr'), $f_name );
	}
}
// Check for conflicting Class names
foreach ($class_names_used as $cl_name )
{
	if ( class_exists($cl_name) )
	{
		/* translators: This refers to a PHP class e.g. class MyClass { ... } */
		$error_items .= sprintf('<li>%1$s: %2$s</li>', __('Class', 'custom-content-type-mgr'), $f_name );
	}
}
// Check for conflicting Constants
foreach ($constants_used as $c_name )
{
	if ( defined($c_name) )
	{
		/* translators: This refers to a PHP constant as defined by the define() function */
		$error_items .= sprintf('<li>%1$s: %2$s</li>', __('Constant', 'custom-content-type-mgr'), $f_name );
	}
}

// Fire the error, or load the plugin.
if ($error_items)
{
	$error_items = '<ul>'.$error_items.'</ul>';
	add_action('admin_notices', 'custom_content_type_manager_cannot_load');
}
// CLEARED FOR LAUNCH!!! ---> Load the plugin
else
{
	include_once('loader.php');
}

/*EOF*/