<?php
/*------------------------------------------------------------------------------
Independent controller that slurps up a file from the library and stores it in
the $settings['candidate'] option array (i.e. in the data structure stored 
in wp_options identified by the key CCTM::db_key_settings).

This needed to live in a separate file because I needed to completely control
the entire request: if it were handled by WP, headers() would be sent.
------------------------------------------------------------------------------*/
require_once( realpath('../../../').'/wp-config.php' );
$this_dir = dirname(__FILE__);
include_once($this_dir.'/includes/CCTM.php');
include_once($this_dir.'/includes/ImportExport.php');

if ( !current_user_can('manage_options') )
{
	wp_die(__('You do not have permission to download CCTM definitions.'));
}

ImportExport::export_to_desktop();

exit;

/*EOF*/