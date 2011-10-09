<?php
/*------------------------------------------------------------------------------
This is run only when this plugin is uninstalled. All cleanup code goes here.

WARNING: uninstalling a plugin fails when developing locally via MAMP et al.
Perhaps related to how WP attempts (and fails) to connect to the local site.
------------------------------------------------------------------------------*/

if ( defined('WP_UNINSTALL_PLUGIN'))
{
	include_once('includes/constants.php');
	include_once('includes/CCTM.php');
	include_once('includes/CCTMFormElement.php');
	
	// If the custom fields modified anything, we need to give them this 
	// opportunity to clean it up.
	$available_custom_field_files = CCTM::get_available_custom_field_types(true);
	foreach ( $available_custom_field_files as $shortname => $file ) {

		include_once($file);
		if ( class_exists(CCTM::classname_prefix.$shortname) )
		{
			$field_type_name = CCTM::classname_prefix.$shortname;
			$FieldObj = new $field_type_name();
			$FieldObj->uninstall();
		}
	}
	
	delete_option( CCTM::db_key );	
	delete_option('custom_content_types_mgr_data'); // legacy pre 0.9.4
	delete_option('custom_content_types_mgr_settings'); // legacy pre 0.9.4
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}
/*EOF*/