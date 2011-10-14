<?php
if ( ! defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('administrator')) exit('Admins only.');

/*------------------------------------------------------------------------------
Duplicate an existing custom field of the type specified by $field_type.  

$field_type is set in the $_GET array
------------------------------------------------------------------------------*/

// Make sure the field exists
if (!array_key_exists($field_name, self::$data['custom_field_defs'])) {
	$msg_id = 'invalid_field_name';
	include(CCTM_PATH.'/controllers/error.php');
	return;
}


// Page variables
$data = array();
$data['page_title'] = sprintf(__('Duplicate Custom Field: %s', CCTM_TXTDOMAIN), $field_name );
$data['help'] = 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/SupportedCustomFields';
$data['msg'] = '';
$data['menu'] = sprintf('<a href="'.get_admin_url(false,'admin.php').'?page=cctm_fields&a=list_custom_fields" title="%s" class="button">%s</a>', __('Cancel'), __('Cancel'));
$data['submit'] = __('Save', CCTM_TXTDOMAIN);
$data['action_name']  = 'custom_content_type_mgr_edit_custom_field';
$data['nonce_name']  = 'custom_content_type_mgr_edit_custom_field_nonce';

$nonce = self::get_value($_GET, '_wpnonce');
if (! wp_verify_nonce($nonce, 'cctm_edit_field') ) {
	die( __('Invalid request.', CCTM_TXTDOMAIN ) );
}
		
	
$field_type = self::$data['custom_field_defs'][$field_name]['type'];
$field_data = self::$data['custom_field_defs'][$field_name]; // Data object we will save

self::include_form_element_class($field_type); // This will die on errors
$field_type_name = self::classname_prefix.$field_type;
$FieldObj = new $field_type_name(); // Instantiate the field element

$field_data['name'] = $field_data['name'] . '_copy';
$field_data['label'] = $field_data['label'] . ' Copy';

$FieldObj->props 	= $field_data;  


// Save if submitted...
if ( !empty($_POST) && check_admin_referer($data['action_name'], $data['nonce_name']) ) {
	// A little cleanup before we handoff to save_definition_filter
	unset($_POST[ $data['nonce_name'] ]);
	unset($_POST['_wp_http_referer']);

	// Validate and sanitize any submitted data
	$field_data 		= $FieldObj->save_definition_filter($_POST, $post_type);
	$field_data['type'] = $field_type; // same effect as adding a hidden field
	
	$field_data['sort_param'] = 0; // default: up top
	
	$FieldObj->props 	= $field_data;  // This is how we repopulate data in the create forms

	// Any errors?
	if ( !empty($FieldObj->errors) ) {
		$data['msg'] = $FieldObj->format_errors();
	}
	// Save;
	else {
		$field_name = $field_data['name']; 
		self::$data['custom_field_defs'][$field_name] = $field_data;
		update_option( self::db_key, self::$data );
		unset($_POST);
		$success_msg = sprintf('<div class="updated"><p>%s</p></div>'
			, sprintf(__('A %s custom field has been created.', CCTM_TXTDOMAIN)
			, '<em>'.$FieldObj->get_name().'</em>'));
		self::set_flash($success_msg);
		include(CCTM_PATH.'/controllers/list_custom_fields.php');
		return;
	}

}

$data['icon'] = sprintf('<img src="%s" class="cctm-field-icon" id="cctm-field-icon-%s"/>'
				, $FieldObj->get_icon(), $field_type);
$data['url'] = $FieldObj->get_url();
$data['name'] = $FieldObj->get_name();
$data['description'] = htmlentities($FieldObj->get_description());

$data['fields'] = $FieldObj->get_edit_field_definition($field_data);

$data['content'] = CCTM::load_view('custom_field.php', $data);
print CCTM::load_view('templates/default.php', $data);
/*EOF*/