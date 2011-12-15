<?php
if ( ! defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('administrator')) exit('Admins only.');
//------------------------------------------------------------------------------
/**
 * Manage custom fields for the given $post_type.
 *
 * @param string  $post_type
 * @param boolen  $reset     true only if we've just reset all custom fields
 * @package
 */


$data     = array();
$data['page_title'] = sprintf( __('Custom Fields for %s', CCTM_TXTDOMAIN), "<em>$post_type</em>");
$data['help']   = 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/FieldAssociations';
$data['menu']   = sprintf('<a href="'.get_admin_url(false, 'admin.php').'?page=cctm&a=list_custom_field_types&pt=%s" class="button">%s</a>', $post_type, __('Create Custom Field for this Post Type', CCTM_TXTDOMAIN) )
	. ' '.sprintf('<a href="'.get_admin_url(false, 'admin.php').'?page=cctm&a=template_single&pt=%s" class="button">%s</a>', $post_type, __('View Sample Template', CCTM_TXTDOMAIN) ) ;
$data['msg']  = CCTM::get_flash();


// Validate post type
if (!self::_is_existing_post_type($post_type) ) {
	$msg_id = 'invalid_post_type';
	include 'error.php';
	return;
}

$data['action_name'] = 'cctm_custom_save_sort_order';
$data['nonce_name'] = 'cctm_custom_save_sort_order_nonce';


// Save custom fields. The sort order is determined by simple physical location on the page.
if (!empty($_POST) && check_admin_referer($data['action_name'], $data['nonce_name']) ) {

	self::$data['post_type_defs'][$post_type]['custom_fields'] = array();
	if (!empty($_POST['custom_fields']) && is_array($_POST['custom_fields'])) {
		self::$data['post_type_defs'][$post_type]['custom_fields'] = $_POST['custom_fields'];
	}

	update_option( self::db_key, self::$data );
	$x = sprintf( __('Custom fields for %s have been updated.', CCTM_TXTDOMAIN), "<em>$post_type</em>" );
	$data['msg'] = sprintf('<div class="updated"><p>%s</p></div>', $x);
	self::set_flash($data['msg']);
	include CCTM_PATH . '/controllers/list_post_types.php';
	return;
}

// Active custom fields are those that are associated with THIS post_type
$active_custom_fields = array();
if ( isset(self::$data['post_type_defs'][$post_type]['custom_fields']) ) {
	$active_custom_fields = self::$data['post_type_defs'][$post_type]['custom_fields'];
}
$active_custom_fields_cnt = count($active_custom_fields);

$all_custom_fields = array();
if (isset(self::$data['custom_field_defs']) && is_array(self::$data['custom_field_defs']) ) {
	$all_custom_fields = array_keys(self::$data['custom_field_defs']);
}
$all_custom_fields_cnt = count($all_custom_fields);

if (!$all_custom_fields_cnt) {
	$data['msg'] .= sprintf('<div class="updated"><p>%s</p></div>'
		, __('There are no custom fields defined yet. <a href="'.get_admin_url(false, 'admin.php').'?page=cctm_fields&a=list_custom_field_types">Define custom fields</a>.', CCTM_TXTDOMAIN));
}
elseif (!$active_custom_fields_cnt ) {
	$data['msg'] .= sprintf('<div class="updated"><p>%s</p></div>'
		, sprintf( __('The %s post type does not have any custom fields yet. Check the fields below to add custom fields.', CCTM_TXTDOMAIN)
			, "<em>$post_type</em>" ));
}

$data['content'] = '';

// First, display the custom fields active for this post_type
foreach ($active_custom_fields as $cf) {
	if ( !isset(self::$data['custom_field_defs'][$cf])) {
		continue;
	}
	$d = self::$data['custom_field_defs'][$cf];

	$field_type_name = CCTM::classname_prefix.$d['type'];
	self::include_form_element_class($d['type']);
	$FieldObj = new $field_type_name();
	$d['icon'] = $FieldObj->get_icon();

	// $icon_src = self::get_custom_icons_src_dir() . $d['type'].'.png';

	if ( !CCTM::is_valid_img($d['icon']) ) {
		$d['icon'] = self::get_custom_icons_src_dir() . 'default.png';
	}

	$d['icon'] = sprintf('<img src="%s" style="float:left; margin:5px;"/>', $d['icon']);

	$d['class'] = '';
	$d['is_checked'] = ' checked="checked"';
	$d['edit_field_link'] = sprintf(
		'<a href="%s/wp-admin/admin.php?page=cctm_fields&a=edit_custom_field&field=%s&_wpnonce=%s" title="%s">%s</a>'
		, get_site_url()
		, $d['name']
		, wp_create_nonce('cctm_edit_field')
		, __('Edit this custom field', CCTM_TXTDOMAIN)
		, __('Edit', CCTM_TXTDOMAIN)
	);

	$data['content'] .= CCTM::load_view('tr_pt_custom_field.php', $d);
}
// Separator
$data['content'] .= '<tr class="no-sort"><td colspan="4" style="background-color:#ededed;"><hr /></td></tr>';

// Following, list the remaining custom fields
$remaining_custom_fields = array_diff($all_custom_fields, $active_custom_fields);
foreach ($remaining_custom_fields as $cf) {
	$d = self::$data['custom_field_defs'][$cf];

	$field_type_name = CCTM::classname_prefix.$d['type'];
	self::include_form_element_class($d['type']);
	$FieldObj = new $field_type_name();
	$d['icon'] = $FieldObj->get_icon();

	if ( !CCTM::is_valid_img($d['icon']) ) {
		$d['icon'] = self::get_custom_icons_src_dir() . 'default.png';
	}

	$d['icon'] = sprintf('<img src="%s" style="float:left; margin:5px;"/>', $d['icon']);
	$d['class'] = ''; // ' no-sort';
	$d['is_checked'] = '';

	$d['edit_field_link'] = sprintf(
		'<a href="%s/wp-admin/admin.php?page=cctm_fields&a=edit_custom_field&field=%s&_wpnonce=%s" title="%s">%s</a>'
		, get_site_url()
		, $d['name']
		, wp_create_nonce('cctm_edit_field')
		, __('Edit this custom field', CCTM_TXTDOMAIN)
		, __('Edit', CCTM_TXTDOMAIN)
	);

	$data['content'] .= CCTM::load_view('tr_pt_custom_field.php', $d);
}

$data['content'] = CCTM::load_view('sortable-list.php', $data);
print CCTM::load_view('templates/default.php', $data);


/*EOF*/