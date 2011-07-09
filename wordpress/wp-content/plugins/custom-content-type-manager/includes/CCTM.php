<?php
/*------------------------------------------------------------------------------
CCTM = Custom Content Type Manager

This is the main class for the Custom Content Type Manager plugin.

Homepage:
http://code.google.com/p/wordpress-custom-content-type-manager/

It is largely static classes 

This class handles the creation and management of custom post-types (also
referred to as 'content-types'). 

Post Thumbnails support is post-type specific:
http://markjaquith.wordpress.com/2009/12/23/new-in-wordpress-2-9-post-thumbnail-images/
------------------------------------------------------------------------------*/
class CCTM {
	// Name of this plugin
	const name   = 'Custom Content Type Manager';
	const version = '0.9.0';
	
	// Required versions (referenced in the CCTMtest class).
	const wp_req_ver  = '3.0.1';
	const php_req_ver  = '5.2.6';
	const mysql_req_ver = '4.1.2';

	/**
	 * The following constants identify the option_name in the wp_options table
	 * where this plugin stores various data.
	 *
	 * db_key : the primary key: the option_value contains a serialized
	 * definition of all defined post types and their custom fields.
	 *
	 * db_key_settings : a secondary key that stores settings particular to the 
	 * current site.
	 */
	 
	const db_key  = 'custom_content_types_mgr_data';

	const db_key_settings = 'custom_content_types_mgr_settings';

	// Used to uniquely identify this plugin's menu page in the WP manager
	const admin_menu_slug = 'cctm';

	// These parameters identify where in the $_GET array we can find the values
	// and how URLs are constructed, e.g. some-admin-page.php?a=123&pt=xyz
	const action_param    = 'a';
	const post_type_param   = 'pt';

	// Each class that extends the FormElement class must prefix this to its class name.
	const FormElement_classname_prefix = 'CCTM_';

	// used to control the uploading of the .cctm.json files
	const max_def_file_size = 524288; // in bytes
	
	// Directory relative to wp-content/uploads where we can store def files
	// Omit the trailing slash.
	const base_storage_dir = 'cctm';
	
	// Directory relative to wp-content/uploads/{self::base_storage_dir} used to store 
	// the .cctm.json definition files. Omit the trailing slash.
	const def_dir = 'defs';

	// Default permissions for dirs/files created in the base_storage_dir.
	// These cannot be more permissive thant the system's settings: the system
	// will automatically shave them down. E.g. if the system has a global setting
	// of 0755, a local setting here of 0770 gets bumped down to 0750.
	const new_dir_perms = 0777;
	const new_file_perms = 0666;

	// Used to filter inputs (e.g. descriptions)
	public static $allowed_html_tags = '<a><strong><em><code><style>';
		
	// Data object stored in the wp_options table representing all primary data
	// for post_types and custom fields
	public static $data = array();
	
	// integer iterator used to uniquely identify groups of field definitions for
	// CSS and $_POST variables
	public static $def_i = 0;

	public static $default_post_type_def = array
		(
		    'supports' => array('title', 'editor'),
		    'taxonomies' => array(),
		    'post_type' => '',
		    'labels' => array
		        (
		            'menu_name' => '',
		            'singular_name' => '',
		            'add_new' => '',
		            'add_new_item' => '',
		            'edit_item' => '',
		            'new_item' => '',
		            'view_item' => '',
		            'search_items' => '',
		            'not_found' => '',
		            'not_found_in_trash' => '',
		            'parent_item_colon' => '',
		        ),
		    'description' => '',
		    'show_ui' => 1,
		    'public' => 1,
		    'menu_icon' => '',
		    'label' => '',
		    'menu_position' => '',
		    'rewrite_with_front' => 1,
		    'permalink_action' => 'Off',
		    'rewrite_slug' => '',
		    'query_var' => '',
		    'capability_type' => 'post',
		    'show_in_nav_menus' => 1,
		    'can_export' => 1,
		    'use_default_menu_icon' => 1,
		    'hierarchical' => 0,
		    'rewrite' => '',
		    'has_archive' => 0
		);


	// Where are the icons for custom images stored?
	// TODO: let the users select their own dir in their own directory
	public static $custom_field_icons_dir;

	// Built-in post-types that can have custom fields, but cannot be deleted.
	public static $built_in_post_types = array('post', 'page');

	// Names that are off-limits for custom post types b/c they're already used by WP
	public static $reserved_post_types = array('post', 'page', 'attachment', 'revision'
		, 'nav_menu', 'nav_menu_item');

	// Custom field names are not allowed to use the same names as any column in wp_posts
	public static $reserved_field_names = array('ID', 'post_author', 'post_date', 'post_date_gmt',
		'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status',
		'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
		'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type',
		'comment_count');

	// Future-proofing: post-type names cannot begin with 'wp_'
	// See: http://codex.wordpress.org/Custom_Post_Types
	// FUTURE: List any other reserved prefixes here (if any)
	public static $reserved_prefixes = array('wp_');

	public static $errors; // used to store validation errors


	//! Private Functions
	//------------------------------------------------------------------------------
	/**
	 * This formats any errors registered in the class $errors array. The errors 
	 * take this format: self::$errors['field_name'] = 'Description of error';
	 * 
	 * @return	string	(empty string if no errors)
	 */
	private static function _format_errors() {
		$error_str = '';
		if ( empty ( self::$errors ) ) {
			return '';
		}
		
		foreach ( self::$errors as $e ) {
			$error_str .= '<li>'.$e.'</li>
			';	
		}

		return sprintf('<div class="error">
			<p><strong>%1$s</strong></p>
			<ul style="margin-left:30px">
				%2$s
			</ul>
			</div>'
			, __('Please correct the following errors:', CCTM_TXTDOMAIN)
			, $error_str
		);
	}
	
	//------------------------------------------------------------------------------
	/**
	 * This prints out a list (including icons) of all available custom field types.
	 *
	 * @param string $post_type
	 * @return unknown
	 */
	private static function _get_available_custom_field_types($post_type) {
		// TODO: move this into another option
		$available_custom_field_types = array('checkbox','colorselector','date','dropdown','image','media','multiselect','relation','text','textarea','wysiwyg', );
		
		// Aha... why have %s AND [+placeholders+].  Good question.
		$output = '<ul id="cctm-field-type-selector">';
		$tpl = sprintf(
			'<li><a href="?page=%s&%s=9&%s=%s&type=[+field_type+]" title="[+title+]">
					[+icon_img+]
					</a>
					<!-- a href="?page=%s&%s=9&%s=%s&type=[+field_type+]" class="button" title="[+title+]">[+label+]</a-->
				</li>'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
		);

		foreach ( $available_custom_field_types as $field_type ) {
			self::include_form_element_class($field_type); // This will die on errors
			$field_type_name = self::FormElement_classname_prefix.$field_type;
			$FieldObj = new $field_type_name(); // Instantiate the field element

			$hash = array();
			# $hash['icon_src'] = self::get_custom_icons_src_dir() . $field_type.'.png';
			$hash['icon_img'] = $FieldObj->get_icon();
			$hash['field_type'] = $field_type;
			$hash['label'] = ucfirst($field_type);
			$hash['title'] = sprintf( __('Create a %s custom field', CCTM_TXTDOMAIN), $field_type );

			$output .= self::parse($tpl, $hash);

		}


		return $output . '</ul>';
	}

