<?php
/*------------------------------------------------------------------------------
Independent controller that displays the contents of a CCTM definition file

Output is the HTML required to display and format the def file.
This needed to live in a separate file because I needed to completely control
the entire request: if it were handled by WP, headers() would be sent.
------------------------------------------------------------------------------*/
@require_once( realpath('../../../../').'/wp-load.php' );
//include_once('../includes/constants.php');
//include_once(CCTM_PATH.'/includes/CCTM.php');
include_once(CCTM_PATH.'/includes/ImportExport.php');

if ( !current_user_can('manage_options') )
{
	wp_die(__('You do not have permission to download CCTM definitions.'));
}

// Check nonces
$nonce = CCTM::get_value($_GET, '_cctm_nonce');
if (! wp_verify_nonce($nonce, 'cctm_preview_def') ) {
	printf( '<div class="error"><p>%s</p></div>'
		, __('Invalid request.', CCTM_TXTDOMAIN)
	);
	exit;
}

// Make sure a file was specified
$filename = CCTM::get_value($_GET,'file');
if (empty($filename)) {
	printf( '<div class="error"><p>%s</p></div>'
		, __('Definition file not specified.', CCTM_TXTDOMAIN)
	);
	exit;
}

// Make sure the filename is legit
if (!ImportExport::is_valid_basename($filename)) {
	printf( '<div class="error"><p>%s</p></div>'
		, __('Invalid filename: the definition filename should not contain spaces and should use an extension of <code>.cctm.json</code>.', CCTM_TXTDOMAIN)
	);
	exit;
}

// Load up this thing... errors will be thrown
$upload_dir = wp_upload_dir();
$dir = $upload_dir['basedir'] .'/'.CCTM::base_storage_dir . '/' . CCTM::def_dir .'/';

$data = ImportExport::load_def_file($dir.$filename);

// Bail if there were errors
if (!empty(CCTM::$errors)) {
	print CCTM::format_errors();
	exit;
}

$data['filename'] = $filename;

print CCTM::load_view('preview_def.php', $data);
exit;

/*EOF*/