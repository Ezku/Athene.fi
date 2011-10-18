<?php
/*------------------------------------------------------------------------------
CCTM = Custom Content Type Manager

This is the main class for the Custom Content Type Manager plugin.
It holds its functions hooked to WP events and utilty functions and configuration
settings.

Homepage:
http://code.google.com/p/wordpress-custom-content-type-manager/

This plugin handles the creation and management of custom post-types (also
referred to as 'content-types'). 
------------------------------------------------------------------------------*/
class CCTM {
	// Name of this plugin and version data.
	// See http://php.net/manual/en/function.version-compare.php:
	// any string not found in this list < dev < alpha =a < beta = b < RC = rc < # < pl = p
	const name   = 'Custom Content Type Manager';
	const version = '0.9.4.4';
	const version_meta = 'pl'; // dev, rc (release candidate), pl (public release)
	
	
	// Required versions (referenced in the CCTMtest class).
	const wp_req_ver  = '3.0.1';
	const php_req_ver  = '5.2.6';
	const mysql_req_ver = '4.1.2';
	
	/**
	 * The following constants identify the option_name in the wp_options table
	 * where this plugin stores various data.
	 */	 
	const db_key  = 'cctm_data';

	/**
	 * Determines where the main CCTM menu appears. WP is vulnerable to conflicts 
	 * with menu items, so the parameter is listed here for easier editing.
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=203
	 */
	const menu_position = 71;

	// Each class that extends either the CCTMFormElement class or the 
	// the CCTMOutputFilter class must prefix this to its class name.
	const classname_prefix = 'CCTM_';

	// used to control the uploading of the .cctm.json files
	const max_def_file_size = 524288; // in bytes
	
	// Directory relative to wp-content/uploads/ where we can store def files
	// Omit the trailing slash.
	const base_storage_dir = 'cctm';
	
	/**
	 * Directory relative to wp-content/uploads/{self::base_storage_dir} used to store 
	 * the .cctm.json definition files. Omit the trailing slash.
	 */ 
	const def_dir = 'defs';
	
	/**
	 * Directory relative to wp-content/uploads/{self::base_storage_dir} used to store 
	 * any 3rd-party or custom custom field types. Omit the trailing slash.
	 */ 
	const custom_fields_dir = 'fields';
	
	/**
	 * Directory relative to wp-content/uploads/{self::base_storage_dir} used to store 
	 * any 3rd-party or output filters. Omit the trailing slash.
	 */
	const filters_dir = 'filters';
	
	/**
	 * Directory relative to wp-content/uploads/{self::base_storage_dir} used to store
	 * formatting templates (tpls)
	 * May contain the following sub directories: fields, fieldtypes, metaboxes
	 */
	const tpls_dir = 'tpls';
	
	// Default permissions for dirs/files created in the base_storage_dir.
	// These cannot be more permissive thant the system's settings: the system
	// will automatically shave them down. E.g. if the system has a global setting
	// of 0755, a local setting here of 0770 gets bumped down to 0750.
	const new_dir_perms = 0755;
	const new_file_perms = 0644;

	// Used to filter inputs (e.g. descriptions)
	public static $allowed_html_tags = '<a><strong><em><code><style>';
		
	// Data object stored in the wp_options table representing all primary data
	// for post_types and custom fields
	public static $data = array();
	
	// integer iterator used to uniquely identify groups of field definitions for
	// CSS and $_POST variables
	public static $def_i = 0;

	// This is the definition shown when a user first creates a post_type
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
		    'public' => 1, // 0.9.4.2 tried to set this verbosely, but WP still req's this attribute
		    'menu_icon' => '',
		    'label' => '',
		    'menu_position' => '',
		    'show_in_menu' => 1,
		    
