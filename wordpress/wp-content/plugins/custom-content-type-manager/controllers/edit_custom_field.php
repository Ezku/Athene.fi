<?php
/*------------------------------------------------------------------------------
* Edit a custom field.  
*
* @param string $field_type identifies the type of field we're editing
* @param string $field_name	uniquely identifies this field inside this post_type

------------------------------------------------------------------------------*/
// Make sure the field exists
if (!array_key_exists($field_name, self::$data['custom_field_defs'])) {
	$msg_id = 'invalid_field_name';
	include(CCTM_PATH.'/controllers/error.php');
	return;
}


// Page variables
$data = array();
$data['page_title'] = sprintf(__('Edit Custom Field: %s', CCTM_TXTDOMAIN), $field_name );
$data['help'] = 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/SupportedCustomFields';
$data['msg'] = '';
$data['menu'] = sprintf('<a href="?page=cctm_fields&a=list_custom_fields" title="%s" class="button">%s</a>', __('Cancel'), __('Cancel'));
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


$FieldObj->props 	= $field_data;  
// THIS is what keys us off to the fact that we're EDITING a field: 
// the logic in CCTMFormElement->save_definition_filter() ensures we don't overwrite other fields.
// This attribute is nuked later
$FieldObj->props['original_name'] = $field_name; 

// Save if submitted...
if ( !empty($_POST) && check_admin_referer($data['action_name'], $data['nonce_name']) ) {
	// A little cleanup before we handoff to save_definition_filter
	unset($_POST[ $data['nonce_name'] ]);
	unset($_POST['_wp_http_referer']);

	// Validate and sanitize any submitted data
	$field_data 		= $FieldObj->save_definition_filter($_POST);
	$field_data['type'] = $field_type; // same effect as adding a hidden field
	$FieldObj->props 	= $field_data; // used for repopulating on errors

	// Any errors?
	if ( !empty($FieldObj->errors) ) {
		$data['msg'] = $FieldObj->format_errors();
	}
	// Save;
	else {
		// Unset the old field if the name changed ($field_name is passed via $_GET)
		if ($field_name != $field_data['name']) {
			unset(self::$data['custom_field_defs'][$field_name]);
			// update database... but what if the new name is taken?
		}
		self::$data['custom_field_defs'][ $field_data['name'] ] = $field_data;
		update_option( self::db_key, self::$data );
		unset($_POST);
		$data['msg'] = sprintf('<div class="updated"><p>%s</p></div>'
			, sprintf(__('The %s custom field has been edited.', CCTM_TXTDOMAIN)
			, '<em>'.$field_name.'</em>'));		
		self::set_flash($data['msg']);
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