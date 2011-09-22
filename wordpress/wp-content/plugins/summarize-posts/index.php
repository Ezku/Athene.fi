<?php
/*
Plugin Name: Summarize Posts
Plugin URI:  http://www.fireproofsocks.com/
Description: Summarize posts offers a complete rewrite of the built-in get_posts/query_posts/WP_Query methods for retrieving posts, and its functions are exposed both to your theme files and to your posts via shortcode tags. You can search by taxonomy terms, post title, status, or just about any other criteria you can think of. You can also paginate the results and format them in a flexible and tokenized matter.
Author: Everett Griffiths
Version: 0.7
Author URI: http://www.fireproofsocks.com/

http://www.smashingmagazine.com/2009/02/02/mastering-wordpress-shortcodes

This index.php file runs some tests be
Requires PHP 5.2 or greater.
*/

/*------------------------------------------------------------------------------
CONFIGURATION: 

Define the names of functions and classes used by this plugin so we can test 
for conflicts prior to loading the plugin and message the WP admins if there are
any conflicts.

$function_names_used -- add any function names that this plugin declares in the 
	main namespace (e.g. utility functions or theme functions).

$class_names_used -- add any class names that are declared by this plugin.

Warning: the text-domain for the __() localization functions is hardcoded.
------------------------------------------------------------------------------*/
$function_names_used = array();
$class_names_used = array('SummarizePosts');
$constants_used = array();

$error_items = '';

// No point in localizing this, because we haven't loaded the textdomain yet.
function summarize_posts_manager_cannot_load()
{
	global $error_items;
	print '<div id="summarize-posts-warning" class="error fade"><p><strong>'
	.'The Summarize Posts plugin cannot load correctly!'
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
		$error_items .= sprintf('<li>%1$s: %2$s</li>', __('Function', 'summarize-posts'), $f_name );
	}
}
// Check for conflicting Class names
foreach ($class_names_used as $cl_name )
{
	if ( class_exists($cl_name) )
	{
		/* translators: This refers to a PHP class e.g. class MyClass { ... } */
		$error_items .= sprintf('<li>%1$s: %2$s</li>', __('Class', 'summarize-posts'), $f_name );
	}
}
// Check for conflicting Constants
foreach ($constants_used as $c_name )
{
	if ( defined($c_name) )
	{
		/* translators: This refers to a PHP constant as defined by the define() function */
		$error_items .= sprintf('<li>%1$s: %2$s</li>', __('Constant', 'summarize-posts'), $f_name );
	}
}

// Fire the error, or load the plugin.
if ($error_items)
{
	$error_items = '<ul>'.$error_items.'</ul>';
	add_action('admin_notices', 'summarize_posts_manager_cannot_load');
}
else
{
	// Run Tests
	//	SummarizePostsTests::php_version_gt('5.2.0');
	//	if () {}

	// Load the plugin
	include_once('loader.php');	// Load the plugin
/*
	include_once('includes/GetPostsQuery.php');
	include_once('includes/SummarizePosts.php');
	include_once('tests/SummarizePostsTests.php');
	
	add_action('init', 'SummarizePosts::initialize');
*/
}


/*EOF*/