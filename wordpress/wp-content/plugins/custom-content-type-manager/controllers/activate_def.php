<?php
/*------------------------------------------------------------------------------
Activate a CCTM .json definition file.
Moves the definition stored in $settings['candidate'] into the active CCTM::$data
------------------------------------------------------------------------------*/
// Validate...
$settings = get_option(self::db_key_settings, array() );
$candidate = self::get_value($settings, 'candidate');
$new_data = self::get_value($candidate, 'payload');

if ( empty($candidate) || empty($new_data)) {
	self::_page_display_error('no_cttm_def_available');
	return;
}

// yes, it *was* export data, now it's being *imported*
$import_data = self::get_value($candidate, 'export_info'); 
$title = self::get_value($import_data, 'title');

// Variables for our template
$page_header = sprintf( __('Import Definition: %s', CCTM_TXTDOMAIN), $title );
$fields   = '';
$action_name = 'custom_content_type_mgr_import_def';
$nonce_name = 'custom_content_type_mgr_import_def_nonce';
$submit   = __('Activate', CCTM_TXTDOMAIN);

// If properly submitted, Proceed with deleting the post type
if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {

	require_once('ImportExport.php');
	
	ImportExport::import_from_preview();
	
	$msg = '<div class="updated"><p>'
		.sprintf( __('The definition %s has been Imported! Welcome to your new site structure!', CCTM_TXTDOMAIN), "<strong><em>$title</em></strong>")
		. '</p></div>';

	self::set_flash($msg);
	
	// We gotta do a JS redirect here to force the page to refresh
	print '
	<script type="text/javascript">
		window.location.replace("?page=cctm_tools");
	</script>';
	return;
	
}

$msg = '<div class="error">
	<img src="'.CCTM_URL.'/images/warning-icon.png" width="50" height="44" style="float:left; padding:10px;"/>
	<p>'
	. sprintf( __('Activating the %s definition will overwrite all your existing custom content type definitions. This does not overwrite any of your content, but this can radically change nearly every other aspect of your site. This is generally only done when you first set up a site.', CCTM_TXTDOMAIN), $title )
	.'</p>'
	. '<p>'.__('Are you sure you want to do this?', CCTM_TXTDOMAIN).'
	<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Import" title="Import a CCTM Definition" target="_blank">
	<img src="'.CCTM_URL.'/images/question-mark.gif" width="16" height="16" />
</a>
	</p></div>';

include 'pages/basic_form.php';
/*EOF*/