		    'rewrite_with_front' => 1,
		    'permalink_action' => 'Off',
		    'rewrite_slug' => '',
		    'query_var' => '',
		    'capability_type' => 'post',
		    'show_in_nav_menus' => 1,
		    'publicly_queryable' => 1,
		    'include_in_search' => 1,	// this makes more sense to users than the exclude_from_search,
		    'exclude_from_search' => 0, // but this is what register_post_type expects. Boo.
		    'include_in_rss' => 1,		// this is a custom option
		    'can_export' => 1,
		    'use_default_menu_icon' => 1,
		    'hierarchical' => 0,
		    'rewrite' => '',
		    'has_archive' => 0,
		    'custom_order' => 'ASC',
		    'custom_orderby' => '',
		);

	/**
	 * List default settings here.
	 */
	public static $default_settings = array(
		'delete_posts' => 0
		, 'delete_custom_fields' => 0
		, 'add_custom_fields' => 0
		, 'update_custom_fields' => 0
	 	, 'show_custom_fields_menu' => 1
	 	, 'show_settings_menu' => 1
	 	, 'show_foreign_post_types' => 1
	 	, 'cache_directory_scans'	=> 1	
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

	/**
	 * Warnings are stored as a simple array of text strings, e.g. 'You spilled your coffee!'
	 * Whether or not they are displayed is determined by checking against the self::$data['warnings']
	 * array: the text of the warning is hashed and this is used as a key to identify each warning.
	 */
	public static $warnings = array();
	
	/** 
	 * used to store validation errors. The errors take this format: 
	 * self::$errors['field_name'] = 'Description of error';
	 */
	public static $errors; 


	//! Private Functions
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
	 *
	 *
	 * @return string representing all img tags for all post-type icons
	 */
	private static function _get_post_type_icons() {

		$icons = array();
		if ($handle = opendir(CCTM_PATH.'/images/icons/16x16')) {
			while (false !== ($file = readdir($handle))) {
				if ( !preg_match('/^\./', $file) ) {
					$icons[] = $file;
				}
			}
			closedir($handle);
		}

		$output = '';

		foreach ( $icons as $img ) {
			$output .= sprintf('
				<span class="cctm-icon">
					<img src="%s" title="%s" onclick="javascript:send_to_menu_icon(\'%s\');"/>
				</span>'
				, CCTM_URL.'/images/icons/32x32/'.$img
				, $img
				, CCTM_URL.'/images/icons/16x16/'.$img
			);
		}

		return $output;
	}

	//------------------------------------------------------------------------------
	/**
	 * SYNOPSIS: checks the custom content data array to see $post_type exists as one 
	 * of CCTM's defined post types (it doesn't check against post types defined 
	 *	elsewhwere).
	 *	
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/wiki/DataStructures
	 *
	 * Built-in post types 'page' and 'post' are considered valid (i.e. existing) by
	 * default, even if they haven't been explicitly defined for use by this plugin
	 * so long as the 2nd argument, $search_built_ins, is not overridden to false.
	 * We do this because sometimes we need to consider posts and pages, and other times
	 * not.
	 *
	 * @param string $post_type	the lowercase database slug identifying a post type.
	 * @param boolean $search_built_ins (optional) whether or not to search inside the
			$built_in_post_types array.
	 * @return boolean indicating whether this is a valid post-type
	 */
	private static function _is_existing_post_type($post_type, $search_built_ins=true) {
	
		// If there is no existing data, check against the built-ins
		if ( empty(self::$data['post_type_defs']) && $search_built_ins ) {
			return in_array($post_type, self::$built_in_post_types);
		}
		// If there's no existing $data and we omit the built-ins...
		elseif ( empty(self::$data['post_type_defs']) && !$search_built_ins ) {
			return false;
		}
		// Check to see if we've stored this $post_type before
		elseif ( array_key_exists($post_type, self::$data['post_type_defs']) ) {
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

	//------------------------------------------------------------------------------
	/**
	 * Check for errors: ensure that $post_type is a valid post_type name.
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

		$registered_post_types = get_post_types();
		$cctm_post_types = array_keys(self::$data['post_type_defs']);
		$other_post_types = array_diff($registered_post_types, $cctm_post_types);
		$other_post_types = array_diff($other_post_types, self::$reserved_post_types);
		
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
			return $msg;
		}
		// If this is a new post_type or if the $post_type name has been changed,
		// ensure that it is not going to overwrite an existing post type name.
		elseif ( $new && is_array(self::$data['post_type_defs']) && in_array($data['post_type'], $cctm_post_types ) ) {
			return sprintf( __('The name %s is already in use.', CCTM_TXTDOMAIN), htmlentities($data['post_type']) );
		}
		// Is the name taken by an existing post type registered by some other plugin?
		elseif (in_array($data['post_type'], $other_post_types) ) {
			return sprintf( __('The name %s has been registered by some other plugin.', CCTM_TXTDOMAIN), htmlentities($data['post_type']) );
		}
		// Make sure there's not an unsuspecting theme file named single-my_post_type.php
/*
		$dir = get_stylesheet_directory();
		if ( file_exists($dir . '/single-'.$data['post_type'].'.php')) {
			return sprintf( __('There is a template file named single-%s.php in your theme directory (%s).', CCTM_TXTDOMAIN)
				, htmlentities($data['post_type']) 
				, get_stylesheet_directory());
		}
*/
		
		return; // no errors
	}

	//------------------------------------------------------------------------------
	/**
	 * Prepare a post type definition for registration.  This gets run immediatlye before
	 * the register_post_type() function is called.  It allows us to abstract the 
	 * stored definition from what gets passed to the WP function.
	 *
	 * @param	mixed 	the CCTM definition for a post type
	 * @return	mixed 	the WordPress authorized definition format.
	 */
	private static function _prepare_post_type_def($def) {			
		// Sigh... working around WP's irksome inputs
		if (isset($def['cctm_show_in_menu']) && $def['cctm_show_in_menu'] == 'custom') {
			$def['show_in_menu'] = $def['cctm_show_in_menu_custom'];
		}
		else {
			$def['show_in_menu'] = (bool) self::get_value($def,'cctm_show_in_menu');
		}
		// We display "include" type options to the user, and here on the backend 
		// we swap this for the "exclude" option that the function requires.
		$def['exclude_from_search'] = !(bool) self::get_value($def,'include_in_search');

		// retro-support... if public is checked, then the following options are inferred
		if (isset($def['public']) && $def['public']) {
			$def['publicly_queriable'] = true;
			$def['show_ui'] = true;
			$def['show_in_nav_menus'] = true;
			$def['exclude_from_search'] = false;
		}
		// Verbosely check to see if "public" is inferred
		if (isset($def['publicly_queriable']) && $def['publicly_queriable']
			&& isset($def['show_ui']) && $def['show_ui']
			&& isset($def['show_in_nav_menus']) && $def['show_in_nav_menus']
			&& (!isset($def['exclude_from_search']) || (isset($def['exclude_from_search']) && !$def['publicly_queriable']))
		) 
		{
			$def['public'] = true;
		}
		
		unset($def['custom_orderby']);
			
			
		return $def;
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
		
		
		$raw = CCTM::striptags_deep(($raw));

		// WP always adds slashes: see http://kovshenin.com/archives/wordpress-and-magic-quotes/
		$raw = CCTM::stripslashes_deep(($raw));
		
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
		// You gotta unset arrays if you want the foreach thing below to work.
		unset($raw['taxonomies']);

		// Temporary thing... ????
		unset($sanitized['rewrite_slug']);

		// The main event
		// We grab everything except stuff that begins with '_', then override specific $keys as needed.
		foreach ($raw as $key => $value ) {
			if ( !preg_match('/^_.*/', $key) ) {
				$sanitized[$key] = self::get_value($raw, $key);
			}
		}

		// Specific overrides below:
		// post_type is the only required field
		$sanitized['post_type'] = self::get_value($raw, 'post_type');
		$sanitized['post_type'] = strtolower($sanitized['post_type']);
		$sanitized['post_type'] = preg_replace('/[^a-z0-9_\-]/', '_', $sanitized['post_type']);
		$sanitized['post_type'] = substr($sanitized['post_type'], 0, 20);

		// Our form passes integers and strings, but WP req's literal booleans,
		// so we do some type-casting here to ensure literal booleans.
		$sanitized['public']    = (bool) self::get_value($raw, 'public');
		$sanitized['rewrite_with_front']     = (bool) self::get_value($raw, 'rewrite_with_front');
		$sanitized['show_ui']     = (bool) self::get_value($raw, 'show_ui');
		$sanitized['public']     = (bool) self::get_value($raw, 'public');
		$sanitized['show_in_nav_menus']  = (bool) self::get_value($raw, 'show_in_nav_menus');
		$sanitized['can_export']    = (bool) self::get_value($raw, 'can_export');
		$sanitized['use_default_menu_icon'] = (bool) self::get_value($raw, 'use_default_menu_icon');
		$sanitized['hierarchical']    = (bool) self::get_value($raw, 'hierarchical');
		$sanitized['include_in_search']    = (bool) self::get_value($raw, 'include_in_search');
		$sanitized['publicly_queryable']    = (bool) self::get_value($raw, 'publicly_queryable');
		$sanitized['include_in_rss']    = (bool) self::get_value($raw, 'include_in_rss');

		if ( empty($sanitized['has_archive']) ) {
			$sanitized['has_archive'] = false;
		}
		else {
			$sanitized['has_archive'] = true;
		}
		
		// *facepalm*... Special handling req'd here for menu_position because 0
		// is handled differently than a literal null.
		if ( (int) self::get_value($raw, 'menu_position') ) {
			$sanitized['menu_position'] = (int) self::get_value($raw, 'menu_position', null);
		}
		else {
			$sanitized['menu_position'] = null;
		}
		$sanitized['show_in_menu']    = self::get_value($raw, 'show_in_menu');	

		$sanitized['cctm_show_in_menu']    = self::get_value($raw, 'cctm_show_in_menu');	


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
			$sanitized['label'] = ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['singular_name']) ) {
			$sanitized['labels']['singular_name'] = ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['add_new']) ) {
			$sanitized['labels']['add_new'] = __('Add New');
		}
		if ( empty($sanitized['labels']['add_new_item']) ) {
			$sanitized['labels']['add_new_item'] = __('Add New') . ' ' .ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['edit_item']) ) {
			$sanitized['labels']['edit_item'] = __('Edit'). ' ' .ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['new_item']) ) {
			$sanitized['labels']['new_item'] = __('New'). ' ' .ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['view_item']) ) {
			$sanitized['labels']['view_item'] = __('View'). ' ' .ucfirst($sanitized['post_type']);
		}
		if ( empty($sanitized['labels']['search_items']) ) {
			$sanitized['labels']['search_items'] = __('Search'). ' ' .ucfirst($sanitized['labels']['menu_name']);
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
	 * this saves a serialized data structure (arrays of arrays) to the db
	 *
	 * @param mixed $def associative array definition describing a single post-type.
	 * @return 
	 */
	private static function _save_post_type_settings($def) {

		$key = $def['post_type'];
		
		unset(self::$data['post_type_defs'][$key]['original_post_type_name']);
		
		// Update existing settings if this post-type has already been added
		if ( isset(self::$data['post_type_defs'][$key]) ) {
			self::$data['post_type_defs'][$key] = array_merge(self::$data['post_type_defs'][$key], $def);
		}
		// OR, create a new node in the data structure for our new post-type
		else {
			self::$data['post_type_defs'][$key] = $def;
		}
		if (self::$data['post_type_defs'][$key]['use_default_menu_icon']) {
			unset(self::$data['post_type_defs'][$key]['menu_icon']);
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
	
		load_plugin_textdomain( CCTM_TXTDOMAIN, false, CCTM_PATH.'/lang/' );

		$file = substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'],'/')+1);
		$page = self::get_value($_GET,'page');
		
		// Only add our junk if we are creating/editing a post or we're on 
		// on of our CCTM pages
		if ( in_array($file, array('post.php','post-new.php','edit.php')) || preg_match('/^cctm.*/', $page) ) {
			
			wp_register_style('CCTM_css', CCTM_URL . '/css/manager.css');
			wp_enqueue_style('CCTM_css');
			// Hand-holding: If your custom post-type omits the main content block,
			// then thickbox will not be queued and your image, reference, selectors will fail.
			// Also, we have to fix the bugs with WP's thickbox.js, so here we include a patched file.
			wp_register_script('cctm_thickbox', CCTM_URL . '/js/thickbox.js', array('thickbox') );
			wp_enqueue_script('cctm_thickbox');
			wp_enqueue_style('thickbox' );
	
			wp_enqueue_style('jquery-ui-tabs', CCTM_URL . '/css/smoothness/jquery-ui-1.8.11.custom.css');
			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-dialog');
	
			wp_enqueue_script('cctm_manager', CCTM_URL . '/js/manager.js' );			
		}
		
		// Allow each custom field to load up any necessary CSS/JS.
		self::initialize_custom_fields();
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
				, admin_url( 'admin.php?page=cctm' )
				, __('Settings')
			);
			array_unshift( $links, $settings_link );
		}

		return $links;
	}
		
	//------------------------------------------------------------------------------
	/**
	 * Solves the problem with encodings.  On many servers, the following won't work:
	 *
	 * 		print 'ę'; // prints Ä™
	 *
	 * But this function solves it by converting the characters into appropriate html-entities: 
	 *
	 * 		print charset_decode_utf_8('ę');
	 *
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=88
	 * Solution from Squirrelmail, see http://pa2.php.net/manual/en/function.utf8-decode.php
	 */
	public static function charset_decode_utf_8($string) { 
		$string = htmlspecialchars($string); // htmlentities will NOT work here.
		
		/* Only do the slow convert if there are 8-bit characters */ 
		/* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */ 
		if (! preg_match("/[\200-\237]/", $string) and ! preg_match("/[\241-\377]/", $string)) {
			return $string;
		}
		
		// decode three byte unicode characters 
		$string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e","'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'",$string); 
		
		// decode two byte unicode characters 
		$string = preg_replace("/([\300-\337])([\200-\277])/e", "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", $string); 
		
		return $string; 
	}

	//------------------------------------------------------------------------------
	/**
	 * WordPress lacks an "onUpdate" event, so this is a home-rolled way I can run
	 * a specific bit of code when a new version of the plugin is installed. The way
	 * this works is the names of all files inside of the updates/ folder are loaded
	 * into an array, e.g. 0.9.4, 0.9.5.  When the first new page request comes through
	 * WP, the database is in the old state, whereas the code is new, so the database
	 * will say e.g. that the plugin version is 0.1 and the code will say the plugin version
	 * is 0.2.  All the available updates are included and their contents are executed 
	 * in order.  This ensures that all update code is run sequentially.
	 *
	 * Any version prior to 0.9.4 is considered "version 0" by this process.
	 *
	 */
	public static function check_for_updates() {
		// check to see if it's a new install and not an update
		/*
if ( empty(self::$data) ) {
			self::$data['cctm_installation_timestamp'] = time();
			update_option( self::db_key, self::$data );
			return;
		}
*/
//		print self::get_stored_version();
//		print self::get_current_version();

		// if it's not a new install, we check for updates
		if ( version_compare( self::get_stored_version(), self::get_current_version(), '<' ) ) 
		{	
			// set the flag
			define('CCTM_UPDATE_MODE', 1);
			// Load up available updates in order (scandir will sort the results automatically)
			$updates = scandir(CCTM_PATH.'/updates');
			foreach ($updates as $file) {
				// Skip the gunk
				if ($file === '.' || $file === '..') continue;
				if (is_dir(CCTM_PATH.'/updates/'.$file)) continue;
				if (substr($file, 0, 1) == '.')	continue;
				// skip non-php files
				if (pathinfo(CCTM_PATH.'/updates/'.$file, PATHINFO_EXTENSION) != 'php') continue;

				// We don't want to re-run older updates
				$this_update_ver = substr($file,0,-4);	
				if ( version_compare( self::get_stored_version(), $this_update_ver, '<' ) ) 
				{
					// Run the update by including the file
					include(CCTM_PATH.'/updates/'.$file);
					// timestamp the update
					self::$data['cctm_update_timestamp'] = time(); // req's new data structure
					// store the new version after the update
					self::$data['cctm_version'] = $this_update_ver; // req's new data structure
					update_option( self::db_key, self::$data );
				}
			}
		}
		
		// If this is empty, then it is a first install, so we timestamp it
		// and prep the data structure
		if (empty(CCTM::$data)) {
			CCTM::$data['cctm_installation_timestamp'] = time();
			CCTM::$data['cctm_version'] = CCTM::get_current_version();
			CCTM::$data['export_info'] = array(
				'title' 		=> 'CCTM Site',
				'author' 		=> get_option('admin_email',''),
				'url' 			=> get_option('siteurl','http://wpcctm.com/'),
				'description'	=> __('This site was created in part using the Custom Content Type Manager', CCTM_TXTDOMAIN),
			);		
			update_option(CCTM::db_key, CCTM::$data);
		}		
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
		$active_post_types = self::get_active_post_types();

		// Main menu item
		add_menu_page(
			__('Manage Custom Content Types', CCTM_TXTDOMAIN),  // page title
			__('Custom Content Types', CCTM_TXTDOMAIN),     	// menu title
			'manage_options',       							// capability
			'cctm',												// menu-slug (should be unique)
			'CCTM::page_main_controller',   					// callback function
			CCTM_URL .'/images/gear.png',   					// Icon
			self::menu_position									// menu position
		);

		add_submenu_page( 
			'cctm', 									// parent slug (menu-slug from add_menu_page call)
			__('CCTM Custom Fields', CCTM_TXTDOMAIN), 	// page title
			__('Custom Fields', CCTM_TXTDOMAIN), 		// menu title
			'manage_options', 							// capability
			'cctm_fields', 								// menu_slug: cf = custom fields
			'CCTM::page_main_controller' 				// callback function
		);
		
		add_submenu_page( 
			'cctm', 								// parent slug (menu-slug from add_menu_page call)
			__('CCTM Global Settings', CCTM_TXTDOMAIN), 	// page title
			__('Global Settings', CCTM_TXTDOMAIN), 		// menu title
			'manage_options', 						// capability
			'cctm_settings', 						// menu_slug
			'CCTM::page_main_controller' 			// callback function
		);
		
/*
		add_submenu_page( 
			'cctm', 								// parent slug (menu-slug from add_menu_page call)
			__('CCTM Themes', CCTM_TXTDOMAIN), 		// page title
			__('Themes', CCTM_TXTDOMAIN), 			// menu title
			'manage_options', 						// capability
			'cctm_themes',  						// menu_slug
			'CCTM::page_main_controller' 			// callback function
		);
*/
		
		add_submenu_page( 
			'cctm', 								// parent slug (menu-slug from add_menu_page call)
			__('CCTM Tools', CCTM_TXTDOMAIN), 		// page title
			__('Tools', CCTM_TXTDOMAIN), 			// menu title
			'manage_options', 						// capability
			'cctm_tools', 							// menu_slug
			'CCTM::page_main_controller' 			// callback function
		);
				
/*
		add_submenu_page( 
			'cctm', 								// parent slug (menu-slug from add_menu_page call)
			__('CCTM Information', CCTM_TXTDOMAIN), // page title
			__('Info', CCTM_TXTDOMAIN), 			// menu title
			'manage_options', 						// capability
			'cctm_info', 							// menu_slug
			'CCTM::page_main_controller' 			// callback function
		);
*/

/*
		add_submenu_page( 
			'cctm',				 					// parent slug (menu-slug from add_menu_page call)
			__('CCTM Store', CCTM_TXTDOMAIN), 		// page title
			__('Store', CCTM_TXTDOMAIN), 			// menu title
			'manage_options', 						// capability
			'cctm_store', 							// menu_slug
			'CCTM::page_main_controller' 			// callback function
		);

		add_submenu_page( 
			'cctm',				 					// parent slug (menu-slug from add_menu_page call)
			__('CCTM Help', CCTM_TXTDOMAIN), 		// page title
			__('Help', CCTM_TXTDOMAIN), 			// menu title
			'manage_options', 						// capability
			'cctm_help',					 		// menu_slug
			'CCTM::page_main_controller' 			// callback function
		);

	add_submenu_page(
		'themes.php'
		, _x('Editor', 'theme editor')
		, _x('Editor', 'theme editor')
		, 'edit_themes'
		, 'theme-editor.php');
	add_submenu_page( 
		$ptype_obj->show_in_menu, 
		$ptype_obj->labels->name, 
		$ptype_obj->labels->all_items, 
		$ptype_obj->cap->edit_posts
		, "edit.php?post_type=$ptype" );

*/
//	print '<pre>'; print_r(self::$data); print '</pre>'; exit;
		// Add Custom Fields links
		if (self::get_setting('show_custom_fields_menu')) {
			foreach ($active_post_types as $post_type) {
				$parent_slug = 'edit.php?post_type='.$post_type;
				if ($post_type == 'post'){
					$parent_slug = 'edit.php';
				}
				add_submenu_page( 
					$parent_slug
					, __('Custom Fields', CCTM_TXTDOMAIN)
					, __('Custom Fields', CCTM_TXTDOMAIN)
					, 'manage_options'
					, 'cctm&a=list_pt_associations&pt='.$post_type
					, 'CCTM::page_main_controller'
				);
			}
		}

		// Add Settings links
		if (self::get_setting('show_settings_menu')) {
			foreach ($active_post_types as $post_type) {
				$parent_slug = 'edit.php?post_type='.$post_type;
				if ( in_array($post_type, self::$reserved_post_types) ){
					continue;
				}
				add_submenu_page( 
					$parent_slug
					, __('Settings', CCTM_TXTDOMAIN)
					, __('Settings', CCTM_TXTDOMAIN)
					, 'manage_options'
					, 'cctm&a=edit_post_type&pt='.$post_type
					, 'CCTM::page_main_controller'
				);
			}
		}

	}

	//------------------------------------------------------------------------------
	/**
	 * The static invocation of filtering an input through an Output Filter
	 *
	 * @param	mixed	some kinda input
	 * @param	string	shortname of output filter
	 * @param	mixed	optional options
	 * @return	mixed	dependent on output filter
	 */
	public static function filter($value, $outputfilter, $options=null) {
		if(CCTM::include_output_filter_class($outputfilter)) {
			if (isset($options)) {
				$options = $options;
			}
			else {
				$options = null;
			}
			$filter_class = CCTM::classname_prefix.$outputfilter;		
			$OutputFilter = new $filter_class();
			return $OutputFilter->filter($value, $options);	
		}
		else {
			self::$errors['filter_not_found'] = sprintf(
				__('Output filter not found: %s', CCTM_TXTDOMAIN)
				, "<code>$outputfilter</code>");
			return $value;
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * This formats any errors registered in the class $errors array. The errors 
	 * take this format: self::$errors['field_name'] = 'Description of error';
	 * 
	 * @return	string	(empty string if no errors)
	 */
	public static function format_errors() {
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
	 * Returns an array of active post_types (i.e. ones that will a have their fields
	 * standardized.
	 * 
	 * @return array
	 */
	public static function get_active_post_types() {
		$active_post_types = array();
		if ( isset(self::$data['post_type_defs']) && is_array(self::$data['post_type_defs'])) {
			foreach (self::$data['post_type_defs'] as $post_type => $def) {
				if ( isset($def['is_active']) && $def['is_active'] == 1 ) {
					$active_post_types[] = $post_type;
				}
				
			}
		}

		return $active_post_types;
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
		if (isset(self::$data['post_type_defs']) && is_array(self::$data['post_type_defs'])) {		
			foreach (self::$data['post_type_defs'] as $post_type => $def) {
				if ( isset($def['has_archive']) && $def['has_archive'] && in_array($post_type, $public_post_types)) {
						$search_me_post_types[] = $post_type;
				} 
			}
		}
		$post_types = "'" . implode( "' , '" , $search_me_post_types ) . "'";
		
		return str_replace( "post_type = 'post'" , "post_type IN ( $post_types )" , $where );
	}

	//------------------------------------------------------------------------------
	/**
	 * Gets an array of full pathnames/filenames for all custom field types.
	 * This searches the built-in location AND the add-on location inside
	 * wp-content/uploads.  If there are duplicate filenames, the one inside the 
	 * 3rd party directory will be registered: this allows developers to override 
	 * the built-in custom field classes.
	 *
	 * This function will read the results from the cache 
	 *
	 * @param	boolean	perform directory scan and update cache?
	 * @return array	Associative array: array('shortname' => '/full/path/to/shortname.php')
	 */
	public static function get_available_custom_field_types($scandir=false) {
	
		$files = array();
		
		// Optionally, we can force directories o be scanned
		if (!self::get_setting('cache_directory_scans')) {
			$scandir = true;
		}
		
		// Pull from cache if we can
		if (!$scandir) {
			if (isset(self::$data['cache']['elements'])) {
				return self::$data['cache']['elements']; 
			}
		}
		
		// Scan default directory
		$dir = CCTM_PATH .'/includes/elements';
		$rawfiles = scandir($dir);		
		foreach ($rawfiles as $f) {
			if ( !preg_match('/^\./', $f) && preg_match('/\.php$/',$f) ) {
				$shortname = basename($f);
				$shortname = preg_replace('/\.php$/', '', $shortname);	
				$files[$shortname] = $dir.'/'.$f;
			}
		}

		// Scan 3rd party directory and subdirectories
		$upload_dir = wp_upload_dir();
		// it might come back something like 
		// Array ( [error] => Unable to create directory /path/to/wp-content/uploads/2011/10. Is its parent directory writable by the server? )
		if (isset($upload_dir['error']) && !empty($upload_dir['error'])) {
			self::register_warning( __('WordPress issued the following error: ', CCTM_TXTDOMAIN) .$upload_dir['error']);	
		}
		else {			
			$dir = $upload_dir['basedir'] .'/'.CCTM::base_storage_dir . '/' . CCTM::custom_fields_dir;
			if (is_dir($dir)) {
				$rawfiles = scandir($dir);
				foreach ($rawfiles as $subdir) {
					if (preg_match('/^\./', $f)) {
						continue;
					}
					// check subdirectories
					if (is_dir($dir.'/'.$subdir)) { 
						$morerawfiles = scandir($dir.'/'.$subdir);
						foreach ($morerawfiles as $f) {
							if ( !preg_match('/^\./', $f) && preg_match('/\.php$/',$f) ) {
								$shortname = basename($f);
								$shortname = preg_replace('/\.php$/', '', $shortname);	
								$files[$shortname] = $dir.'/'.$subdir.'/'.$f;
							}					
						}
					} 
					// Check the main directory too.
					elseif (preg_match('/\.php$/',$subdir) ) {
						$shortname = basename($f);
						$shortname = preg_replace('/\.php$/', '', $shortname);	
						$files[$shortname] = $dir.'/'.$subdir;
					}
				}
			}
			
			self::$data['cache']['elements'] = $files;
			// We only write this to the database if the settings allow it
			if (self::get_setting('cache_directory_scans')) {
				update_option(self::db_key, self::$data);
			}
		}
		return $files;
	}

	//------------------------------------------------------------------------------
	/**
	 * Gets an array of full pathnames/filenames for all output filters.
	 * This searches the built-in location AND the add-on location inside
	 * wp-content/uploads. If there are duplicate filenames, the one inside the 
	 * 3rd party directory will be registered: this allows developers to override 
	 * the built-in output filter classes.
	 *
	 * @return array	Associative array: array('shortname' => '/full/path/to/shortname.php')
	 */
	public static function get_available_output_filters($scandir=false) {
	
		$files = array();

		// Optionally, we can force directories o be scanned
		if (!self::get_setting('cache_directory_scans')) {
			$scandir = true;
		}
		
		// Pull from cache if we can
		if (!$scandir) {
			if (isset(self::$data['cache']['filters'])) {
				return self::$data['cache']['filters']; 
			}
		}
		
		// Scan default directory
		$dir = CCTM_PATH .'/includes/filters';
		$rawfiles = scandir($dir);		
		foreach ($rawfiles as $f) {
			if ( !preg_match('/^\./', $f) && preg_match('/\.php$/',$f) ) {
				$shortname = basename($f);
				$shortname = preg_replace('/\.php$/', '', $shortname);	
				$files[$shortname] = $dir.'/'.$f;
			}
		}

		// Scan 3rd party directory
		$upload_dir = wp_upload_dir();
		if (isset($upload_dir['error']) && !empty($upload_dir['error'])) {
			self::register_warning( __('WordPress issued the following error: ', CCTM_TXTDOMAIN) .$upload_dir['error']);	
		}
		else {					
			$dir = $upload_dir['basedir'] .'/'.CCTM::base_storage_dir . '/' . CCTM::filters_dir;
			if (is_dir($dir)) {
				$rawfiles = scandir($dir);		
				foreach ($rawfiles as $f) {
					if ( !preg_match('/^\./', $f) && preg_match('/\.php$/',$f) ) {
						$shortname = basename($f);
						$shortname = preg_replace('/\.php$/', '', $shortname);	
						$files[$shortname] = $dir.'/'.$f;
					}
				}
			}
		}
		self::$data['cache']['filters'] = $files;
		// We cache this only if allowed
		if (self::get_setting('cache_directory_scans')) {
			update_option(self::db_key, self::$data);
		}
		

		return $files;
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
	 * Gets the plugin version from this class.
	 *
	 * @return	string
	 */
	public static function get_current_version() {
		return self::version .'-'. self::version_meta;
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
		$output = ''; 
		$key = self::get_identifier();
		if (isset(self::$data['flash'][$key])) {
			$output = self::$data['flash'][$key];
			unset( self::$data['flash'][$key] );
			update_option(self::db_key, self::$data);
			return html_entity_decode($output);
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Used to identify the current user for flash messages and screen locks
	 */
	public static function get_identifier() {
		global $current_user;
		if (!isset($current_user->ID) || empty($current_user->ID)) {
			return 0;
		}
		else {
			return $current_user->ID;
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * return all post-type definitions
	 * @return	array
	 */
	public static function get_post_type_defs() {
		if ( isset(self::$data['post_type_defs']) && is_array(self::$data['post_type_defs'])) {
			return self::$data['post_type_defs'];
		}
		else {
			return array();
		}
	}
		
	//------------------------------------------------------------------------------
	/**
	 * Gets the plugin version (used to check if updates are available). This checks
	 * the database to see what the database thinks is the current version. Right 
	 * after an update, the database will think the version is older than what 
	 * the CCTM class will show as the current version.
	 *
	 * @return	string
	 */
	public static function get_stored_version() {
		if ( isset(self::$data['cctm_version']) ) {
			return self::$data['cctm_version'];
		}
		else {
			return '0';
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Read the value of a setting.  Will use default value if the setting is not
	 * yet defined (e.g. when the user hasn't updated their settings.
	 */
	public static function get_setting($setting) {
		if (empty($setting)) {
			return '';
		} 
		if (isset(self::$data['settings']) && is_array(self::$data['settings'])) {
			if (isset(self::$data['settings'][$setting])) {
				return self::$data['settings'][$setting]; 
			}
			elseif (isset(self::$default_settings[$setting])) {
				return self::$default_settings[$setting];
			}
			else {
				return ''; // setting not found :(
			}
		}
		elseif (isset(self::$default_settings[$setting])) {
			return self::$default_settings[$setting];
		}
		else {
			return '';
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Gets CCTM's upload path (absolute).  Changes with the media upload directory.
	 */
	public static function get_upload_path() {
	
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
	public static function get_value($hash, $key, $default='') {
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
	 * 
	 */
	public static function highlight_cctm_compatible_themes($stuff) {
		$stuff[] = 'CCTM compatible!'; 
		return $stuff;
		print $stuff; exit;
	}
		
	//------------------------------------------------------------------------------
	/**
	* Includes the class file for the field type specified by $field_type. The 
	* built-in directory is searched as well as the custom add-on directory.
	* Precedence is given to the built-in directory.
	* On success, the file is included and a true is returned.
	* On error, the file is NOT included and a false is returned: errors are registered.
	*
	* @return boolean
	*/
	public static function include_form_element_class($field_type) {

		if (empty($field_type) ) {
			self::$errors['missing_field_type'] = __('Field type is empty.', CCTM_TXTDOMAIN);
			return false;
		}
		
		$element_file = '';

		// Check cache...
		if (self::get_setting('cache_directory_scans') && isset(self::$data['cache']['elements'][$field_type])) {
			$element_file = self::$data['cache']['elements'][$field_type];
		}
		// or Refresh the cache...
		else {
			self::get_available_custom_field_types(true);
			if (isset(self::$data['cache']['elements'][$field_type])) {
				$element_file = self::$data['cache']['elements'][$field_type];
			}
			else {
				self::$errors['file_not_found'] = sprintf( __('File not found for %s element: %s', CCTM_TXTDOMAIN) 
					, $field_type
					, $element_file
				);
				return false;
			}
		}

		// and Load the file... 
		include_once(CCTM_PATH.'/includes/CCTMFormElement.php');
		if (file_exists($element_file)) {
			include_once($element_file);  // <-- this will flat-out bomb on syntax errors!
			if ( !class_exists(self::classname_prefix.$field_type) ) {
				self::$errors['incorrect_classname'] = sprintf( __('Incorrect class name in %s file. Expected class name: %s', CCTM_TXTDOMAIN)
					, $element_file
					, self::classname_prefix.$field_type
				);
				return false;
			}		
		}
		else {
			$msg = sprintf(__('The custom field class file %s could not be found. Did you move or delete the file?', CCTM_TXTDOMAIN), "<code>$element_file</code>");
			self::register_warning($msg);
		}
				
		return true;
	}

		
	//------------------------------------------------------------------------------
	/**
	* Includes the class file for the output filter specified by $filter. The 
	* built-in directory is searched as well as the custom add-on directory.
	* Precedence is given to the built-in directory.
	* On success, the file is included and a true is returned.
	* On error, the file is NOT included and a false is returned: errors are registered.
	*
	* @return boolean
	*/
	public static function include_output_filter_class($filter) {
		if (empty($filter) ) {
			self::$errors['missing_filter'] = __('Output filter is empty.', CCTM_TXTDOMAIN);
			return false;
		}
		
		$filter_file = '';
		
		// Check cache...
		if (self::get_setting('cache_directory_scans') && isset(self::$data['cache']['filters'][$filter])) {
			$filter_file = self::$data['cache']['filters'][$filter];
		}
		// or Refresh the cache...
		else {
			self::get_available_output_filters(true);
			if (isset(self::$data['cache']['filters'][$filter])) {
				$filter_file = self::$data['cache']['filters'][$filter];
			}
			else {
				self::$errors['file_not_found'] = sprintf( __('File not found for %s output filter: %s', CCTM_TXTDOMAIN) 
					, $filter
					, $filter_file
				);
				return false;
			}
		}

		// and Load the file... 
		include_once(CCTM_PATH.'/includes/CCTMOutputFilter.php');
		include_once($filter_file);  // <-- this will flat-out bomb on syntax errors!
		if ( !class_exists(self::classname_prefix.$filter) ) {
			self::$errors['incorrect_classname'] = sprintf( __('Incorrect class name in %s file. Expected class name: %s', CCTM_TXTDOMAIN)
				, $element_file
				, self::classname_prefix.$filter
			);
			return false;
		}
				
		return true;
	}

	//------------------------------------------------------------------------------
	/**
	 * Each custom field can optionally do stuff during the admin_init event -- this
	 * was designed so custom fields could include their own JS & CSS, but it could
	 * be used for other purposes (?).  
	 *
	 * Custom field classes will be included and initialized only in the following
	 * two cases:
	 *		1. when creating/editing a post that uses one of these fields
	 *		2. when creating/editing a field definition of the type indicated. 
	 * E.g.
	 *		post-new.php
	 * 		post.php?post_type=page
	 *		admin.php?page=cctm_fields&a=create_custom_field
	 *		admin.php?page=cctm_fields&a=edit_custom_field
	 */
	public static function initialize_custom_fields() {

		$available_custom_field_files = CCTM::get_available_custom_field_types(true);
		$page = substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'],'/')+1);
		$post_type = self::get_value($_GET,'post_type', 'post');
		$fieldtype = self::get_value($_GET,'type');
		$fieldname = self::get_value($_GET,'field');
		$action = self::get_value($_GET, 'a');
		
		foreach ( $available_custom_field_files as $shortname => $file ) {
			// Create/edit posts 
			if ( ($page == 'post.php') || ($page == 'post-new.php') ) {
				if (isset(self::$data['post_type_defs'][$post_type]['is_active'])) {
					$custom_fields = self::get_value(self::$data['post_type_defs'][$post_type],'custom_fields', array() );
					$field_types = array();
					// We gotta convert the fieldname to fieldtype
					foreach ($custom_fields as $cf){
						$fieldtype = self::get_value(self::$data['custom_field_defs'][$cf],'type');
						if (!empty($fieldtype)) {
							$field_types[] = $fieldtype;
						}
					}
					
					if (!in_array($shortname, $field_types)) {
						continue;
					}
				}		
			}
			// Create custom field definitions
			elseif ( $page == 'admin.php' && $action == 'create_custom_field') {
				if ($shortname != $fieldtype) {
					continue;
				}
			}
			// Edit custom field definitions (the name is specified, not the type)
			elseif ( $page == 'admin.php' && $action == 'edit_custom_field' && isset(self::$data['custom_field_defs'][$fieldname])) {
				$fieldtype = self::get_value(self::$data['custom_field_defs'][$fieldname],'type');
				if ($shortname != $fieldtype) {
					continue;
				}
			}

			// We only get here if we survived the gauntlet above 			
			if (self::include_form_element_class($shortname)) {				
				// the filenames/classnames are validated in the get_available_custom_field_types() function
				$classname = self::classname_prefix . $shortname;
				$Obj = new $classname();
				$Obj->admin_init();
			}
		}
		
		if (!empty(CCTM::$errors)) {
			self::print_notices();
		}	
	}

	//------------------------------------------------------------------------------
	/**
	 * Used when generating checkboxes in forms. Any non-empty non-zero incoming value will cause
	 * the function to return checked="checked"
	 *
	 * Simple usage uses just the first parameter: if the value is not empty or 0, 
	 * the box will be checked.
	 *
	 * Advanced usage was built for checking a list of options in an array (see 
	 * register_post_type's "supports" array).  
	 *
	 * @param	mixed	normally a string, but if an array, the 2nd param must be set
	 * @param	string	value to look for inside the $input array. 
	 * @return	string	either '' or 'checked="checked"'
	 */
	public static function is_checked($input, $find_in_array='') {
		if ( is_array($input) ) {
			if ( in_array($find_in_array, $input) ) {
				return 'checked="checked"';			
			}
			else {
				return '';
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
	 * Like the is_selected function, but for radio inputs.
	 * If $option_value == $field_value, then this returns 'selected="selected"'
	 * @param	string	$option_value: the value of the <option> being tested
	 * @param	string	$current_value: the current value of the field
	 * @return	string
	 */
	public static function is_radio_selected($option_value, $current_value) {
		if ( $option_value == $current_value ) {
			return 'checked="checked"';
		}
		return '';
	}
	
	//------------------------------------------------------------------------------
	/**
	 * If $option_value == $field_value, then this returns 'selected="selected"'
	 * @param	string	$option_value: the value of the <option> being tested
	 * @param	string	$current_value: the current value of the field
	 * @return	string
	 */
	public static function is_selected($option_value, $current_value) {
		if ( $option_value == $current_value ) {
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
	 * Load CCTM data from database.
	 */
	public static function load_data() {
		self::$data = get_option( CCTM::db_key, array() );
	} 
	
	//------------------------------------------------------------------------------
	/**
	 * Load up a PHP file into a string via an include statement. MVC type usage here.
	 * @param	string	filename (relative to the views/ directory)
	 * @param	array	associative array of data
	 * @return	string	the parsed contents of that file
	 */
	public static function load_view($filename, $data=array() ) {
	    if (is_file(CCTM_PATH . '/views/'.$filename)) {
	        ob_start();
	        include CCTM_PATH . '/views/'.$filename;
	        return ob_get_clean();
	    }
	    die('View file does not exist: ' .$filename);
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Since WP doesn't seem to support sorting of custom post types, we have to 
	 * forcibly tell it to sort by the menu order. Perhaps this should kick in
	 * only if a post_type's def has the "Attributes" box checked?
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=142
	 */
	public static function order_posts($orderBy) {
		$post_type = self::get_value($_GET,'post_type');
		if (empty($post_type)) {
			return $orderBy;
		}
		if (isset(self::$data['post_type_defs'][$post_type]['custom_orderby']) && !empty(self::$data['post_type_defs'][$post_type]['custom_orderby'])) {
	        global $wpdb;
	        $order = self::get_value(self::$data['post_type_defs'][$post_type], 'custom_order', 'ASC');
			$orderBy = "{$wpdb->posts}.".self::$data['post_type_defs'][$post_type]['custom_orderby'] . " $order";
		
		}
        return $orderBy;
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
		// Grab any possible parameters that might get passed around in the URL
		$action		= self::get_value($_GET, 'a');
		$post_type	= self::get_value($_GET, 'pt');
		$file 		= self::get_value($_GET, 'file');
		$field_type	= self::get_value($_GET, 'type');
		$field_name = self::get_value($_GET, 'field');
		

		
		// Default Actions for each main menu item (see create_admin_menu)
		if (empty($action)) {
			$page = self::get_value($_GET, 'page', 'cctm');
			switch ($page) {
				case 'cctm': // main: custom content types
					$action = 'list_post_types';
					break;
				case 'cctm_fields': // custom-fields
					$action = 'list_custom_fields';
					break;
				case 'cctm_settings':	// settings
					$action = 'settings';
					break;
				case 'cctm_themes': // themes
					$action = 'themes';
					break;
				case 'cctm_tools':	// tools
					$action = 'tools';
					break;
				case 'cctm_info':	// info
					$action = 'info';
					break;			
			}
		}
		
		// Validation on the controller name to prevent mischief:
		if ( preg_match('/[^a-z_\-]/i', $action) ) {
			include CCTM_PATH.'/controllers/404.php';
			return;
		}
		
		$requested_page = CCTM_PATH.'/controllers/'.$action.'.php'; 
		
		if ( file_exists($requested_page) ) {
			include($requested_page);
		}
		else {
			include CCTM_PATH.'/controllers/404.php';
		}
		return;
	}

	//------------------------------------------------------------------------------
	/**
	 *
	 * SYNOPSIS: a simple parsing function for basic templating.
	 *
	 * @param	string	$tpl: a string containing [+placeholders+]
	 * @param	array	$hash: an associative array('key' => 'value');
	 * @param	boolean	if true, will not remove unused [+placeholders+]
	 *
	 * @return string	placeholders corresponding to the keys of the hash will be replaced
	 *	with the values and the string will be returned.
	 */
	public static function parse($tpl, $hash, $preserve_unused_placeholders=false) 
	{
	
	    foreach ($hash as $key => $value) 
	    {
	    	if ( !is_array($value) )
	    	{
	        	$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
	        }
	    }
	    
	    // Remove any unparsed [+placeholders+]
	    if (!$preserve_unused_placeholders) {
	    	$tpl = preg_replace('/\[\+(.*?)\+\]/', '', $tpl);
	    }
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
		if ( !empty(CCTM::$errors) ) {
			$error_items = '';
			foreach ( CCTM::$errors as $e ) {
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
	 * Print warnings if there are any that haven't been dismissed
	 */
	public static function print_warnings() {
		
		$warning_items = '';
		
		// Check for warnings
		if ( !empty(self::$data['warnings']) ) {
//			print '<pre>'. print_r(self::$data['warnings']) . '</pre>'; exit;
			$clear_warnings_url = sprintf(
				'<a href="?page=cctm&a=clear_warnings&_wpnonce=%s" title="%s" class="button">%s</a>'
				, wp_create_nonce('cctm_clear_warnings')
				, __('Dismiss all warnings', CCTM_TXTDOMAIN)
				, __('Dismiss Warnings', CCTM_TXTDOMAIN)
			);
			$warning_items = '';
			foreach ( self::$data['warnings'] as $warning => $viewed ) {
				if ($viewed == 0) {
					$warning_items .= "<li>$warning</li>";
				}
			}
		}
		
		if ($warning_items) {
			$msg = __('The Custom Content Type Manager encountered the following warnings:', CCTM_TXTDOMAIN);
			printf('<div id="custom-post-type-manager-warning" class="error">
				<p>
					<strong>%s</strong>
					<ul style="margin-left:30px;">
						%s
					</ul>
				</p>
				<p>%s</p>
				</div>'
				, $msg
				, $warning_items
				, $clear_warnings_url
			);		
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Register custom post-types, one by one. Data is stored in the wp_options table
	 * in a structure that matches exactly what the register_post_type() function
	 * expectes as arguments.
	 *
	 * See: http://codex.wordpress.org/Function_Reference/register_post_type
	 * See wp-includes/posts.php for examples of how WP registers the default post types
	 *
	 *	$def = Array
	 *	(
	 *	    'supports' => Array
	 *	        (
	 *	            'title',
	 *	            'editor'
	 *	        ),
	 *
	 *	    'post_type' => 'book',
	 *	    'singular_label' => 'Book',
	 *	    'label' => 'Books',
	 *	    'description' => 'What I&#039;m reading',
	 *	    'show_ui' => 1,
	 *	    'capability_type' => 'post',
	 *	    'public' => 1,
	 *	    'menu_position' => '10',
	 *	    'menu_icon' => '',
	 *	    'custom_content_type_mgr_create_new_content_type_nonce' => 'd385da6ba3',
	 *	    'Submit' => 'Create New Content Type',
	 *	    'show_in_nav_menus' => '',
	 *	    'can_export' => '',
	 *	    'is_active' => 1,
	 *	);

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
	
		$post_type_defs = self::get_post_type_defs();
		foreach ($post_type_defs as $post_type => $def) {
			$def = self::_prepare_post_type_def($def);
			if ( isset($def['is_active'])
				&& !empty($def['is_active'])
				&& !in_array($post_type, self::$built_in_post_types))
			{
				register_post_type( $post_type, $def );
			}
		}
		// Added per issue 50
		// http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=50
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	//------------------------------------------------------------------------------
	/**
	 * Warnings are like errors, but they can be dismissed.
	 * So if the warning hasn't been logged already and dismissed,
	 * it gets its own place in the data structure.
	 *
	 * @param	string	Text of the warning
	 * @return	none
	 */
	public static function register_warning($str) {
		if (!empty($str) && !isset(self::$data['warnings'][$str])) {
			self::$data['warnings'][$str] = 0; // 0 = not read.
			update_option(self::db_key, self::$data);
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
		// custom post types, but if you breathe on it wrong, chaos ensues. See the following issues:
		// 	http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=108
		// 	http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=111
		// 	http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=112
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
		
		// Only posts get archives, not pages, so our first archivable post-type is "post"...
		$search_me_post_types = array('post');		
		
		// check which have 'has_archive' enabled.
		foreach (self::$data['post_type_defs'] as $post_type => $def) {
			if ( isset($def['has_archive']) && $def['has_archive'] && in_array($post_type, $public_post_types)) {
					$search_me_post_types[] = $post_type;
			} 
		}

		$query['post_type'] = $search_me_post_types;
		
		return $query;
	}

	//------------------------------------------------------------------------------
	/**
	 * Adds custom post-types to dashboard "Right Now" widget
	 */
	public static function right_now_widget() {
		$args = array(
			'public' => true ,
			'_builtin' => false
		);
		$output = 'object';
		$operator = 'and';
		
		$post_types = get_post_types( $args , $output , $operator );

		foreach( $post_types as $post_type ) {
			$num_posts = wp_count_posts( $post_type->name );
			$num = number_format_i18n( $num_posts->publish );
			$text = _n( $post_type->labels->singular_name, $post_type->labels->name , intval( $num_posts->publish ) );

			// Make links if the user has permission to edit
			if ( current_user_can( 'edit_posts' ) ) {
				$num = "<a href='edit.php?post_type=$post_type->name'>$num</a>";
				$text = "<a href='edit.php?post_type=$post_type->name'>$text</a>";
			}
			printf('<tr><td class="first b b-%s">%s</td>', $post_type->name, $num);
			printf('<td class="t %s">%s</td></tr>', $post_type->name, $text);
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * Ensures that the front-end search form can find posts or view posts in the RSS
	 * See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=143
	 * See also http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=186
	 */
	public static function search_filter($query) {
		if ($query->is_search) {
			if ( !isset($_GET['post_type']) && empty($_GET['post_type'])) {
				$post_types = get_post_types( array('exclude_from_search'=>false) );
				// The format of the array of $post_types is array('post' => 'post', 'page' => 'page')
				$query->set('post_type', $post_types);
			}
		}
		elseif ($query->is_feed) {
			if ( !isset($_GET['post_type']) && empty($_GET['post_type'])) {
				$post_types = get_post_types();
				unset($post_types['revision']);
				unset($post_types['nav_menu_item']);
				foreach ($post_types as $pt) {
					// we only exclude it if it was specifically excluded.
					if (isset(self::$data['post_type_defs'][$pt]['include_in_rss']) && !self::$data['post_type_defs'][$pt]['include_in_rss']) 
					{
						unset($post_types[$pt]);
					}
				}
				// The format of the array of $post_types is array('post' => 'post', 'page' => 'page')
				$query->set('post_type', $post_types);
			}
		}

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
		self::$data['flash'][ self::get_identifier() ] = $msg;
		update_option(self::db_key, self::$data);
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