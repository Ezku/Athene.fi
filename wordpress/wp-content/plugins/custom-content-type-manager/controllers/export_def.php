<?php
/*------------------------------------------------------------------------------
Export a content type definition to a .json file
------------------------------------------------------------------------------*/
$data 				= array();
$data['page_title']	= __('Export Definition', CCTM_TXTDOMAIN);
$data['help'] = 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Export';
$data['menu'] 		= $data['menu'] = sprintf('<a href="?page=cctm_tools&a=tools" title="%s" class="button">%s</a>', __('Back'), __('Back')) . ' ' .
	sprintf('<a href="?page=cctm_tools&a=import_def" title="%s" class="button">%s</a>',__('Import'), __('Import'));;
$data['msg']		= '';
$data['action_name']  = 'custom_content_type_mgr_export';
$data['nonce_name']  = 'custom_content_type_mgr_export';
$data['submit']   = __('Save', CCTM_TXTDOMAIN);
$data['content'] = '';


// If properly submitted, Proceed with saving settings and exporting def.
if ( !empty($_POST) && check_admin_referer($data['action_name'], $data['nonce_name']) ) {
	
	require_once(CCTM_PATH . '/includes/ImportExport.php');
	
	$sanitized = ImportExport::sanitize_export_params($_POST, $data['nonce_name']);
	
	// Any errors?
	if ( !empty(CCTM::$errors) ) {
		$data['msg'] = CCTM::format_errors();
	}
	// Download to desktop
	elseif ($_POST['export_type'] == 'download') {
		$nonce = wp_create_nonce('cctm_download_definition');
		
		$data['msg'] = sprintf('<div class="updated"><p>%s</p></div>'
			, sprintf(__('Your Custom Content Type definition %s should begin downloading shortly. If the download does not begin, %s', CCTM_TXTDOMAIN)
			, '<strong>'.ImportExport::get_download_title($sanitized['title']).'</strong>'
			, '<a href="'.CCTM_URL.'/controllers-standalone/download.php?_wpnonce='.$nonce.'">click here</a>'));

		// Save the options: anything that's in the form is considered a valid "info" key.
		self::$data['export_info'] = $sanitized;
		update_option(self::db_key, self::$data);

		// Fire off a request to download the file:
		$data['msg'] .= sprintf('
			<script type="text/javascript">
				jQuery(document).ready(function() {
					window.location.replace("%s?_wpnonce=%s");
				});
			</script>'
			, CCTM_URL.'/controllers-standalone/download.php'
			, $nonce );
	}
	elseif($_POST['export_type'] == 'to_library') {
		// Save the options: anything that's in the form is considered a valid "info" key.
		self::$data['export_info'] = $sanitized;
		update_option(self::db_key, self::$data);
		
		if( ImportExport::export_to_local_webserver() ) {
			$data['msg'] = sprintf('<div class="updated"><p>%s</p></div>'
					, __('Your Custom Content Type definition has been saved to your library. <a href="?page=cctm_tools&a=import_def">Click here</a> to view your library.', CCTM_TXTDOMAIN)
				);
		}
		else {
			$data['msg'] = CCTM::format_errors();
		}
	}
}

// Populate the values
$data['title'] = CCTM::get_value(self::$data['export_info'], 'title');
$data['author'] = CCTM::get_value(self::$data['export_info'], 'author');
$data['url'] = CCTM::get_value(self::$data['export_info'], 'url');
$data['description'] = CCTM::get_value(self::$data['export_info'], 'description');
$data['template_url'] = CCTM::get_value(self::$data['export_info'], 'template_url');

$data['content'] = CCTM::load_view('export.php', $data);
print CCTM::load_view('templates/default.php', $data);

/*EOF*/