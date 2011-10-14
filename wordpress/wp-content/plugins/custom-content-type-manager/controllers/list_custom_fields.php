<?php
if ( ! defined('CCTM_PATH')) exit('No direct script access allowed');
if (!current_user_can('administrator')) exit('Admins only.');
//------------------------------------------------------------------------------
/**
 * Manage all custom fields.
 *
 * @param string $post_type
 * @param boolen $reset true only if we've just reset all custom fields
 */

$data=array();
$data['page_title'] = __('Manage Custom Fields', CCTM_TXTDOMAIN);
$data['help'] = 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/DefinedCustomFields';
$data['msg'] = self::get_flash();
$data['menu'] = sprintf('<a href="'.get_admin_url(false,'admin.php').'?page=cctm_fields&a=list_custom_field_types" class="button">%s</a>', __('Create Custom Field', CCTM_TXTDOMAIN) );

// Load 'em up
$def = array();
if ( isset(self::$data['custom_field_defs']) ) {
	$def = self::$data['custom_field_defs'];
}
// sort them
usort($def, CCTM::sort_custom_fields('name', 'strnatcasecmp'));

foreach ( $def as $i => $d ) {
	$field_name = $d['name'];
	$def[$field_name] = $d; // re-establish the key version.
	unset($def[$i]); // kill the integer version
} 			


$def_cnt = count($def);

if (!isset($reset) && !$def_cnt ) {
	$data['msg'] .= sprintf('<div class="updated"><p>%s</p></div>'
		, __('There are no custom fields defined. Click the button below to add a custom field.', CCTM_TXTDOMAIN));
}

$data['fields'] = '';

foreach ($def as $field_name => $d) {
	$d['name'] = $field_name; // just in case the key and the 'name' got out of sync.
	$icon_src = self::get_custom_icons_src_dir() . $d['type'].'.png';

	if ( !CCTM::is_valid_img($icon_src) ) {
		$icon_src = self::get_custom_icons_src_dir() . 'default.png';
	}

	$d['icon'] = sprintf('<img src="%s" style="float:left; margin:5px;"/>', $icon_src);

	
	$d['edit'] = __('Edit');
	$d['delete'] = __('Delete');
	$d['edit_field_link'] = sprintf(
		'<a href="?page=cctm_fields&a=edit_custom_field&field=%s&_wpnonce=%s" title="%s">%s</a>'
		, $d['name']
		, wp_create_nonce('cctm_edit_field')
		, __('Edit this custom field', CCTM_TXTDOMAIN)
		, __('Edit', CCTM_TXTDOMAIN)
	);
	$d['duplicate_field_link'] = sprintf(
		'<a href="?page=cctm_fields&a=duplicate_custom_field&field=%s&_wpnonce=%s" title="%s">%s</a>'
		, $d['name']
		, wp_create_nonce('cctm_edit_field')
		, __('Duplicate this custom field', CCTM_TXTDOMAIN)
		, __('Duplicate', CCTM_TXTDOMAIN)
	);
	$d['delete_field_link'] = sprintf(
		'<a href="?page=cctm_fields&a=delete_custom_field&field=%s&_wpnonce=%s" title="%s">%s</a>'
		, $d['name']
		, wp_create_nonce('cctm_delete_field')
		, __('Delete this custom field', CCTM_TXTDOMAIN)
		, __('Delete', CCTM_TXTDOMAIN)
	);
	$d['manage_associations_link'] = sprintf(
		'<a href="?page=cctm_fields&a=list_field_associations&field=%s&_wpnonce=%s" title="%s">%s</a>'
		, $d['name']
		, wp_create_nonce('cctm_delete_field')
		, __('Manage which content types this custom field is associated with.', CCTM_TXTDOMAIN)
		, __('Manage Associations', CCTM_TXTDOMAIN)
	);
	//$data['fields'] .= self::parse($tpl, $d);
	$data['fields'] .= CCTM::load_view('tr_custom_field.php',$d);
}

$data['content'] = CCTM::load_view('list_custom_fields.php', $data);
print CCTM::load_view('templates/default.php', $data);

/*EOF*/