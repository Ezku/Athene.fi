<?php
/*------------------------------------------------------------------------------
Standalone controller to cough up a download.
------------------------------------------------------------------------------*/
require_once( realpath('../../../../').'/wp-load.php' );

//include_once('../includes/constants.php');
//include_once(CCTM_PATH.'/includes/CCTM.php');
include_once(CCTM_PATH.'/includes/ImportExport.php');

if ( !current_user_can('manage_options') ) {
	wp_die(__('You do not have permission to download CCTM definitions.'));
}

// The nonce here must line up with the nonce defined in
// controllers/export_def.php ~line 36
$nonce = '';
if ( isset($_GET['_wpnonce']) ) {
	$nonce = $_GET['_wpnonce'];
}
if (! wp_verify_nonce($nonce, 'cctm_download_definition') ) {
	die( __('Invalid request.', CCTM_TXTDOMAIN ) );
}

ImportExport::export_to_desktop();

exit;

/*EOF*/