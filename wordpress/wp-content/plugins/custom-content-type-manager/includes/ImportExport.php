<?php
/**
 * Used to handle various tasks involved with the importing and exporting of CCTM definition data.
 *
 *
 * @package
 */


class ImportExport {

	/**
	 * API for dedicated CCTM pastebin user.
	 */
	const pastebin_dev_key = '';
	const pastebin_endpoint = '';
	const extension = '.cctm.json';
	
	
	/**
	 * We can't just compare them because the menu_icon bits will be different: the candidate
	 * will have a relative URL, the live one will have an absolute URL.
	 *
	 * @param	mixed	CCTM definition data structure
	 * @param	mixed	CCTM definition data structure
	 * @return	boolean	true if they are equal, false if not	 
	 */
	public static function defs_are_equal($def1,$def2) {
		if (is_array($def1) ) {
			foreach ( $def1 as $post_type => $def ) {
				if ( isset($def1[$post_type]['menu_icon']) && !empty($def1[$post_type]['menu_icon']) ) {
					$def1[$post_type]['menu_icon'] = self::make_img_path_rel($def1[$post_type]['menu_icon']);
				}
			}
		}
		if (is_array($def2) ) {
			foreach ( $def2 as $post_type => $def ) {
				if ( isset($def2[$post_type]['menu_icon']) && !empty($def2[$post_type]['menu_icon']) ) {
					$def2[$post_type]['menu_icon'] = self::make_img_path_rel($def2[$post_type]['menu_icon']);
				}
			}
		}
		
		if ( $def1 == $def2 ) {
			return true;
		}		
		else
		{
			return false;
		}
	}
	
	/**
	 * Initiates a download: prints headers with payload
	 * or an error.
	 */
	public static function export_to_desktop() {

		// The nonce here must line up with the nonce defined in
		// includes/CCTM.php ~line 2300 in the page_export() function
		$nonce = '';
		if ( isset($_GET['_wpnonce']) ) {
			$nonce = $_GET['_wpnonce'];
		}
		if (! wp_verify_nonce($nonce, 'cctm_download_definition') ) {
			die( __('Invalid request.', CCTM_TXTDOMAIN ) );
		}
		
		
		// Load up the settings (this includes the 'export_info')
		$save_me = get_option( CCTM::db_key_settings, array() );
		// and tack on additional tracking stuff
		// consider user data: http://codex.wordpress.org/get_currentuserinfo
		$save_me['export_info']['_timestamp_export'] = time();
		$save_me['export_info']['_source_site'] = site_url();
		$save_me['export_info']['_charset'] = get_bloginfo('charset');
		$save_me['export_info']['_language'] = get_bloginfo('language');
		$save_me['export_info']['_wp_version'] = get_bloginfo('version');
		$save_me['export_info']['_cctm_version'] = CCTM::version;
		// And finally, the main event				
		$payload = get_option( CCTM::db_key, array() );
		
		// 1. Filter out any absolute paths used for menu icons.
		// 2. Zero out any default values for referential fields 
		// See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=66
		if (is_array($payload) ) {
			foreach ( $payload as $post_type => $def ) {
				if ( isset($payload[$post_type]['menu_icon']) && !empty($payload[$post_type]['menu_icon']) ) {
					$payload[$post_type]['menu_icon'] = self::make_img_path_rel($payload[$post_type]['menu_icon']);
				}
				if ( isset($def['custom_fields']) && is_array($def['custom_fields']) ) {
					foreach ( $def['custom_fields'] as $field => $field_def ) {
						if ( in_array($field_def['type'], array('image','relation','media') ) ) {
							$payload[$post_type]['custom_fields'][$field]['default_value'] = '';
						}
					}
				}
			}
		}
		
		
		
		// This cleans up a couple things that crept into $_POST in earlier versions
		unset($payload['custom_content_type_mgr_create_new_content_type_nonce']);
		unset($payload['custom_content_type_mgr_edit_content_type_nonce']);
		
		$save_me['payload'] = $payload;
		
		// download-friendly name of the file
		$title = 'definition'; // default --> .cctm.json is appended
		if ( !empty($save_me['export_info']['title']) ) {
			$title = $save_me['export_info']['title'];
			$title = strtolower($title);
			$title = preg_replace('/\s+/', '_', $title); 
			$title = preg_replace('/[^a-z_\-0-9]/i', '', $title); 
		}
		
		if ( $download = json_encode($save_me) ) {
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=$title.cctm.json");
			header("Content-length: ".(string) mb_strlen($download, '8bit') );
			header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
			header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			print $download;
		}
		else {
			print __('There was a problem exporting your CCTM definition.', CCTM_TXTDOMAIN);
		}
	}



	/**
	 *
	 */
	public static function export_to_local_webserver() {

	}



	/**
	 * see http://pastebin.com/api
	 */
	public static function export_to_pastebin() {

	}