	//------------------------------------------------------------------------------
	/**
	 * This allows us to dynamically change the field classes in our forms.
	 * Normally, the output is only 'cctm_text', but if there is an error 
	 * in self::$errors[$fieldname], then the class becomes 
	 * 'cctm_text cctm_error'.
	 */
	private static function _get_class($fieldname, $fieldtype='text') {
		if ( isset(self::$errors[$fieldname]) ) {
			return "cctm_$fieldtype cctm_error";
		}
		else {
			return "cctm_$fieldtype";
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	* Geared to be backwards compatible with CCTM versions prior to 0.8.8 where
	* custom field defs were stored in numbered arrays instead of keyed off of their
	* unique names.
	*
	* @param 	mixed	$data: full data structure
	* @param	string	$post_type: the name of this post_type
	* @param	string	$field_name: the name of the field whose data you want
	* @return	array	associative array representing a field definition for $field_name
	*/
	private static function _get_field_data($data, $post_type, $field_name) {
		if ( empty($data) || empty($data[$post_type]) || empty($data[$post_type]['custom_fields']))
		{
			return array();
		}
		foreach ( $data[$post_type]['custom_fields'] as $tmp => $def )
		{
			if ( $def['name'] == $field_name )
			{
				return $def;
			}
		}
		return array(); // gave up
	}
	
	/*------------------------------------------------------------------------------
	Generate HTML portion of our manage custom fields form. This is in distinction
	to the JS portion of the form, which uses a slightly different format.

	self::$def_i is used to track the definition #.  All 5 output fields will use
	the same $def_i number to identify their place in the $_POST array.

	INPUT: $custom_field_defs (mixed) an array of hashes, each hash describing
	a custom field.

	Array
	(
	    [1] => Array
	        (
	            [label] => Rating
	            [name] => rating
	            [description] => MPAA rating
	            [type] => dropdown
	            [options] => Array
	                (
	                    [0] => G
	                    [1] => PG
	                    [2] => PG-13
	                )

	            [sort_param] =>
	        )

	)
	 * @return string	An HTML form, length depends on the # of field defs.
	 */
	private static function _get_field_type_icons() {

		$icons = array();
		if ($handle = opendir(CCTM_PATH.'/images/custom-fields/')) {
			while (false !== ($file = readdir($handle))) {
				if ( !preg_match('/^\./', $file) && preg_match('/\.png$/i', $file) ) {
					$icons[] = $file;
				}
			}
			closedir($handle);
		}

		$output = '';
		$tpl = CCTM_PATH.'/tpls/settings/icon.tpl';
		if ( file_exists($tpl) ) {
			$tpl = file_get_contents($tpl);

		}
		foreach ( $icons as $img ) {
			$output .= self::parse($tpl, array('title'=> $img, 'src'=> CCTM_URL.'/images/icons/default/'.$img) );
		}

		return $output;
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @return unknown
	 */
	private static function _get_post_type_icons() {

		$icons = array();
		if ($handle = opendir(CCTM_PATH.'/images/icons/default')) {
			while (false !== ($file = readdir($handle))) {
				if ( !preg_match('/^\./', $file) ) {
					$icons[] = $file;
				}
			}
			closedir($handle);
		}

		$output = '';
		$tpl = CCTM_PATH.'/tpls/settings/icon.tpl';
		$tpl = file_get_contents($tpl);

		foreach ( $icons as $img ) {
			$output .= self::parse($tpl, array('title'=> $img, 'src'=> CCTM_URL.'/images/icons/default/'.$img) );
		}

		return $output;
	}

	//------------------------------------------------------------------------------
	/**
	SYNOPSIS: checks the custom content data array to see if $post_type exists.
		The $data array is structured something like this:

		$data = array(
			'movie' => array('name'=>'movie', ... ),
			'book' => array('name'=>'book', ... ),
			...
		);

	So we can just check the keys of the main array to see if the post type exists.

	Built-in post types 'page' and 'post' are considered valid (i.e. existing) by
	default, even if they haven't been explicitly defined for use by this plugin
	so long as the 2nd argument, $search_built_ins, is not overridden to false.

	 *
	 *
	 * @param string $post_type	the lowercase database slug identifying a post type.
	 * @param boolean $search_built_ins (optional) whether or not to search inside the
			$built_in_post_types array.
	 * @return boolean indicating whether this is a valid post-type
	 */
	private static function _is_existing_post_type($post_type, $search_built_ins=true) {
	
		// If there is no existing data, check against the built-ins
		if ( empty(self::$data) && $search_built_ins ) {
			return in_array($post_type, self::$built_in_post_types);
		}
		// If there's no existing $data and we omit the built-ins...
		elseif ( empty(self::$data) && !$search_built_ins ) {
			return false;
		}
		// Check to see if we've stored this $post_type before
		elseif ( array_key_exists($post_type, self::$data) ) {
			return true;
		}
		// Check the built-ins
		elseif ( $search_built_ins && in_array($post_type, self::$built_in_post_types) ) {
			return true;
		}
		else {
			return false;
		}
	}


	//! Links
	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string $post_type
	 * @return unknown
	 */
	private static function _link_activate($post_type) {
		return sprintf(
			'<a href="?page=%s&%s=6&%s=%s" title="%s">%s</a>'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, __('Activate this content type', CCTM_TXTDOMAIN)
			, __('Activate', CCTM_TXTDOMAIN)
		);
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Used when you want to activate an imported cctm definition
	 *
	 * @return string	the href target
	 */
	private static function _link_activate_imported_def() {
		return sprintf(
			'?page=%s&%s=13'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
		);
	}	

	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string $post_type
	 * @return unknown
	 */
	private static function _link_deactivate($post_type) {
		return sprintf(
			'<a href="?page=%s&%s=7&%s=%s" title="%s">%s</a>'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, __('Deactivate this content type', CCTM_TXTDOMAIN)
			, __('Deactivate', CCTM_TXTDOMAIN)
		);
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string $post_type
	 * @return unknown
	 */
	private static function _link_delete($post_type) {
		return sprintf(
			'<a href="?page=%s&%s=3&%s=%s" title="%s">%s</a>'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, __('Delete this content type', CCTM_TXTDOMAIN)
			, __('Delete', CCTM_TXTDOMAIN)
		);
	}

	//------------------------------------------------------------------------------
	/**
	 * Used when you want to preview an imported def
	 *
	 * @return string	the href target
	 */
	private static function _link_preview_def() {
		return sprintf(
			'?page=%s&%s=14'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
		);
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Delete all custom fields for the given post_type
	 *
	 * @param string $post_type
	 * @return string
	 */
	private static function _link_reset_all_custom_fields($post_type) {
		return sprintf(
			'<a href="?page=%s&%s=12&%s=%s" title="%s" class="button">%s</a>'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, __('Delete all custom field definitions for this post type', CCTM_TXTDOMAIN)
			, __('Reset Custom Fields', CCTM_TXTDOMAIN)
		);
	}

	///------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string 	$post_type a post_type known to CCTM (not necessarily currently registered)
	 * @return string	HTML link for managing the custom fields 
	 */
	private static function _link_manage_custom_fields($post_type) {
		return sprintf(
			'<a href="?page=%s&%s=4&%s=%s" title="%s">%s</a>'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, __('Manage Custom Fields for this content type', CCTM_TXTDOMAIN)
			, __('Manage Custom Fields', CCTM_TXTDOMAIN)
		);

	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string $post_type a post_type known to CCTM (not necessarily currently registered)
	 * @return string	HTML link to edit a post_type
	 */
	private static function _link_edit($post_type) {
		return sprintf(
			'<a href="?page=%s&%s=2&%s=%s" title="%s">%s</a>'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, __('Edit this content type', CCTM_TXTDOMAIN )
			, __('Edit', CCTM_TXTDOMAIN)
		);
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string $post_type
	 * @return unknown
	 */
	private static function _link_view_sample_templates($post_type) {
		return sprintf('<a href="?page=%s&%s=8&%s=%s" title="%s">%s</a>'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, __('View Sample Templates for this content type', CCTM_TXTDOMAIN )
			, __('View Sample Templates', CCTM_TXTDOMAIN)
		);
	}


	//! Pages
	//------------------------------------------------------------------------------
	/**
	 * Moves the definition stored in $settings['candidate'] into the active CCTM::$data
	 */
	private static function _page_activate_imported_def() {
	
		// Validate...
		$settings = get_option(self::db_key_settings, array() );
		$candidate = self::_get_value($settings, 'candidate');
		$new_data = self::_get_value($candidate, 'payload');
		
		if ( empty($candidate) || empty($new_data)) {
			self::_page_display_error('no_cttm_def_available');
			return;
		}
		
		// yes, it *was* export data, now it's being *imported*
		$import_data = self::_get_value($candidate, 'export_info'); 
		$title = self::_get_value($import_data, 'title');
		
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
				window.location.replace("?page='.self::admin_menu_slug.'");
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

	}
	
	//------------------------------------------------------------------------------
	/**
	 * Manager Page -- called by page_main_controller()
	 * Activating a post type will cause it to show up in the WP menus and its custom
	 * fields will be managed.
	 * @param string $post_type
	 */
	private static function _page_activate_post_type($post_type) {
		// Validate post type
		if (!self::_is_existing_post_type($post_type) ) {
			self::_page_display_error();
			return;
		}

		self::$data[$post_type]['is_active'] = 1;
		update_option( self::db_key, self::$data );
		$msg = '
				<div class="updated">
					<p>'
			. sprintf( __('The %s post_type has been activated.', CCTM_TXTDOMAIN), '<em>'.$post_type.'</em>')
			. '</p>
				</div>';
		self::set_flash($msg);
		// Often, PHP scripts use the header() function to refresh a page, but
		// WP has already claimed those, so we use a JavaScript refresh instead.
		// Refreshing the page ensures that active post types are added to menus.
		$msg = '
			<script type="text/javascript">
				window.location.replace("?page='.self::admin_menu_slug.'");
			</script>';
		print $msg;
	}


	//------------------------------------------------------------------------------
	/**
	Edit a custom field.  This is a bit complicated, but it doesn't involve JS like
	the previous version did. 
	 *
	 *
	 * @param string $post_type
	 * @param string $field_name	 
	 */
	private static function _page_create_custom_field($post_type, $field_type) {

		if ( !self::_is_existing_post_type($post_type, true ) ) {
			self::_page_display_error();
			return;
		}
		
		// Page variables
		$heading = __('Create Field', CCTM_TXTDOMAIN);
		$msg = '';
		$action_name  = 'custom_content_type_mgr_create_new_custom_field';
		$nonce_name  = 'custom_content_type_mgr_create_new_custom_field_nonce';
		$success_msg = sprintf('<div class="updated"><p>%s</p></div>'
			, sprintf(__('A custom field for %s has been created.', CCTM_TXTDOMAIN)
			, '<em>'.$post_type.'</em>'));
			
		$field_data = array(); // Data object we will save
		
		self::include_form_element_class($field_type); // This will die on errors
		
		$field_type_name = self::FormElement_classname_prefix.$field_type;
		$FieldObj = new $field_type_name(); // Instantiate the field element
		
		// Save if submitted...
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			// A little cleanup before we handoff to save_definition_filter
			unset($_POST[ $nonce_name ]);
			unset($_POST['_wp_http_referer']);

			// Validate and sanitize any submitted data
			$field_data 		= $FieldObj->save_definition_filter($_POST, $post_type);
			$field_data['type'] = $field_type; // same effect as adding a hidden field
			
			$field_data['sort_param'] = 0; // default: up top
			
			$FieldObj->props 	= $field_data;  // This is how we repopulate data in the create forms

			// Any errors?
			if ( !empty($FieldObj->errors) ) {
				$msg = $FieldObj->format_errors();
			}
			// Save;
			else {
				$field_name = $field_data['name']; 
				self::$data[$post_type]['custom_fields'][$field_name] = $field_data;
				update_option( self::db_key, self::$data );
				unset($_POST);
				self::set_flash($success_msg);
				self::_page_show_custom_fields($post_type);
				return;
			}

		}
		// this should change to get_edit_field_definition() if it's an edit.
		$fields = $FieldObj->get_create_field_definition();

		$submit_link = $tpl = sprintf(
			'?page=%s&%s=9&%s=%s&type=%s'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, $field_type
		);
		
		$icon = $FieldObj->get_icon();
		
		include 'pages/custom_field.php';

	}

	//------------------------------------------------------------------------------
	/**
	 * Manager Page -- called by page_main_controller()
	 * Create a new post type
	 */
	private static function _page_create_post_type() {
//		self::_set_post_type_form_definition();

		// Variables for our template
		$page_header  = __('Create Custom Content Type', CCTM_TXTDOMAIN);
		$fields   = '';

		$action_name  = 'custom_content_type_mgr_create_new_content_type';
		$nonce_name  = 'custom_content_type_mgr_create_new_content_type_nonce';
		$submit   = __('Create New Content Type', CCTM_TXTDOMAIN);
		$action = 'create';
		$msg    = '';
		$post_type = ''; // as default
		$def = self::$default_post_type_def;
//		$def = self::$post_type_form_definition;

		// Save data if it was properly submitted
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			$sanitized_vals = self::_sanitize_post_type_def($_POST);
			$error_msg = self::_post_type_name_has_errors($sanitized_vals, true);

			if ( empty($error_msg) ) {
				self::_save_post_type_settings($sanitized_vals);
				$msg = '
				<div class="updated">
					<p>'
					. sprintf( __('The content type %s has been created', CCTM_TXTDOMAIN), '<em>'.$sanitized_vals['post_type'].'</em>')
					. '</p>
				</div>';
				self::set_flash($msg);
				self::_page_show_all_post_types();
				return;
			}
			else {
				// clean up... menu labels in particular can get gunked up. :(
				$def  = $sanitized_vals;
				$def['labels']['singular_name'] = '';
				$def['label'] = '';
				$msg = "<div class='error'>$error_msg</div>";
			}
		}

		include('pages/post_type.php');

	}

	
	/*------------------------------------------------------------------------------
	Manager Page -- called by page_main_controller()
	Deactivate a post type. This will remove custom post types from the WP menus;
	deactivation stops custom fields from being standardized in built-in and custom
	post types
	------------------------------------------------------------------------------*/

	/**
	 *
	 *
	 * @param string $post_type
	 */
	private static function _page_deactivate_post_type($post_type) {
		// Validate post type
		if (!self::_is_existing_post_type($post_type) ) {
			self::_page_display_error();
			return;
		}
		// Variables for our template
		$page_header  = sprintf( __('Deactivate Content Type %s', CCTM_TXTDOMAIN), $post_type );
		$fields   = '';
		$action_name  = 'custom_content_type_mgr_deactivate_content_type';
		$nonce_name  = 'custom_content_type_mgr_deactivate_content_type_nonce';
		$submit   = __('Deactivate', CCTM_TXTDOMAIN);

		// If properly submitted, Proceed with deleting the post type
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			// get current values from database
			self::$data[$post_type]['is_active'] = 0;
			update_option( self::db_key, self::$data );

			$msg = '<div class="updated"><p>'
				. sprintf( __('The %s content type has been deactivated.', CCTM_TXTDOMAIN), $post_type )
				. '</p></div>';
			self::set_flash($msg);

			// A JavaScript refresh ensures that inactive post types are removed from the menus.
			$msg = '
			<script type="text/javascript">
				window.location.replace("?page='.self::admin_menu_slug.'");
			</script>';
			print $msg;
			return;
		}

		$msg = '<div class="error">
			<img src="'.CCTM_URL.'/images/warning-icon.png" width="50" height="44" style="float:left; padding:10px;"/>
			<p>'
			. sprintf( __('You are about to deactivate the %s post type.', CCTM_TXTDOMAIN ), "<strong>$post_type</strong>")
			.'</p>';

		// If it's a custom post type, we include some additional info.
		if ( !in_array($post_type, self::$built_in_post_types) ) {
			$msg .= '<p>'
				. sprintf( __('Deactivation does not delete anything, but it does make %s posts unavailable to the outside world. %s will be removed from the administration menus and you will no longer be able to edit them using the WordPress manager.', CCTM_TXTDOMAIN), "<strong>$post_type</strong>", "<strong>$post_type</strong>" )
				.'</p>';

		}

		$post_cnt_obj = wp_count_posts($post_type);
		$msg .= '<p>'
			. sprintf( __('This would affect %1$s published %2$s posts.'
				, CCTM_TXTDOMAIN), '<strong>'.$post_cnt_obj->publish.'</strong>'
			, "<strong>$post_type</strong>")
			.'</p>';
		$msg .= '<p>'.__('Are you sure you want to do this?', CCTM_TXTDOMAIN).'
				<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/DeactivatePostType" title="deactivating a content type" target="_blank">
					<img src="'.CCTM_URL.'/images/question-mark.gif" width="16" height="16" />
				</a>
				</p>
			</div>';

		include 'pages/basic_form.php';
	}


	//------------------------------------------------------------------------------
	/**
	 * called by page_main_controller()
	 *
	 * @param string $post_type
	 * @param null
	 */
	private static function _page_delete_custom_field($post_type, $field) {
		// We can't delete built-in post types
		if (!self::_is_existing_post_type($post_type, false ) ) {
			self::_page_display_error();
			return;
		}

		$custom_fields_array = array();

		// For compatibility with versions prior to 0.8.8, we iterate through
		if ( !empty(self::$data[$post_type]['custom_fields']) ) {
			foreach (self::$data[$post_type]['custom_fields'] as $k => $def )
			{
				$custom_fields_array[] = $def['name'];
			}
			# $custom_fields_array = array_keys($data[$post_type]['custom_fields']);
		}
		if ( !in_array($field, $custom_fields_array) ) {
			$msg = '<p>'. __('Invalid custom field.', CCTM_TXTDOMAIN)
				. '</p>';
			$msg .= sprintf(
				'<a href="?page=%s&%s=4&%s=%s" title="%s" class="button">%s</a>'
				, self::admin_menu_slug
				, self::action_param
				, self::post_type_param
				, $post_type
				, __('Manage Custom Fields for this content type', CCTM_TXTDOMAIN)
				, __('Back', CCTM_TXTDOMAIN)
			);
			wp_die( $msg );
		}

		$nonce = self::_get_value($_GET, '_wpnonce');

		if (! wp_verify_nonce($nonce, 'cctm_delete_field') ) {
			die( __('Invalid request.', CCTM_TXTDOMAIN ) );
		}
		else {
			// Again, for compatibility with versions prior to 0.8.8, we do not assume that the 
			// field names exist as keys inside of 'custom_fields' (there could be int keys)
			foreach (self::$data[$post_type]['custom_fields'] as $k => $def ) {
				if ($def['name'] == $field) {
					unset(self::$data[$post_type]['custom_fields'][$k]);
				}
			}
			 
			
			update_option( self::db_key, self::$data );
			$msg = '<div class="updated"><p>'
				.sprintf( __('The %s custom field has been deleted', CCTM_TXTDOMAIN), "<em>$field</em>")
				. '</p></div>';
			self::set_flash($msg);
			unset($_POST);
			self::_page_show_custom_fields($post_type);
			return;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Manager Page -- called by page_main_controller()
	 * This is only a valid page for custom post types.
	 * @param string $post_type
	 * @return null
	 */
	private static function _page_delete_post_type($post_type) {
		// We can't delete built-in post types
		if (!self::_is_existing_post_type($post_type, false ) ) {
			self::_page_display_error();
			return;
		}

		// Variables for our template
		$page_header = sprintf( __('Delete Content Type: %s', CCTM_TXTDOMAIN), $post_type );
		$fields   = '';
		$action_name = 'custom_content_type_mgr_delete_content_type';
		$nonce_name = 'custom_content_type_mgr_delete_content_type_nonce';
		$submit   = __('Delete', CCTM_TXTDOMAIN);

		// If properly submitted, Proceed with deleting the post type
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			unset(self::$data[$post_type]); // <-- Delete this node of the data structure
			update_option( self::db_key, self::$data );
			$msg = '<div class="updated"><p>'
				.sprintf( __('The post type %s has been deleted', CCTM_TXTDOMAIN), "<em>$post_type</em>")
				. '</p></div>';
			self::set_flash($msg);
			self::_page_show_all_post_types();
			return;
		}

		$msg = '<div class="error">
			<img src="'.CCTM_URL.'/images/warning-icon.png" width="50" height="44" style="float:left; padding:10px;"/>
			<p>'
			. sprintf( __('You are about to delete the %s post type. This will remove all of its settings from the database, but this will NOT delete any rows from the wp_posts table. However, without a custom post type defined for those rows, they will be essentially invisible to WordPress.', CCTM_TXTDOMAIN), "<em>$post_type</em>" )
			.'</p>'
			. '<p>'.__('Are you sure you want to do this?', CCTM_TXTDOMAIN).'
			<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/DeletePostType" title="Deleting a content type" target="_blank">
			<img src="'.CCTM_URL.'/images/question-mark.gif" width="16" height="16" />
		</a>
			</p></div>';

		include 'pages/basic_form.php';

	}


	//------------------------------------------------------------------------------
	/**
	 * Manager Page -- called by page_main_controller()
	 * Returned on errors. Future: accept an argument identifying an error
	 * @param string $msg_id identifies the error.
	 */
	private static function _page_display_error($msg_id='invalid_post_type') {
		$msg = '';
		switch ($msg_id) {
			case 'invalid_field_name':
				$msg = '<p>'. __('Invalid field name.', CCTM_TXTDOMAIN)
					. '</p><a class="button" href="?page='
					.self::admin_menu_slug.'">'. __('Back', CCTM_TXTDOMAIN). '</a>';
				break;
			case 'no_cttm_def_available':
				$msg = '<p>'. __('There is no definition that is ready for importing, or the definition that you are trying to import is empty.', CCTM_TXTDOMAIN)
					. '</p><a class="button" href="?page='
					.self::admin_menu_slug.'_i">'. __('Import Definition', CCTM_TXTDOMAIN). '</a>';
				break;
			default:
				$msg = '<p>'. __('Invalid post type.', CCTM_TXTDOMAIN)
					. '</p><a class="button" href="?page='
					.self::admin_menu_slug.'">'. __('Back', CCTM_TXTDOMAIN). '</a>';
		}
		wp_die( $msg );
	}

	//------------------------------------------------------------------------------
	/**
	 * Edit a custom field.  This is a bit complicated, but it doesn't involve JS like
	 * the previous version did. 
	 *
	 * @param string $post_type
	 * @param string $field_name	uniquely identifies this field inside this post_type
	 */
	private static function _page_edit_custom_field($post_type, $field_name) {
	
		if ( !self::_is_existing_post_type($post_type, true ) ) {
			self::_page_display_error();
			return;
		}
		$msg = '';
		
		$nonce = self::_get_value($_GET, '_wpnonce');
		if (! wp_verify_nonce($nonce, 'cctm_edit_field') ) {
			die( __('Invalid request.', CCTM_TXTDOMAIN ) );
		}
				
		$field_data = array(); // Data object we will save
		// For compatibility with versions prior to 0.8.8, we iterate through
		if ( !empty(self::$data[$post_type]['custom_fields']) ) {
			foreach (self::$data[$post_type]['custom_fields'] as $k => $def )
			{
				$custom_fields_array[] = $def['name'];
				if ($def['name'] == $field_name) {
					$field_data = $def; // Data object we will save
				}
			}
		}
//		$field_data = self::$data[$post_type]['custom_fields'][$field_name];
				
		if ( !in_array($field_name, $custom_fields_array) ) {
			$msg = '<p>'. __('Invalid custom field.', CCTM_TXTDOMAIN)
				. '</p>';
			$msg .= sprintf(
				'<a href="?page=%s&%s=4&%s=%s" title="%s" class="button">%s</a>'
				, self::admin_menu_slug
				, self::action_param
				, self::post_type_param
				, $post_type
				, __('Manage Custom Fields for this content type', CCTM_TXTDOMAIN)
				, __('Back', CCTM_TXTDOMAIN)
			);
			wp_die( $msg );
		}
		
		$field_type = $field_data['type'];
		
		// Page variables
		$heading = __('Edit Field', CCTM_TXTDOMAIN);
		
		$action_name  = 'custom_content_type_mgr_create_new_custom_field';
		$nonce_name  = 'custom_content_type_mgr_create_new_custom_field_nonce';
		$success_msg = sprintf('<div class="updated"><p>%s</p></div>'
			, sprintf(__('The %s custom field has been edited.', CCTM_TXTDOMAIN)
			, '<em>'.$post_type.'</em>'));
			
		
		self::include_form_element_class($field_type); // This will die on errors
		
		$field_type_name = self::FormElement_classname_prefix.$field_type;
		$FieldObj = new $field_type_name(); // Instantiate the field element
		//
		$FieldObj->props 	= $field_data;  
		// THIS is what keys us off to the fact that we're EDITING a field: 
		// the logic in FormElement->save_definition_filter() ensures we don't overwrite other fields.
		// This attribute is nuked by the time we get down to line 691 or so.
		$FieldObj->original_name = $field_name; 
		
		// Save if submitted...
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			// A little cleanup before we handoff to save_definition_filter
			unset($_POST[ $nonce_name ]);
			unset($_POST['_wp_http_referer']);

			// Validate and sanitize any submitted data
			$field_data 		= $FieldObj->save_definition_filter($_POST, $post_type);
			$field_data['type'] = $field_type; // same effect as adding a hidden field
			$field_data['sort_param'] = self::$data[$post_type]['custom_fields'][$field_name]['sort_param'];
			$FieldObj->props 	= $field_data;

			// Any errors?
			if ( !empty($FieldObj->errors) ) {
				$msg = $FieldObj->format_errors();
			}
			// Save;
			else {
				// Unset the old field if the name changed ($field_name is passed via $_GET)
				if ($field_name != $field_data['name']) {
					unset(self::$data[$post_type]['custom_fields'][$field_name]);
				}
				self::$data[$post_type]['custom_fields'][ $field_data['name'] ] = $field_data;
				update_option( self::db_key, self::$data );
				unset($_POST);
				self::set_flash($success_msg);
				self::_page_show_custom_fields($post_type);
				return;
			}

		}

		$fields = $FieldObj->get_edit_field_definition($field_data);

		$submit_link = $tpl = sprintf(
			'?page=%s&%s=9&%s=%s&type=%s'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
			, $field_type
		);
		
		include 'pages/custom_field.php';
	}


	//------------------------------------------------------------------------------
	/**
	 * Manager Page -- called by page_main_controller()
	 * Edit an existing post type. Changing the unique post-type identifier (i.e. name)
	 * is not allowed.
	 * @param string $post_type
	 */
	private static function _page_edit_post_type($post_type) {
		// We can't edit built-in post types
		if (!self::_is_existing_post_type($post_type, false ) ) {
			self::_page_display_error();
			return;
		}

		// Variables for our template
		$page_header  = __('Edit Content Type: ') . $post_type;
		$fields   = '';
		$action_name = 'custom_content_type_mgr_edit_content_type';
		$nonce_name = 'custom_content_type_mgr_edit_content_type_nonce';
		$submit   = __('Save', CCTM_TXTDOMAIN);
		$msg    = '';  // Any validation errors

		$def = CCTM::$data[$post_type];

		// Save data if it was properly submitted
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			$sanitized_vals = self::_sanitize_post_type_def($_POST);

			$error_msg = self::_post_type_name_has_errors($sanitized_vals);

			if ( empty($error_msg) ) {
				self::_save_post_type_settings($sanitized_vals);

				$msg .= '<div class="updated"><p>'
					. sprintf( __('Settings for %s have been updated.', CCTM_TXTDOMAIN )
					, '<em>'.$sanitized_vals['post_type'].'</em>')
					.'</p></div>';
				self::set_flash($msg);

				$msg = '
					<script type="text/javascript">
						window.location.replace("?page='.self::admin_menu_slug.'");
					</script>';
				print $msg;
				return;
			}
			else {
				// clean up... menu labels in particular can get gunked up. :(
				$def  = $sanitized_vals;
				$def['labels']['singular_name'] = '';
				$def['label'] = '';
				$msg = "<div class='error'>$error_msg</div>";
			}		}

		include 'pages/post_type.php';
	}

	//------------------------------------------------------------------------------
	/**
	 * @param	string	local name of the uploaded file 
	 *					(stored in wp-content/uploads/cctm/defs)
	 */
	private static function _page_preview_def($file) {
		require_once('ImportExport.php');
		// Validate: check file name
		if ( !ImportExport::is_valid_basename($file) ) {
			$msg = '<div class="error"><p>'
				. sprintf( 
					__('Bad filename: %s. No special characters or spaces allowed.', CCTM_TXTDOMAIN)
					, '<strong>'.htmlentities($filename).'</strong>'
					)
				.'</p></div>';
			self::set_flash($msg);
			return self::page_import();
		}
		$upload_dir = wp_upload_dir();
		$dir = $upload_dir['basedir'] .'/'.self::base_storage_dir . '/' . self::def_dir;
		
		$data_raw = file_get_contents($dir.'/'.$file);
		$data = json_decode($data_raw, true);

		// Check the contents of the array
		if ( !ImportExport::is_valid_upload_structure($data) ) {
			$msg = '<div class="error"><p>'
				. sprintf( __('%s contained an incompatible data structure.', CCTM_TXTDOMAIN)
					, '<strong>'.htmlentities($file).'</strong>'
					)
				. '</p></div>';
			self::set_flash($msg);
			return self::page_import();
		}

		$settings = get_option(CCTM::db_key_settings, array() );
		$settings['candidate'] = $data;
		update_option(CCTM::db_key_settings, $settings );
		
		return self::page_import();
	}
	
	
	//------------------------------------------------------------------------------
	/**
	Manager Page -- called by page_main_controller()
	Deletes all custom field definitions for a given post_type.
	 * @param string $post_type
	 */
	private static function _page_reset_all_custom_fields($post_type) {
		// We can't delete built-in post types
		if (!self::_is_existing_post_type($post_type, true ) ) {
			self::_page_display_error();
			return;
		}

		// Variables for our template
		$page_header = __('Reset custom field definitions', CCTM_TXTDOMAIN);
		$fields   		= '';
		$action_name 	= 'custom_content_type_mgr_delete_all_custom_fields';
		$nonce_name 	= 'custom_content_type_mgr_delete_all_custom_fields';
		$submit   		= __('Reset', CCTM_TXTDOMAIN);

		// If properly submitted, Proceed with deleting the post type
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {

			unset(self::$data[$post_type]['custom_fields']); // <-- Delete this node of the data structure
			update_option( self::db_key, self::$data );
			$msg = '<div class="updated"><p>'
				.sprintf( __('All custom field definitions for the %s post type have been deleted', CCTM_TXTDOMAIN), "<em>$post_type</em>")
				. '</p></div>';
			self::set_flash($msg);
			self::_page_show_custom_fields($post_type, true);
			return;
		}

		$msg = '<div class="error">
			<img src="'.CCTM_URL.'/images/warning-icon.png" width="50" height="44" style="float:left; padding:10px;"/>
			<p>'
			. sprintf( __('You are about to delete all custom field definitions for the %s post type. This will not delete any data from the wp_postmeta table, but it will make any custom fields invisible to WordPress users on the front and back end.', CCTM_TXTDOMAIN), "<em>$post_type</em>" )
			.'</p>'
			. '<p>'.__('Are you sure you want to do this?', CCTM_TXTDOMAIN).'</p></div>';

		// The URL nec. to take the "Cancel" button back to this page.
		$cancel_target_url = '?page='.self::admin_menu_slug . '&'.self::action_param .'=4&'.self::post_type_param.'='.$post_type;
		
		include 'pages/basic_form.php';

	}

	//------------------------------------------------------------------------------
	/**
	 * Manager Page -- called by page_main_controller()
	 * List all post types (default page)
	 */
	private static function _page_show_all_post_types() {
		$msg = self::get_flash();
		//print $msg; exit; // EHG
		$customized_post_types =  array();
		$displayable_types = array();
		$displayable_types = array();

		if ( !empty(self::$data) ) {
			$customized_post_types =  array_keys(self::$data);
		}
		$displayable_types = array_merge(self::$built_in_post_types , $customized_post_types);
		$displayable_types = array_unique($displayable_types);

		$row_data = '';
		$tpl = file_get_contents(CCTM_PATH.'/tpls/settings/post_type_tr.tpl');
		foreach ( $displayable_types as $post_type ) {
			$hash = array(); // populated for the tpl
			$hash['post_type'] = $post_type;

			// Get our links
			$deactivate    = self::_link_deactivate($post_type);
			$edit_link     = self::_link_edit($post_type);
			$manage_custom_fields  = self::_link_manage_custom_fields($post_type);
			$view_templates   = self::_link_view_sample_templates($post_type);


			$hash['edit_manage_view_links'] = $edit_link . ' | '. $manage_custom_fields . ' | ' . $view_templates;

			if ( isset(self::$data[$post_type]['is_active']) && !empty(self::$data[$post_type]['is_active']) ) {
				$hash['class'] = 'active';
				$hash['activate_deactivate_delete_links'] = '<span class="deactivate">'.$deactivate.'</span>';
				$is_active = true;
			}
			else {
				$hash['class'] = 'inactive';
				$hash['activate_deactivate_delete_links'] = '<span class="activate">'
					. self::_link_activate($post_type) . ' | </span>'
					. '<span class="delete">'. self::_link_delete($post_type).'</span>';
				$is_active = false;
			}

			// Built-in post types use a canned description and override a few other behaviors
			if ( in_array($post_type, self::$built_in_post_types) ) {
				$hash['description']  = __('Built-in post type.', CCTM_TXTDOMAIN);
				$hash['edit_manage_view_links'] = $manage_custom_fields . ' | ' . $view_templates;
				if (!$is_active) {
					$hash['activate_deactivate_delete_links'] = '<span class="activate">'
						. self::_link_activate($post_type) . '</span>';
				}
			}
			// Whereas users define the description for custom post types
			else {
				$hash['description']  = self::_get_value(self::$data[$post_type], 'description');
			}

			// Images
			$hash['icon'] = '';
			switch ($post_type) {
			case 'post':
				$hash['icon'] = '<img src="'. CCTM_URL . '/images/icons/default/post.png' . '" width="15" height="15"/>';
				break;
			case 'page':
				$hash['icon'] = '<img src="'. CCTM_URL . '/images/icons/default/page.png' . '" width="14" height="16"/>';
				break;
			default:
				if ( !empty(self::$data[$post_type]['menu_icon']) && !self::$data[$post_type]['use_default_menu_icon'] ) {
					$hash['icon'] = '<img src="'. self::$data[$post_type]['menu_icon'] . '" />';
				}
				break;
			}
			$row_data .= self::parse($tpl, $hash);
		}

		include 'pages/default.php';
	}

	//------------------------------------------------------------------------------
	/**
	 * Manage custom fields for the given $post_type.
	 *
	 * @param string $post_type
	 * @param boolen $reset true only if we've just reset all custom fields
	 */
	private static function _page_show_custom_fields($post_type, $reset=false) {
		// Validate post type
		if (!self::_is_existing_post_type($post_type) ) {
			self::_page_display_error();
			return;
		}

		$action_name = 'cctm_custom_save_sort_order';
		$nonce_name = 'cctm_custom_save_sort_order_nonce';
		$msg = self::get_flash();

		// Save sort order
		// Validate/Save data if it was properly submitted
		if ( !$reset && !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			$sanitized = array();
			foreach ( self::$data[$post_type]['custom_fields'] as $def_i => $cf ) {
				$name = $cf['name'];
				self::$data[$post_type]['custom_fields'][$def_i]['sort_param'] = (int) $_POST[$name]['sort_param'];
			}

			update_option( self::db_key, self::$data );
			$x = sprintf( __('Sort order has been saved.', CCTM_TXTDOMAIN) );
			$msg .= sprintf('<div class="updated"><p>%s</p></div>', $x);
		}

		// We want to extract a $def for only THIS content_type's custom_fields
		$def = array();
		if ( isset(self::$data[$post_type]['custom_fields']) ) {
			$def = self::$data[$post_type]['custom_fields'];
		}

		$def_cnt = count($def);

		if (!$reset && !$def_cnt ) {
			$x = sprintf( __('The %s post type does not have any custom fields yet.', CCTM_TXTDOMAIN)
				, "<em>$post_type</em>" );
			$y = __('Click one of the buttons below to add a custom field.', CCTM_TXTDOMAIN );
			$msg .= sprintf('<div class="updated"><p>%s %s</p></div>', $x, $y);
		}

		$tpl = '';
		$tpl_file = CCTM_PATH.'/tpls/settings/custom_field_tr.tpl';
		if ( file_exists($tpl_file) ) {
			$tpl = file_get_contents($tpl_file);
		}
		// Sort by sort_param column: (1st input is by reference)
		// Sorting this way destroys the field_name key, so we have to re-establish it.
		usort($def, CCTM::sort_custom_fields('sort_param', 'strnatcasecmp'));
		// Sorting kills the array key, so we have to restore it
		foreach ( $def as $def_id => $d ) {
			$k = $d['name'];
			$def[$k] = $d;
			if ( is_int($def_id) || empty($def_id) ) {
				unset($def[$def_id]); // remove the integer version (used prior to 0.8.8)
			}
		}

		$fields = '';

		foreach ($def as $def_i => $d) {
			$icon_src = self::get_custom_icons_src_dir() . $d['type'].'.png';

			if ( !CCTM::is_valid_img($icon_src) ) {
				$icon_src = self::get_custom_icons_src_dir() . 'default.png';
			}

			$d['icon'] = sprintf('<img src="%s" style="float:left; margin:5px;"/>', $icon_src);

			
			$d['edit'] = __('Edit');
			$d['delete'] = __('Delete');
			$d['edit_field_link'] = sprintf(
				'<a href="?page=%s&%s=11&%s=%s&field=%s&_wpnonce=%s" title="%s">%s</a>'
				, self::admin_menu_slug
				, self::action_param
				, self::post_type_param
				, $post_type
				, $d['name']
				, wp_create_nonce('cctm_edit_field')
				, __('Edit this custom field', CCTM_TXTDOMAIN)
				, __('Edit', CCTM_TXTDOMAIN)
			);
			$d['delete_field_link'] = sprintf(
				'<a href="?page=%s&%s=10&%s=%s&field=%s&_wpnonce=%s" title="%s">%s</a>'
				, self::admin_menu_slug
				, self::action_param
				, self::post_type_param
				, $post_type
				, $d['name']
				, wp_create_nonce('cctm_delete_field')
				, __('Delete this custom field', CCTM_TXTDOMAIN)
				, __('Delete', CCTM_TXTDOMAIN)
			);

			$fields .= self::parse($tpl, $d);
		}

		// Gets a form definition ready for use inside of a JS variable
		// $new_field_def_js = self::_get_javascript_field_defs();
		//  print $new_field_def_js; exit;
		$action_link = sprintf(
			'?page=%s&%s=4&%s=%s'
			, self::admin_menu_slug
			, self::action_param
			, self::post_type_param
			, $post_type
		);
		include 'pages/sortable-list.php';
		//  include('pages/manage_custom_fields.php');
	}


	//------------------------------------------------------------------------------
	/**
	 * Manager Page -- called by page_main_controller()
	 * Show what a single page for this custom post-type might look like.  This is
	 * me throwing a bone to template editors and creators.
	 *
	 * I'm using a tpl and my parse() function because I have to print out sample PHP
	 * code and it's too much of a pain in the ass to include PHP without it executing.
	 *
	 * @param string $post_type
	 */
	private static function _page_sample_template($post_type) {
		// Validate post type
		if (!self::_is_existing_post_type($post_type) ) {
			self::_page_display_error();
			return;
		}

		$current_theme_name = get_current_theme();
		$current_theme_path = get_stylesheet_directory();

		$hash = array();

		$tpl = file_get_contents( CCTM_PATH.'/tpls/samples/single_post.tpl');
		$tpl = htmlentities($tpl);

		$single_page_msg = sprintf( __('WordPress supports a custom theme file for each registered post-type (content-type). Copy the text below into a file named <strong>%s</strong> and save it into your active theme.', CCTM_TXTDOMAIN)
			, 'single-'.$post_type.'.php'
		);
		$single_page_msg .= sprintf( __('You are currently using the %1$s theme. Save the file into the %2$s directory.', CCTM_TXTDOMAIN)
			, '<strong>'.$current_theme_name.'</strong>'
			, '<strong>'.$current_theme_path.'</strong>'
		);

		$def = array();
		if ( isset(self::$data[$post_type]['custom_fields']) ) {
			$def = self::$data[$post_type]['custom_fields'];
		}

		// built-in content types don't verbosely display what fields they display
		/* Array
(
    [product] => Array
        (
            [supports] => Array
                (
                    [0] => title
                    [1] => editor
                    [2] => author
                    [3] => thumbnail
                    [4] => excerpt
                    [5] => trackbacks
                    [6] => custom-fields
                )
*/

		// Check the TYPE of custom field to handle image and relation custom fields.
		// title, author, thumbnail, excerpt
		$custom_fields_str = '';
		$builtin_fields_str = '';
		$comments_str = '';
		// Built-in Fields
		if ( is_array(self::$data[$post_type]['supports']) ) {
			if ( in_array('title', self::$data[$post_type]['supports']) ) {
				$builtin_fields_str .= "\n\t<h1><?php the_title(); ?></h1>\n";
			}
			if ( in_array('editor', self::$data[$post_type]['supports']) ) {
				$builtin_fields_str .= "\n\t\t<?php the_content(); ?>\n";
			}
			if ( in_array('author', self::$data[$post_type]['supports']) ) {
				$builtin_fields_str .= "\n\t\t<?php the_author(); ?>\n";
			}
			if ( in_array('thumbnail', self::$data[$post_type]['supports']) ) {
				$builtin_fields_str .= "\n\t\t<?php the_post_thumbnail(); ?>\n";
			}
			if ( in_array('excerpt', self::$data[$post_type]['supports']) ) {
				$builtin_fields_str .= "\n\t\t<?php the_excerpt(); ?>\n";
			}
			if ( in_array('comments', self::$data[$post_type]['supports']) ) {
				$comments_str .= "\n\t\t<?php comments_template(); ?>\n";
			}
		}

		// Custom fields
		foreach ( $def as $d ) {
			switch ($d['type']) {
			case 'text':
			default:
				$custom_fields_str .= sprintf("\t\t<strong>%s:</strong> <?php print_custom_field('%s'); ?><br />\n", $d['label'], $d['name']);
			}
		}
		// Populate placeholders
		$hash['post_type'] = $post_type;
		$hash['built_in_fields'] = $builtin_fields_str;
		$hash['custom_fields'] = $custom_fields_str;
		$hash['comments'] = $comments_str;

		$single_page_sample_code = self::parse($tpl, $hash);

		include 'pages/sample_template.php';
	}


	//------------------------------------------------------------------------------
	/**
	 * Check for errors: ensure that $post_type is a valid post_type name.
	 *
	 *
	 * @param mixed 	$data describes a post type (this will be input to the register_post_type() function
	 * @param boolean 	$new  (optional) whether or not the post_type is new (default=false)
	 * @return mixed 	returns null if there are no errors, otherwise returns a string describing an error.
	 */
	private static function _post_type_name_has_errors($data, $new=false) {
		$errors = null;

		$taxonomy_names_array = get_taxonomies('', 'names');

		if ( empty($data['post_type']) ) {
			return __('Name is required.', CCTM_TXTDOMAIN);
		}
		if ( empty($data['labels']['menu_name'])) // remember: the location in the $_POST array is different from the name of the option in the form-def.
			{
			return __('Menu Name is required.', CCTM_TXTDOMAIN);
		}

		foreach ( self::$reserved_prefixes as $rp ) {
			if ( preg_match('/^'.preg_quote($rp).'.*/', $data['post_type']) ) {
				return sprintf( __('The post type name cannot begin with %s because that is a reserved prefix.', CCTM_TXTDOMAIN)
					, $rp);
			}
		}


		// Is reserved name?
		if ( in_array($data['post_type'], self::$reserved_post_types) ) {
			$msg = __('Please choose another name.', CCTM_TXTDOMAIN );
			$msg .= ' ';
			$msg .= sprintf( __('%s is a reserved name.', CCTM_TXTDOMAIN )
				, '<strong>'.$post_type.'</strong>' );
			return $msg;
		}
		// Make sure the post-type name does not conflict with any registered taxonomies
		elseif ( in_array( $data['post_type'], $taxonomy_names_array) ) {
			$msg = __('Please choose another name.', CCTM_TXTDOMAIN );
			$msg .= ' ';
			$msg .= sprintf( __('%s is already in use as a registered taxonomy name.', CCTM_TXTDOMAIN)
				, $post_type );
		}
		// If this is a new post_type or if the $post_type name has been changed,
		// ensure that it is not going to overwrite an existing post type name.
		else {
			if ( $new && is_array(self::$data) && in_array($data['post_type'], array_keys(self::$data) ) ) {
				return __('That name is already in use.');
			}
		}

		return; // no errors
	}

	//------------------------------------------------------------------------------
	/**
	 * Sanitize posted data for a clean export.  This just ensures that the user 
	 * has entered some info about what they are about to export.
	 *
	 * @param	mixed	$raw = $_POST data
	 * @return	mixed	sanitized post data
	 */
	private static function _sanitize_export_params($raw) {
		$sanitized = array();
		// title
		if ( empty($raw['title'])) {
			self::$errors['title'] = __('Title is required.', CCTM_TXTDOMAIN);
		}
		elseif ( preg_match('/[^a-z\s\-_0-9]/i', $raw['title']) ) {
			self::$errors['title'] = __('Only basic text characters are allowed.', CCTM_TXTDOMAIN);
		}
		elseif ( strlen($raw['title'] > 64) ) {
			self::$errors['title'] = __('The title cannot exceed 64 characters.', CCTM_TXTDOMAIN);
		}
		
		// author
		if ( empty($raw['author'])) {
			self::$errors['author'] = __('Author is required.', CCTM_TXTDOMAIN);
		}
		elseif ( preg_match('/[^a-z\s\-_0-9]/i', $raw['author']) ) {
			self::$errors['author'] = __('Only basic text characters are allowed.', CCTM_TXTDOMAIN);
		}
		elseif ( strlen($raw['author'] > 64) ) {
			self::$errors['author'] = __('The author name cannot exceed 32 characters.', CCTM_TXTDOMAIN);
		}
		
		if ( empty($raw['url'])) {
			$raw['url'] = site_url(); // defaults to this site
		}
		elseif ( !preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $raw['url']) ) {
			self::$errors['url'] = __('The URL must be in a standard format, e.g. http://yoursite.com.', CCTM_TXTDOMAIN);
		}
		elseif ( strlen($raw['url'] > 255) ) {
			self::$errors['url'] = __('The URL cannot exceed 255 characters.', CCTM_TXTDOMAIN);
		}
		
		if ( empty($raw['description'])) {
			self::$errors['description'] = __('Description is required.', CCTM_TXTDOMAIN);
		}
		elseif ( strlen($raw['description'] > 1024) ) {
			self::$errors['description'] = __('The description cannot exceed 1024 characters.', CCTM_TXTDOMAIN);
		}

		$sanitized['title'] = htmlentities( substr( preg_replace('/[^a-z\s\-_0-9]/i', '', trim($raw['title']) ), 0, 64) );
		$sanitized['author'] = htmlentities( substr( preg_replace('/[^a-z\s\-_0-9]/i', '', trim($raw['author']) ), 0, 64) );
		$sanitized['url'] = htmlentities( substr( trim($raw['url']), 0, 255) );
		$sanitized['description'] = htmlentities( substr( strip_tags( trim($raw['description']) ), 0, 1024) );
		
		return $sanitized;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Every form element when creating a new post type must be filtered here.
	 * 
	 * Problems with:
	 * 	hierarchical
	 * 	rewrite_with_front
	 * 
	 * This is janky... sorta doesn't work how it's supposed when combined with _save_post_type_settings().
	 *
	 *
	 * @param mixed $raw unsanitized $_POST data
	 * @return mixed filtered $_POST data (only white-listed are passed thru to output)
	 */
	private static function _sanitize_post_type_def($raw) {

		unset($raw['custom_content_type_mgr_create_new_content_type_nonce']);
		unset($raw['custom_content_type_mgr_edit_content_type_nonce']);
		
		
		$raw = CCTM::stripalltags_deep(($raw));
//		$raw = CCTM::striptags_deep(($raw));
		if ( get_magic_quotes_gpc() ) {
			$raw = CCTM::stripslashes_deep(($raw));
		}
		
		$sanitized = array();
		// Handle unchecked checkboxes
		if ( empty($raw['cctm_hierarchical_custom'])) {
			$sanitized['cctm_hierarchical_custom'] = '';
		}
		if ( empty($raw['cctm_hierarchical_includes_drafts'])) {
			$sanitized['cctm_hierarchical_includes_drafts'] = '';
		}
		if ( empty($raw['cctm_hierarchical_post_types'])) {
			$sanitized['cctm_hierarchical_post_types'] = array();
		}
		
		// This will be empty if no "supports" items are checked.
		if (!empty($raw['supports']) ) {
			$sanitized['supports'] = $raw['supports'];
			unset($raw['supports']);
		}
		else {
			$sanitized['supports'] = array();
		}

		if (!empty($raw['taxonomies']) ) {
			$sanitized['taxonomies'] = $raw['taxonomies'];
		}
		else {
			// do this so this will take precedence when you merge the existing array with the new one in the _save_post_type_settings() function.
			$sanitized['taxonomies'] = array();
		}
		// You gotta unset these if you want the arrays to passed unmolested.
		unset($raw['taxonomies']);

		// Temporary thing...
		unset($sanitized['rewrite_slug']);
		unset($sanitized['rewrite_with_front']);

		// The main event
		// We grab everything except stuff that begins with '_', then override specific $keys as needed.
		foreach ($raw as $key => $value ) {
			if ( !preg_match('/^_.*/', $key) ) {
				$sanitized[$key] = self::_get_value($raw, $key);
			}
		}

		// Specific overrides below:
		// post_type is the only required field
		$sanitized['post_type'] = self::_get_value($raw, 'post_type');
		$sanitized['post_type'] = strtolower($sanitized['post_type']);
		$sanitized['post_type'] = preg_replace('/[^a-z|_]/', '_', $sanitized['post_type']);
		$sanitized['post_type'] = substr($sanitized['post_type'], 0, 20);

		// Our form passes integers and strings, but WP req's literal booleans,
		// so we do some type-casting here to ensure literal booleans.
		$sanitized['show_ui']     = (bool) self::_get_value($raw, 'show_ui');
		$sanitized['public']     = (bool) self::_get_value($raw, 'public');
		$sanitized['show_in_nav_menus']  = (bool) self::_get_value($raw, 'show_in_nav_menus');
		$sanitized['can_export']    = (bool) self::_get_value($raw, 'can_export');
		$sanitized['use_default_menu_icon'] = (bool) self::_get_value($raw, 'use_default_menu_icon');
		$sanitized['hierarchical']    = (bool) self::_get_value($raw, 'hierarchical');

		if ( empty($sanitized['has_archive']) ) {
			$sanitized['has_archive'] = false;
		}
		else {
			$sanitized['has_archive'] = true;
		}
		
		// *facepalm*... Special handling req'd here for menu_position because 0
		// is handled differently than a literal null.
		if ( (int) self::_get_value($raw, 'menu_position') ) {
			$sanitized['menu_position'] = (int) self::_get_value($raw, 'menu_position', null);
		}
		else {
			$sanitized['menu_position'] = null;
		}

		// menu_icon... the user will lose any custom Menu Icon URL if they save with this checked!
		// TODO: let this value persist.
		if ( $sanitized['use_default_menu_icon'] ) {
			unset($sanitized['menu_icon']); // === null;
		}

		if (empty($sanitized['query_var'])) {
			$sanitized['query_var'] = false;
		}

		// Cleaning up the labels
		if ( empty($sanitized['label']) ) {
			$sanitized['label'] = $sanitized['post_type'];
		}
		if ( empty($sanitized['labels']['singular_name']) ) {
			$sanitized['labels']['singular_name'] = $sanitized['post_type'];
		}
		if ( empty($sanitized['labels']['add_new']) ) {
			$sanitized['labels']['add_new'] = __('Add New');
		}
		if ( empty($sanitized['labels']['add_new_item']) ) {
			$sanitized['labels']['add_new_item'] = __('Add New') . ' ' .$sanitized['post_type'];
		}
		if ( empty($sanitized['labels']['edit_item']) ) {
			$sanitized['labels']['edit_item'] = __('Edit'). ' ' .$sanitized['post_type'];
		}
		if ( empty($sanitized['labels']['new_item']) ) {
			$sanitized['labels']['new_item'] = __('New'). ' ' .$sanitized['post_type'];
		}
		if ( empty($sanitized['labels']['view_item']) ) {
			$sanitized['labels']['view_item'] = __('View'). ' ' .$sanitized['post_type'];
		}
		if ( empty($sanitized['labels']['search_items']) ) {
			$sanitized['labels']['search_items'] = __('Search'). ' ' .$sanitized['labels']['menu_name'];
		}
		if ( empty($sanitized['labels']['not_found']) ) {
			$sanitized['labels']['not_found'] = sprintf( __('No %s found', CCTM_TXTDOMAIN), strtolower($raw['labels']['menu_name']) );
		}		
		if ( empty($sanitized['labels']['not_found_in_trash']) ) {
			$sanitized['labels']['not_found_in_trash'] = sprintf( __('No %s found in trash', CCTM_TXTDOMAIN), strtolower($raw['labels']['menu_name']) );
		}
		if ( empty($sanitized['labels']['parent_item_colon']) ) {
			$sanitized['labels']['parent_item_colon'] = __('Parent Page');
		}


		// Rewrites. TODO: make this work like the built-in post-type permalinks
		switch ($sanitized['permalink_action']) {
		case '/%postname%/':
			$sanitized['rewrite'] = true;
			break;
		case 'Custom':
			$sanitized['rewrite']['slug'] = $raw['rewrite_slug'];
			$sanitized['rewrite']['with_front'] = (bool) $raw['rewrite_with_front'];
			break;
		case 'Off':
		default:
			$sanitized['rewrite'] = false;
		}

		return $sanitized;
	}


	//------------------------------------------------------------------------------
	/**
	OUTPUT: none; this saves a serialized data structure (arrays of arrays) to the db
	 *
	 *
	 * @param mixed $def associative array definition describing a single post-type.
	 * @return 
	 */
	private static function _save_post_type_settings($def) {

		$key = $def['post_type'];
		// Update existing settings if this post-type has already been added
		if ( isset(self::$data[$key]) ) {
			self::$data[$key] = array_merge(self::$data[$key], $def);
		}
		// OR, create a new node in the data structure for our new post-type
		else {
			self::$data[$key] = $def;
		}
		if (self::$data[$key]['use_default_menu_icon']) {
			unset(self::$data[$key]['menu_icon']);
		}

		update_option( self::db_key, self::$data );
	}

	//------------------------------------------------------------------------------
	/**
	 * Used when creating or editing Post Types
	 * I had to put this here in a function rather than in a config file so I could
	 * take advantage of the WP translation functions __()
	 * @param string $post_type_label (optional)
	 */
	private static function _set_post_type_form_definition($post_type_label='sample_post_type') {
		$def = array();
		include 'form_defs/post_type.php';
		self::$post_type_form_definition = $def;
	}


	//! Public Functions
	//------------------------------------------------------------------------------
	/**
	 * Load CSS and JS for admin folks in the manager.  Note that we have to verbosely
	 * ensure that thickbox's css and js are loaded: normally they are tied to the
	 * "editor" area of the content type, so thickbox would otherwise fail
	 * if your custom post_type doesn't use the main editor.
	 * See http://codex.wordpress.org/Function_Reference/wp_enqueue_script for a list
	 * of default scripts bundled with WordPress
	 */
	public static function admin_init() {

		load_plugin_textdomain( CCTM_TXTDOMAIN, '', CCTM_PATH );
		
		wp_register_style('CCTM_css'
			, CCTM_URL . '/css/manager.css');
		wp_enqueue_style('CCTM_css');
		// Hand-holding: If your custom post-type omits the main content block,
		// then thickbox will not be queued.
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_style( 'jquery-ui-tabs', CCTM_URL . '/css/smoothness/jquery-ui-1.8.11.custom.css');
		wp_enqueue_script( 'jquery-ui-tabs');
		wp_enqueue_script( 'jquery-ui-sortable');
		wp_enqueue_script( 'jquery-ui-datepicker', CCTM_URL . '/js/datepicker.js', 'jquery-ui-core');
		wp_enqueue_script( 'jquery-mcolorpicker', CCTM_URL . '/js/mColorPicker.js', 'jquery-ui-core');
		wp_enqueue_script( 'cctm_manager', CCTM_URL . '/js/manager.js' );
	}


	//------------------------------------------------------------------------------
	/**
	 * Adds a link to the settings directly from the plugins page.  This filter is
	 * called for each plugin, so we need to make sure we only alter the links that
	 * are displayed for THIS plugin.
	 * 
	 * INPUTS (determined by WordPress):
	 * @param	array	$links is a hash of links to display in the format of name => translation e.g.
	 * 		array('deactivate' => 'Deactivate')
	 * @param	string	$file is the path to plugin's main file (the one with the info header),
			relative to the plugins directory, e.g. 'custom-content-type-manager/index.php'
	 * @return array $links 
	 */
	public static function add_plugin_settings_link($links, $file) {
		if ( $file == basename(self::get_basepath()) . '/index.php' ) {
			$settings_link = sprintf('<a href="%s">%s</a>'
				, admin_url( 'options-general.php?page='.self::admin_menu_slug )
				, __('Settings')
			);
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	//------------------------------------------------------------------------------
	/**
	 * Create custom post-type menu
	 * To avoid having the 1st submenu page be a duplicate of the parent item,
	 * make the menu_slug equal to the parent menu_slug; however then all incoming 
	 * links are then identified by the same $_GET param. For some reason, this 
	 * causes all my admin pages to print twice.
	 *
	 * See http://codex.wordpress.org/Administration_Menus	 
	 */
	public static function create_admin_menu() {

		// Main menu item
		add_menu_page(
			__('Manage Custom Content Types', CCTM_TXTDOMAIN),  // page title
			__('Custom Content Types', CCTM_TXTDOMAIN),     	// menu title
			'manage_options',       		// capability
			self::admin_menu_slug,      	// menu-slug (should be unique)
			'CCTM::page_main_controller',   // callback function
			CCTM_URL .'/images/gear.png',   // Icon
			71								// menu position
		);
		
		// returns value something like: custom-content-types_page_cctm_ie
		add_submenu_page( 
			self::admin_menu_slug, 					// parent slug (menu-slug from add_menu_page call)
			__('Import Content Type Definition', CCTM_TXTDOMAIN), 	// page title
			__('Import', CCTM_TXTDOMAIN), 			// menu title
			'manage_options', 						// capability
			self::admin_menu_slug .'_i', 			// menu_slug
			'CCTM::page_import' 				// callback function
		);

		add_submenu_page( 
			self::admin_menu_slug, 					// parent slug (menu-slug from add_menu_page call)
			__('Export Content Type Definition', CCTM_TXTDOMAIN), 	// page title
			__('Export', CCTM_TXTDOMAIN), 			// menu title
			'manage_options', 						// capability
			self::admin_menu_slug .'_e', 			// menu_slug
			'CCTM::page_export' 				// callback function
		);


	}
	

	//------------------------------------------------------------------------------
	/**
	 * Handles creation of any directories this plugin writes to the webserver 
	 * file system.
	 * @return 	boolean true if everything is Ok, false if there were errors
	 */
	public static function create_verify_storage_directories() {
		
		$upload_dir = wp_upload_dir();
		$dir = $upload_dir['basedir'] .'/'.self::base_storage_dir . '/' . self::def_dir;

		if ( file_exists($dir) && is_dir($dir) ) {
			return true;
		}
		
		if ( !mkdir ( $dir, self::new_dir_perms, true) ) {
			self::$errors['mkdir'] = '<p>Failed to create the CCTM base storage directory: <code>'.$dir.'</code></p>
				<p><a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Permissions" target="_blank">Click here</a> for more information about correcting permissions errors on your server.</p>';
			return false;
		}
		return true;
	}


	//------------------------------------------------------------------------------
	/**
	 * Custom manipulation of the WHERE clause used by the wp_get_archives() function.
	 * WP deliberately omits custom post types from archive results.
	 *
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=13
	 */		
	public static function get_archives_where_filter( $where , $r ) {
	
		// Get only public, custom post types
		$args = array( 'public' => true, '_builtin' => false ); 		
		$public_post_types = get_post_types( $args );
		
		// Only posts get archives... not pages.
		$search_me_post_types = array('post');
		
		// check which have 'has_archive' enabled.
		foreach (self::$data as $post_type => $def) {
			if ( isset($def['has_archive']) && $def['has_archive'] && in_array($post_type, $public_post_types)) {
					$search_me_post_types[] = $post_type;
			} 
		}
		
		$post_types = "'" . implode( "' , '" , $search_me_post_types ) . "'";
		
		return str_replace( "post_type = 'post'" , "post_type IN ( $post_types )" , $where );
	}


	//------------------------------------------------------------------------------
	/**
	 *  Defines the diretory for this plugin.
	 *
	 * @return string
	 */
	public static function get_basepath() {
		return dirname(dirname(__FILE__));
	}

	//------------------------------------------------------------------------------
	/**
	 * Returns a path with trailing slash.
	 *
	 * @return string
	 */
	public static function get_custom_icons_src_dir() {
		self::$custom_field_icons_dir = CCTM_URL.'/images/custom-fields/';
		return self::$custom_field_icons_dir;
	}

	//------------------------------------------------------------------------------
	/**
	 * Get the flash message (i.e. a message that persists for the current user only
	 * for the next page view). See "Flashdata" here:
	 * http://codeigniter.com/user_guide/libraries/sessions.html
	 *
	 * @return message
	 */
	public static function get_flash() {
		$settings = get_option(self::db_key_settings, array() );
		$output = self::_get_value($settings, 'flash');
		unset( $settings['flash'] );
		update_option(self::db_key_settings, $settings);
		return html_entity_decode($output);
	}

	//------------------------------------------------------------------------------
	/**
	 * Designed to safely retrieve scalar elements out of a hash. Don't use this
	 * if you have a more deeply nested object (e.g. an array of arrays).
	 *
	 * @param array $hash an associative array, e.g. array('animal' => 'Cat');
	 * @param string $key the key to search for in that array, e.g. 'animal'
	 * @param mixed $default (optional) : value to return if the value is not set. Default=''
	 * @return mixed
	 */
	public static function _get_value($hash, $key, $default='') {
		if ( !isset($hash[$key]) ) {
			return $default;
		}
		else {
			if ( is_array($hash[$key]) ) {
				return $hash[$key];
			}
			// Warning: stripslashes was added to avoid some weird behavior
			else {
				return esc_html(stripslashes($hash[$key]));
			}
		}
	}

	//------------------------------------------------------------------------------
	/**
	* Includes the class file for the field type specified by $field_type
	*/
	public static function include_form_element_class($field_type) {
		if (empty($field_type) ) {
			$msg = __('Field type is empty.', CCTM_TXTDOMAIN);
			die($msg);
		}
		
		$element_file = CCTM_PATH.'/includes/elements/'.$field_type.'.php';
		if ( !file_exists($element_file))
		{
			// ERROR!
			$msg = sprintf( __('File not found for %s element: %s', CCTM_TXTDOMAIN) 
				, $field_type
				, $element_file
			);
			die ($msg); //! TODO: print admin notice
		}
		//
		else
		{
			//! TODO: try/catch block
			include_once($element_file);
			if ( !class_exists(self::FormElement_classname_prefix.$field_type) )
			{
				$msg = sprintf( __('Incorrect class name in %s file.', CCTM_TXTDOMAIN)
					, $element_file
				);
				die( $msg );
			}
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Checks whether or not a given post-type is active with its custom fields standardized..
	 * 
	 * @param string $post_type
	 * @return boolean
	 */
	public static function is_active_post_type($post_type) {
		if ( isset(self::$data[$post_type]['is_active']) && self::$data[$post_type]['is_active'] == 1 ) {
			return true;
		}
		else {
			return false;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Used when generating forms. Any non-empty non-zero incoming value will cause
	 * the function to return checked="checked"
	 * @param	mixed	normally a string, but if an array, the 2nd param must be set
	 * @param	string	value to look for inside the $input array. 
	 * @return	string	either '' or 'checked="checked"'
	 */
	public static function is_checked($input, $find_in_array='') {
		if ( is_array($input) ) {
			if ( in_array($find_in_array, $input) ) {
				return 'checked="checked"';			
			}		
		}
		else
		{
			if (!empty($input) && $input!=0) {
				return 'checked="checked"';
			}
		}
		return ''; // default
	}

	//------------------------------------------------------------------------------
	/**
	 * If $option_value == $field_value, then this returns 'selected="selected"'
	 * @param	string	$option_value: the value of the <option> being tested
	 * @param	string	$field_value: the current value of the field
	 * @return	string
	 */
	public static function is_selected($option_value, $field_value) {
		if ( $option_value == $field_value ) {
			return 'selected="selected"';
		}
		return '';
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Using something like the following:
	 *	if (!@fclose(@fopen($src, 'r'))) {
	 *		$src = CCTM_URL.'/images/custom-fields/default.png';
	 *	}
	 * caused segfaults in some server configurations (see issue 60):
	 * http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=60
	 * So in order to check whether an image path is broken or not, we translate the 
	 * $src URL into a local path so we can use humble file_exists() instead.
	 *
	 * This must also be able to handle when WP is installed in a sub directory.
	 *
	 * @param	string	$src a path to an image ON THIS SERVER, e.g. '/wp-content/uploads/img.jpg'
	 *					or 'http://mysite.com/some/img.jpg'
	 * @return	boolean	true if the img is valid, false if the img link is broken
	 */
	public static function is_valid_img($src) {
	
		$info = parse_url($src);
		
		// Bail on malformed URLs
		if (!$info) {
			return false;
		}		
		// Is this image hosted on another server? (currently that's not allowed)
		if ( isset($info['scheme']) ) {
			$this_site_info = parse_url( get_site_url() );
			if ( $this_site_info['scheme'] != $info['scheme'] 
				|| $this_site_info['host'] != $info['host'] 
				|| $this_site_info['port'] != $info['port']) {
				
				return false;
			}
		}
		
		// Gives us something like "/home/user/public_html/blog"
		$ABSPATH_no_trailing_slash = preg_replace('#/$#','', ABSPATH);
		
		// This will tell us whether WP is installed in a subdirectory
		$wp_info = parse_url(site_url());

		// This works when WP is installed @ the root of the site
		if ( !isset($wp_info['path']) ) {
			$path = $ABSPATH_no_trailing_slash . $info['path'];
		}
		// But if WP is installed in a sub dir...
		else {
			$path_to_site_root = preg_replace('#'.preg_quote($wp_info['path']).'$#'
				,''
				, $ABSPATH_no_trailing_slash);
			$path = $path_to_site_root . $info['path'];
		}

		if ( file_exists($path) ) {
			return true;
		}
		else {
			return false;
		}
	}

	//------------------------------------------------------------------------------
	/**
	* This admin menu page handles exporting of the CCTM definition data. 
	*/
	public static function page_export() {

		$settings = get_option(CCTM::db_key_settings, array() );
		$settings['export_info'] = self::_get_value($settings, 'export_info', array() );
		$action_name  = 'custom_content_type_mgr_export_def';
		$nonce_name  = 'custom_content_type_mgr_export_def_nonce';
		$msg = '';
					
		// Save if submitted...
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			// A little cleanup before we sanitize
			unset($_POST[ $nonce_name ]);
			unset($_POST['_wp_http_referer']);

			$posted_data = self::_sanitize_export_params($_POST);
			$settings['export_info'] = $posted_data; // prep for saving.

			// Any errors?
			if ( !empty(self::$errors) ) {
				$msg = self::_format_errors();
			}
			// Save;
			else {
				$title = 'cctm_def';
				if ( !empty($posted_data['title']) ) {
					$title = $posted_data['title'];
					$title = strtolower($title);
					$title = preg_replace('/\s+/', '_', $title); 
					$title = preg_replace('/[^a-z_]/', '', $title); 
				}
				$nonce = wp_create_nonce('cctm_download_definition');
				$msg = sprintf('<div class="updated"><p>%s</p></div>'
				, sprintf(__('Your Custom Content Type definition %s should begin downloading shortly. If the download does not begin, %s', CCTM_TXTDOMAIN)
				, '<storng>'.$title.'.cctm.json</strong>'
				, '<a href="'.CCTM_URL.'/download.php?_wpnonce='.$nonce.'">click here</a>'));

				// Save the options: anything that's in the form is considered a valid "info" key.
				update_option( self::db_key_settings, $settings );

				// Fire off a request to download the file:
				$msg .= sprintf('
					<script type="text/javascript">
						jQuery(document).ready(function() {
							window.location.replace("%s?_wpnonce=%s");
						});
					</script>'
					, CCTM_URL.'/download.php'
					, $nonce );
			}
		}
	
		include(CCTM_PATH.'/includes/pages/export.php');
	}

	//------------------------------------------------------------------------------
	/**
	* Ugh... the structure here sucks... tiered validation is messy
	*/
	public static function page_import() {
		require_once('ImportExport.php');

		$settings = get_option(CCTM::db_key_settings, array() );
		$settings['export_info'] = self::_get_value($settings, 'export_info', array() );
		$action_name  = 'custom_content_type_mgr_import_def';
		$nonce_name  = 'custom_content_type_mgr_import_def_nonce';
		$msg = self::get_flash();
		
		
		// Save if submitted... this is tricky because validation comes in tiers here.
		if ( !empty($_POST) && check_admin_referer($action_name, $nonce_name) ) {
			// A little cleanup before we sanitize
			unset($_POST[ $nonce_name ]);
			unset($_POST['_wp_http_referer']);

			// Start Checking stuff....
			// Big no-no #1: no file 
			if ( empty($_FILES) || empty($_FILES['cctm_settings_file']['tmp_name'])) {
				self::$errors['cctm_settings_file'] = sprintf( 
					__('No file selected', CCTM_TXTDOMAIN)
					, CCTM::max_def_file_size 
				); 
				$msg = self::_format_errors();
				include_once(CCTM_PATH.'/includes/pages/import.php');
				return;
			}
			// Big no-no #2: file is too  big
			if ($_FILES['cctm_settings_file']['size'] > CCTM::max_def_file_size ) {
				self::$errors['cctm_settings_file'] = sprintf( 
					__('The definition filesize must not exceed %s bytes.', CCTM_TXTDOMAIN)
					, CCTM::max_def_file_size 
				); 
				$msg = self::_format_errors();
				include_once(CCTM_PATH.'/includes/pages/import.php');
				return;
			}
			
			// Big no-no #3: bad data structure
			$raw_file_contents = file_get_contents($_FILES['cctm_settings_file']['tmp_name']);
			$data = json_decode( $raw_file_contents, true);

			// Let's check that this thing is legit
			if ( !ImportExport::is_valid_upload_structure($data) ) {
				self::$errors['format'] = __('The uploaded file is not in the correct format.', CCTM_TXTDOMAIN);
				$msg = self::_format_errors();
				include_once(CCTM_PATH.'/includes/pages/import.php');
				return;
			}
			
			// create_verify_storage_directories will set errors, and we add another error here
			// to let the user know that we can't interface with the library dir 
			$basename = basename($_FILES['cctm_settings_file']['name']);
			// Sometimes you can get filenames that look lie "your_def.cctm (1).json"
			if ( !ImportExport::is_valid_basename($basename) ) {
				// grab anything left of the first period, then re-create the .cctm.json extension
				list($basename) = explode('.', $basename);
				$basename .= ImportExport::extension;
			}
			$upload_dir = wp_upload_dir();
			$dir = $upload_dir['basedir'] .'/'.self::base_storage_dir . '/' . self::def_dir;
	
			if ( !self::create_verify_storage_directories() ) {
				self::$errors['library'] = __('We could not upload the definition file to your library.', CCTM_TXTDOMAIN);	
			} 
			elseif ( !move_uploaded_file($_FILES['cctm_settings_file']['tmp_name'], $dir.'/'.$basename )) {
				self::$errors['library'] = __('We could not upload the definition file to your library.', CCTM_TXTDOMAIN);	
			}
		
			// Any errors?  At this point, they aren't deal-breakers.
			if ( !empty(self::$errors) ) {
				$msg = self::_format_errors();
			}

			// Save
			$settings = get_option(self::db_key_settings, array() );
			$settings['candidate'] = $data;
			update_option(self::db_key_settings, $settings);			

		}

		include_once(CCTM_PATH.'/includes/pages/import.php');
	}
	
	//------------------------------------------------------------------------------
	/**
	 * This is the grand poobah of functions for the admin pages: it routes requests 
	 * to specific functions.
	 * This is the function called when someone clicks on the settings page.
	 * The job of a controller is to process requests and route them.
	 *
	 */
	public static function page_main_controller() {
		// TODO: this should be specific to the request
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$action   = (int) self::_get_value($_GET, self::action_param, 0);
		$post_type   = self::_get_value($_GET, self::post_type_param);

		switch ($action) {
		case 1: // create new custom post type
			self::_page_create_post_type();
			break;
		case 2: // update existing custom post type. Override form def.
			self::_page_edit_post_type($post_type);
			break;
		case 3: // delete existing custom post type
			self::_page_delete_post_type($post_type);
			break;
		case 4: // Manage Custom Fields for existing post type
			//self::_page_manage_custom_fields($post_type);
			self::_page_show_custom_fields($post_type);
			break;
		case 5: // TODO: Manage Taxonomies for existing post type (???)
			break;
		case 6: // Activate post type
			self::_page_activate_post_type($post_type);
			break;
		case 7: // Deactivate post type
			self::_page_deactivate_post_type($post_type);
			break;
		case 8: // Show an example of custom field template
			self::_page_sample_template($post_type);
			break;
		case 9: // Create custom field
			$field_type = self::_get_value($_GET, 'type');
			self::_page_create_custom_field($post_type, $field_type);
			break;
		case 10: // Delete custom field
			$field_name = self::_get_value($_GET, 'field');
			self::_page_delete_custom_field($post_type, $field_name);
			break;
		case 11: // Edit custom field
			$field_name = self::_get_value($_GET, 'field');
			self::_page_edit_custom_field($post_type, $field_name);
			break;
		case 12: // Reset all custom fields from this post_type
			self::_page_reset_all_custom_fields($post_type);
			break;
		case 13: // Activate the imported post type def
			self::_page_activate_imported_def();
			break;
		case 14: // Preview an uploaded  post type def
			$file = self::_get_value($_GET, 'file');
			self::_page_preview_def($file);
			break;
		default: // Default: List all post types
			self::_page_show_all_post_types();
		}
	}

	/*------------------------------------------------------------------------------
	SYNOPSIS: a simple parsing function for basic templating.
	INPUT:
		$tpl (str): a string containing [+placeholders+]
		$hash (array): an associative array('key' => 'value');
	OUTPUT
		string; placeholders corresponding to the keys of the hash will be replaced
		with the values and the string will be returned.
	------------------------------------------------------------------------------*/
	public static function parse($tpl, $hash) 
	{
	
	    foreach ($hash as $key => $value) 
	    {
	    	if ( !is_array($value) )
	    	{
	        	$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
	        }
	    }
	    
	    // Remove any unparsed [+placeholders+]
	    $tpl = preg_replace('/\[\+(.*?)\+\]/', '', $tpl);
	    
	    return $tpl;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Print errors if they were thrown by the tests. Currently this is triggered as
	 * an admin notice so as not to disrupt front-end user access, but if there's an
	 * error, you should fix it! The plugin may behave erratically!
	 * INPUT: none... ideally I'd pass this a value, but the WP interface doesn't make
	 * 	this easy, so instead I just read the class variable: CCTMtests::$errors
	 * 	
	 * @return	none  But errors are printed if present.
	 */
	public static function print_notices() {
		if ( !empty(CCTMtests::$errors) ) {
			$error_items = '';
			foreach ( CCTMtests::$errors as $e ) {
				$error_items .= "<li>$e</li>";
			}
			$msg = sprintf( __('The %s plugin encountered errors! It cannot load!', CCTM_TXTDOMAIN)
				, CCTM::name);
			printf('<div id="custom-post-type-manager-warning" class="error">
				<p>
					<strong>%1$s</strong>
					<ul style="margin-left:30px;">
						%2$s
					</ul>
				</p>
				</div>'
				, $msg
				, $error_items);
		}
	}


	//------------------------------------------------------------------------------
	/**
	Register custom post-types, one by one. Data is stored in the wp_options table
	in a structure that matches exactly what the register_post_type() function
	expectes as arguments.

	See:
	http://codex.wordpress.org/Function_Reference/register_post_type

	See wp-includes/posts.php for examples of how WP registers the default post types

	$def = Array
	(
	    'supports' => Array
	        (
	            'title',
	            'editor'
	        ),

	    'post_type' => 'book',
	    'singular_label' => 'Book',
	    'label' => 'Books',
	    'description' => 'What I&#039;m reading',
	    'show_ui' => 1,
	    'capability_type' => 'post',
	    'public' => 1,
	    'menu_position' => '10',
	    'menu_icon' => '',
	    'custom_content_type_mgr_create_new_content_type_nonce' => 'd385da6ba3',
	    'Submit' => 'Create New Content Type',
	    'show_in_nav_menus' => '',
	    'can_export' => '',
	    'is_active' => 1,
	);

	FUTURE??:
		register_taxonomy( $post_type,
			$cpt_post_types,
			array( 'hierarchical' => get_disp_boolean($cpt_tax_type["hierarchical"]),
			'label' => $cpt_label,
			'show_ui' => get_disp_boolean($cpt_tax_type["show_ui"]),
			'query_var' => get_disp_boolean($cpt_tax_type["query_var"]),
			'rewrite' => array('slug' => $cpt_rewrite_slug),
			'singular_label' => $cpt_singular_label,
			'labels' => $cpt_labels
		) );
	*/
	public static function register_custom_post_types() {
	
		if ( is_array(self::$data) ) {
			foreach (self::$data as $post_type => $def) {
				if ( isset($def['is_active'])
					&& !empty($def['is_active'])
					&& !in_array($post_type, self::$built_in_post_types)) {

					register_post_type( $post_type, $def );
					// TODO: make global setting that asks whether or not the user wants us to do this automatically
					//if ( is_array($def['supports']) && in_array('thumbnail', $def['supports']) )
					//{
					/* This generates a warning:
						Warning: in_array() [function.in-array]: Wrong datatype for second argument in /Users/everett2/Sites/pretasurf/html/blog/wp-includes/theme.php on line 1671 */
					// add_theme_support( 'post-thumbnails', $post_type );
					// it probably needs to go in a different action
					//}
				}
			}
			// Added per issue 50
			// http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=50
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}

	}

	//------------------------------------------------------------------------------
	/**
	 * This filters the basic page lookup so URLs like http://mysite.com/archives/date/2010/11
	 * will return custom post types.
	 * See issue 13 for full archive suport:
	 * http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=13
	 * and http://bajada.net/2010/08/31/custom-post-types-in-the-loop-using-request-instead-of-pre_get_posts
	 */
	public static function request_filter( $query ) {

		// This is a troublesome little query... we need to monkey with it so WP will play nice with
		// custom post types, but if you breathe on it wrong, chaos ensues.
		// see http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=108
		// and http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=111
		// and http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=112
		if ( empty($query) 
			|| isset($query['pagename']) 
			|| isset($query['preview']) 
			|| isset($query['feed']) 
			|| isset($query['page_id'])
			|| !empty($query['post_type']) ) {
			
			return $query;
		}

		// Get only public, custom post types
		$args = array( 'public' => true, '_builtin' => false ); 		
		$public_post_types = get_post_types( $args );


		// Categories can apply to posts and pages
		$search_me_post_types = array('post','page');		
		if ( isset($query['category_name']) ) {
			foreach ($public_post_types as $pt => $tmp) {
				$search_me_post_types[] = $pt;
			}
			$query['post_type'] = $search_me_post_types;
			return $query;
		}
		
		// Only posts get archives... not pages.
		$search_me_post_types = array('post');		
		
		// check which have 'has_archive' enabled.
		foreach (self::$data as $post_type => $def) {
			if ( isset($def['has_archive']) && $def['has_archive'] && in_array($post_type, $public_post_types)) {
					$search_me_post_types[] = $post_type;
			} 
		}

		$query['post_type'] = $search_me_post_types;
		
		return $query;
	}

	//------------------------------------------------------------------------------
	/**
	 * Sets a flash message that's viewable only for the next page view (for the current user)
	 * $_SESSION doesn't work b/c WP doesn't natively support them = lots of confused users.
	 * setcookie() won't work b/c WP has already sent header info.
	 * So instead, we store this stuff in the database. Sigh.
	 * 
	 * @param string $msg text or html message
	 */
	public static function set_flash($msg) {
		$settings = get_option(self::db_key_settings, array() );
		$settings['flash'] = $msg;
		update_option(self::db_key_settings, $settings);
	}


	//------------------------------------------------------------------------------
	/**
	 * Used by php usort to sort custom field defs by their sort_param attribute
	 *
	 * @param string $field
	 * @param string $sortfunc
	 * @return array
	 */
	public static function sort_custom_fields($field, $sortfunc) {
		return create_function('$var1, $var2', 'return '.$sortfunc.'($var1["'.$field.'"], $var2["'.$field.'"]);');
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Recursively removes all quotes from $_POSTED data if magic quotes are on
	 * http://algorytmy.pl/doc/php/function.stripslashes.php
	 *
	 * @param	array	possibly nested 
	 * @return	array	clensed of slashes
	 */
	public static function stripslashes_deep($value)
	{
		if ( is_array($value) ) {
			$value = array_map( 'CCTM::'. __FUNCTION__, $value);    
		}
		else {
			$value = stripslashes($value);
		}
	   return $value;
	}

	//------------------------------------------------------------------------------
	/**
	 * Recursively strips ALL tags from all inputs, including nested ones.
	 *
	 * @param	array	usually the $_POST array or a copy of it
	 * @return	array	the input array, with tags stripped out of each value.
	 */
	public static function stripalltags_deep($value)
	{
		if ( is_array($value) ) {
			$value = array_map('CCTM::'. __FUNCTION__, $value);    
		}
		else {
			$value = strip_tags($value);
		}
	   return $value;
	}

	//------------------------------------------------------------------------------
	/**
	 * Recursively strips tags from all inputs, including nested ones.
	 *
	 * @param	array	usually the $_POST array or a copy of it
	 * @return	array	the input array, with tags stripped out of each value.
	 */
	public static function striptags_deep($value)
	{
		if ( is_array($value) ) {
			$value = array_map('CCTM::'. __FUNCTION__, $value);    
		}
		else {
			$value = strip_tags($value, self::$allowed_html_tags);
		}
	   return $value;
	}

}

/*EOF CCTM.php*/