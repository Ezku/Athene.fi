<?php
/*------------------------------------------------------------------------------
This is run only when this plugin is uninstalled. All cleanup code goes here.
Only a single option was used in the wp_options table -- that's where I 
stashed the serialized array that stored all the settings and such. 

WARNING: uninstalling a plugin fails when developing locally via MAMP.
I think it's a WordPress bug (version 3.0.1). Perhaps related to how WP
attempts (and fails) to connect to the local site.
------------------------------------------------------------------------------*/

if ( defined('WP_UNINSTALL_PLUGIN'))
{
	include_once('includes/CCTM.php');
	delete_option( CCTM::db_key );
	delete_option( CCTM::db_key_settings );
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}
/*EOF*/