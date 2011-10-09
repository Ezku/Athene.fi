<?php
/*------------------------------------------------------------------------------
Create a new post type
------------------------------------------------------------------------------*/
$data=array();
$data['page_title'] = __('Create Custom Content Type', CCTM_TXTDOMAIN);
$data['help'] = 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/CreatePostType';
$data['msg'] = '';
$data['menu'] = sprintf('<a href="?page=cctm" title="%s" class="button">%s</a>', __('Cancel'), __('Cancel'));
$data['edit_warning'] = ''; // only used when you edit a post_type, not delete.

// Variables for our template

$fields   = '';

$data['action_name']  = 'custom_content_type_mgr_create_new_content_type';
$data['nonce_name']  = 'custom_content_type_mgr_create_new_content_type_nonce';
$data['submit']   = __('Create New Content Type', CCTM_TXTDOMAIN);
$data['action'] = 'create';

$data['post_type'] = ''; // as default
$data['def'] = self::$default_post_type_def;
//		$def = self::$post_type_form_definition;

// Save data if it was properly submitted
if ( !empty($_POST) && check_admin_referer($data['action_name'], $data['nonce_name']) ) {
	$sanitized_vals = self::_sanitize_post_type_def($_POST);
	$error_msg = self::_post_type_name_has_errors($sanitized_vals, true);

	if ( empty($error_msg) ) {
		self::_save_post_type_settings($sanitized_vals);
		$data['msg'] = '
		<div class="updated">
			<p>'
			. sprintf( __('The content type %s has been created', CCTM_TXTDOMAIN), '<em>'.$sanitized_vals['post_type'].'</em>')
			. '</p>
		</div>';
		self::set_flash($data['msg']);
		include CCTM_PATH . '/controllers/list_post_types.php';
		return;
	}
	else {
		// clean up... menu labels in particular can get gunked up. :(
		$data['def']  = $sanitized_vals;
		$data['def']['labels']['singular_name'] = '';
		$data['def']['label'] = '';
		$data['msg'] = "<div class='error'>$error_msg</div>";
	}
}

$data['content'] = CCTM::load_view('post_type.php', $data);
print CCTM::load_view('templates/default.php', $data);
/*EOF*/