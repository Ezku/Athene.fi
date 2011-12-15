<?php
if ( ! defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('administrator')) exit('Admins only.');
/*------------------------------------------------------------------------------
Edit a custom field of the type specified by $field_type.

$field_type is set in the $_GET array
------------------------------------------------------------------------------*/

// Page variables
$data = array();
$data['page_title'] = __('Create Custom Field', CCTM_TXTDOMAIN);
$data['help'] = 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/SupportedCustomFields';
$data['msg'] = '';
$data['menu'] = sprintf('<a href="'.get_admin_url(false, 'admin.php').'?page=cctm_fields&a=list_custom_field_types" title="%s" class="button">%s</a>', __('Cancel'), __('Cancel'));
$data['action_name']  = 'custom_content_type_mgr_create_new_custom_field';
$data['nonce_name']  = 'custom_content_type_mgr_create_new_custom_field_nonce';

$field_data = array(); // Data object we will save

// Fail if there's a problem
if (!self::include_form_element_class($field_type)) {
	$data['msg'] = CCTM::format_errors();
	$data['content'] = '';
	print CCTM::load_view('templates/default.php', $data);
	return;
}

$field_type_name = self::classname_prefix.$field_type;
$FieldObj = new $field_type_name(); // Instantiate the field element





// Save if submitted...
if ( !empty($_POST) && check_admin_referer($data['action_name'], $data['nonce_name']) ) {
	// A little cleanup before we handoff to save_definition_filter
	unset($_POST[ $data['nonce_name'] ]);
	unset($_POST['_wp_http_referer']);

	// Validate and sanitize any submitted data
	$field_data   = $FieldObj->save_definition_filter($_POST, $post_type);
	$FieldObj->props  = $field_data;  // This is how we repopulate data in the create forms

	// Any errors?
	if ( !empty($FieldObj->errors) ) {
		$data['msg'] = $FieldObj->format_errors();
	}
	// Save;
	else {
		$field_name = $field_data['name'];
		self::$data['custom_field_defs'][$field_name] = $field_data;

		$success_msg = sprintf('<div class="updated"><p>%s</p></div>'
			, sprintf(__('A %s custom field has been created.', CCTM_TXTDOMAIN)
				, '<em>'.$FieldObj->get_name().'</em>'));

		// Optionally, the &pt parameter can be set, indicating that this field should be associated with the given post_type
		if (!empty($post_type)) {
			if (isset(self::$data['post_type_defs'][$post_type])) {
				$success_msg = sprintf('<div class="updated"><p>%s</p></div>'
					, sprintf(__('A %s custom field has been created for the %s post-type.', CCTM_TXTDOMAIN)
						, '<em>'.$FieldObj->get_name().'</em>'
						, "<em>$post_type</em>"));

				if (isset(self::$data['post_type_defs'][$post_type]['custom_fields'])
					&& is_array(self::$data['post_type_defs'][$post_type]['custom_fields'])
				) {
					// Make sure we have unique entries in the custom_fields array
					if (!in_array($field_name, self::$data['post_type_defs'][$post_type]['custom_fields'])) {
						self::$data['post_type_defs'][$post_type]['custom_fields'][] = $field_name;
					}
				}
				else {
					self::$data['post_type_defs'][$post_type]['custom_fields'][] = $field_name;
				}

			}
		}

		update_option( self::db_key, self::$data );
		unset($_POST);
		self::set_flash($success_msg);

		// We redirect to different places if we have auto-associated the field to a post_type
		if (!empty($post_type)) {
			self::redirect('?page=cctm&a=list_pt_associations&pt='.$post_type);
			//include(CCTM_PATH.'/controllers/list_pt_associations.php');
		}
		else {
			include CCTM_PATH.'/controllers/list_custom_fields.php';
		}
		return;
	}

}

$data['fields'] = $FieldObj->get_create_field_definition();

$data['icon'] = sprintf('<img src="%s" class="cctm-field-icon" id="cctm-field-icon-%s"/>'
	, $FieldObj->get_icon(), $field_type);
$data['url'] = $FieldObj->get_url();
$data['name'] = $FieldObj->get_name();
$data['description'] = htmlentities($FieldObj->get_description());


$data['content'] = CCTM::load_view('custom_field.php', $data);
print CCTM::load_view('templates/default.php', $data);


/*EOF*/