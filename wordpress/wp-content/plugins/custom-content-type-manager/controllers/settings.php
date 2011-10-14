<?php
if ( ! defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('administrator')) exit('Admins only.');

/*------------------------------------------------------------------------------
Settings Page
------------------------------------------------------------------------------*/
$data 				= array();
$data['page_title']	= __('Settings', CCTM_TXTDOMAIN);
$data['help'] 		= 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Settings';
$data['menu'] 		='';
$data['msg']		= self::get_flash();
$data['action_name']  = 'custom_content_type_mgr_settings';
$data['nonce_name']  = 'custom_content_type_mgr_settings';
$data['submit']   = __('Save', CCTM_TXTDOMAIN);
$data['custom_fields_settings_links'] = ''; // <-- optionally kicks in if the Field Element implements the get_settings_page() function

// Add links to any custom field settings here
$data['content'] = ''; 

// If properly submitted, Proceed with deleting the post type
if ( !empty($_POST) && check_admin_referer($data['action_name'], $data['nonce_name']) ) {
	self::$data['settings']['delete_posts'] 			= (int) CCTM::get_value($_POST, 'delete_posts', 0);	
	self::$data['settings']['delete_custom_fields'] 	= (int) CCTM::get_value($_POST, 'delete_custom_fields', 0);
	self::$data['settings']['add_custom_fields'] 		= (int) CCTM::get_value($_POST, 'add_custom_fields', 0);
	self::$data['settings']['update_custom_fields'] 	= (int) CCTM::get_value($_POST, 'update_custom_fields', 0);
	self::$data['settings']['show_custom_fields_menu']	= (int) CCTM::get_value($_POST, 'show_custom_fields_menu', 0);
	self::$data['settings']['show_settings_menu'] 		= (int) CCTM::get_value($_POST, 'show_settings_menu', 0);
	self::$data['settings']['show_foreign_post_types'] 	= (int) CCTM::get_value($_POST, 'show_foreign_post_types', 0);
	self::$data['settings']['cache_directory_scans'] 	= (int) CCTM::get_value($_POST, 'cache_directory_scans', 0);
	update_option( self::db_key, self::$data );

	$data['msg'] = '<div class="updated"><p>'
		. __('Settings have been updated.', CCTM_TXTDOMAIN )
		.'</p></div>';
	self::set_flash($data['msg']);
	print '<script type="text/javascript">window.location.replace("?page=cctm_settings");</script>';
	return;
}

// Use Defaults by default...
$data['settings'] = CCTM::$default_settings;

// list all checkboxes here
$checkboxes = array(
	'delete_posts' 
	, 'delete_custom_fields'
	, 'add_custom_fields'
	, 'update_custom_fields'
 	, 'show_custom_fields_menu'
 	, 'show_settings_menu'
 	, 'show_foreign_post_types'
 	, 'cache_directory_scans'
);

// this only works for checkboxes...
foreach ( $checkboxes as $k) {
	if (self::get_setting($k)) {
		$data['settings'][$k] = ' checked="checked"';
	}
}

// Load up any settings pages for custom fields

$element_files = CCTM::get_available_custom_field_types();
$flag = false;
foreach ( $element_files as $shortname => $file ) {
	include_once($file);

	if ( class_exists(CCTM::classname_prefix.$shortname) )
	{
		$d = array();
		$field_type_name = CCTM::classname_prefix.$shortname;
		$FieldObj = new $field_type_name();
		
		if ($FieldObj->get_settings_page() ) {
			$flag = true;
			$data['custom_fields_settings_links'] .= sprintf(
				'<li><strong>%s</strong>: %s (<a href="?page=cctm_settings&a=settings_cf&type=%s">%s</a>)'
				, $FieldObj->get_name()
				, $FieldObj->get_description()
				, $shortname
				, __('Edit Settings', CCTM_TXTDOMAIN)
			);
			
		}
	}
}
// We gots some!
if ($flag) {
	$data['custom_fields_settings_links'] = '<h3>'.__('Custom Fields', CCTM_TXTDOMAIN).'</h3>
		<ul>'. $data['custom_fields_settings_links'] . '</ul>';
}

$data['content'] .= CCTM::load_view('settings.php', $data);
print CCTM::load_view('templates/default.php', $data);

/*EOF*/