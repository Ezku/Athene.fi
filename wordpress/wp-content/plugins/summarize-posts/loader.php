<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed');
/*------------------------------------------------------------------------------
This file (loader.php) is called only when we've checked for any potential 
conflicts with function names, class names, or constant names. With so many WP 
plugins available and so many potential conflicts out there, I've attempted to 
avoid the headaches caused from name pollution as much as possible.
------------------------------------------------------------------------------*/

// Required Files	
include_once('includes/GetPostsQuery.php');
include_once('includes/SummarizePosts.php');
include_once('tests/SummarizePostsTests.php');


// Run Tests (add new tests to the CCCTMtests class as req'd)
// If there are errors, CCTMtests::$errors will get populated.
SummarizePosts::$options = get_option( SummarizePosts::db_key, SummarizePosts::$default_options );

SummarizePostsTests::wp_version_gt(SummarizePosts::wp_req_ver);
SummarizePostsTests::php_version_gt(SummarizePosts::php_req_ver);
SummarizePostsTests::mysql_version_gt(SummarizePosts::mysql_req_ver);
SummarizePostsTests::check_and_set_group_concat_max_len(SummarizePosts::$options['group_concat_max_len']);

// Get admin ready, print any SummarizePostsTests::$errors in the admin dashboard
add_action( 'admin_notices', 'SummarizePosts::print_notices');


if ( empty(SummarizePostsTests::$errors) )
{
	add_action('init', 'SummarizePosts::initialize');
	add_action('admin_menu', 'SummarizePosts::create_admin_menu');
}

/*EOF*/