	/**
	 * Used to check the names of uploaded files -- also passed in URLs
	 * Basename only! Not full path!
	 * 
	 * @param	string	file basename, e.g. 'my_def.cctm.json'
	 * @return	boolean	false if it's a bad filename, true if it's legit.
	 */
	public static function is_valid_basename($basename) {
		if ( empty($basename) ) {
			return false;
		}
		//  Must have the .cctm.json extension
		if ( !preg_match('/'.self::extension.'$/i', $basename) ) {
			false;
		}
		$cnt;
		$basename = str_replace(self::extension, '', $basename, $cnt);
		if ( preg_match('/[^a-z_\-0-9]/i', $basename) ) {
			return false;
		}
		// I guess the filename is legit.
		return true;
	}

	
	/**
	 * Given an array, we make sure it's a valid for use as a CCTM definition.
	 *
	 * @param	array		mixed data structure
	 * @return	boolean 	true if the structure is valid
	 */
	public static function is_valid_def_structure($data) {
		if ( empty($data) ) {
			return true; // empty defs are allowed.
		}
		if ( !is_array($data) ) {
			return false;
		}
		foreach ( $data as $post_type => $def ) {
			if ( is_array($post_type) ) {
				return false;
			}
			if ( !is_array($def) ) {
				return false;
			}
			if ( is_numeric($post_type) ) {
				return false;
			}
			//foreach ($def as $k => $v) {
			// 	todo	
			//}
		}
		// If we make it here, it's a thumbs-up
		return true;
	}
	
	/**
	 * Given an array, we make sure it's a valid import package
	 *
	 * @param	array		mixed data structure
	 * @return	boolean 	true if the structure is valid
	 */
	public static function is_valid_upload_structure($data) {
		if ( !is_array($data) ) {
			return false;
		}
		elseif ( !isset($data['export_info'])) {
			return false;
		}
		elseif ( !isset($data['export_info']['_timestamp_export'])) {
			return false;
		}		
		elseif ( !isset($data['export_info']['_source_site'])) {
			return false;
		}
		elseif ( !isset($data['export_info']['_charset'])) {
			return false;
		}
		elseif ( !isset($data['export_info']['_language'])) {
			return false;
		}
		elseif ( !isset($data['export_info']['_wp_version'])) {
			return false;
		}
		elseif ( !isset($data['export_info']['_cctm_version'])) {
			return false;
		}
		elseif ( !isset($data['payload'])) {
			return false;
		}

		return self::is_valid_def_structure($data['payload']);
	}	
	
	/**
	 *
	 */
	public static function import_from_desktop() {

	}



	/**
	 * The preview data object is stored nextdoor in a neighboring option:
	 
	 */
	public static function import_from_preview() {
	
		$settings = get_option(CCTM::db_key_settings, array() );
		$candidate = CCTM::_get_value($settings, 'candidate');
		$new_data = CCTM::_get_value($candidate, 'payload');

		// Clean up icon URLs: make them absolute again. See the ImportExport::export_to_desktop function
		// and issue 64:http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=64
		foreach ( $new_data as $post_type => $def ) {
			if ( isset($new_data[$post_type]['menu_icon']) && !empty($new_data[$post_type]['menu_icon']) ) {
				$new_data[$post_type]['menu_icon'] = self::make_img_path_abs($new_data[$post_type]['menu_icon']);
			}
		}
		update_option( CCTM::db_key, $new_data );
	}


	/**
	 *
	 */
	public static function import_from_pastebin() {

	}

	
	/**
	 * Make any relative image paths absolute on the new server. Image paths
	 * including a full url (e.g. "http://something...") will be ignored
	 * and returned unaltered.
	 *
	 * See make_img_path_rel() for more info about the problem that 
	 * this is solving.
	 *
	 * @param	string	URL representing an image.
	 * @param	string	Absolute URL
	 */
	public static function make_img_path_abs($src) {
		$parts = parse_url($src);
		if (isset($parts['host']) ) {
			return $src; // <-- path is already absolute
		}
		elseif ( !isset($parts['path'])) {
			return $src; // Just in case the parse_url() fails
		}
		else {
			// Here we manage the potential leading slash...
			$parts['path'] = preg_replace('|^/?|','', $parts['path']);
			return site_url() .'/'. $parts['path'];
		}
	}
	
	/**
	 * When storing image paths, esp. for the custom post type icons, the full URL
	 * is typically used, but if we are going to import and export definitions, 
	 * that will break the definitions that use custom icons.  The solution is 
	 * to strip the site url from the image path prior to export, then append it 
	 * prior to import. This should allow images hosted on another domain to be
	 * used without being affected.
	 *
	 * This function should only act on images hosted locally on the same domain
	 * listed by the site_url();
	 *
	 * @param	string	$src	a full path to an image, e.g."http://x.com/my.jpg"
	 * @return	string	a relative path to that image, e.g. "my.jpg"
	 */
	public static function make_img_path_rel($src) {
		return str_replace(site_url(), '', $src);
	}

	/**
	 * Take a data structure and return true or false as to whether or not it's
	 * in the correct format for a CCTM definition.
	 */
	public static function validate_data_structure($data) {
		// move portions from CCTM::_sanitize_import_params
	}

}


/*EOF*/