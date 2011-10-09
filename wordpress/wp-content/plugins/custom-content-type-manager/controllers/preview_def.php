<?php
/*------------------------------------------------------------------------------
@param	string	local name of the uploaded file (stored in wp-content/uploads/cctm/defs)
------------------------------------------------------------------------------*/
require_once('ImportExport.php');
// Validate: check file name
if ( !ImportExport::is_valid_basename($file) ) {
	$msg = '<div class="error"><p>'
		. sprintf( 
			__('Bad filename: %s. No special characters or spaces allowed.', CCTM_TXTDOMAIN)
			, '<strong>'.htmlentities($filename).'</strong>'
			)
		.'</p></div>';
	self::set_flash($msg);
	return self::page_import();
}
$upload_dir = wp_upload_dir();
$dir = $upload_dir['basedir'] .'/'.self::base_storage_dir . '/' . self::def_dir;

$data_raw = file_get_contents($dir.'/'.$file);
$data = json_decode($data_raw, true);

// Check the contents of the array
if ( !ImportExport::is_valid_upload_structure($data) ) {
	$msg = '<div class="error"><p>'
		. sprintf( __('%s contained an incompatible data structure.', CCTM_TXTDOMAIN)
			, '<strong>'.htmlentities($file).'</strong>'
			)
		. '</p></div>';
	self::set_flash($msg);
	return self::page_import();
}

$settings = get_option(CCTM::db_key_settings, array() );
$settings['candidate'] = $data;
update_option(CCTM::db_key_settings, $settings );

return self::page_import();

/*EOF*/