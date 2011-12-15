<?php
/*------------------------------------------------------------------------------
This plugin standardizes the custom fields for specified content types, e.g.
post, page, and any other custom post-type you register via a plugin.
------------------------------------------------------------------------------*/
class StandardizedCustomFields 
{
	
	//! Private Functions
	//------------------------------------------------------------------------------
	/**
	 * Get custom fields for this content type.
	 * @param string $post_type the name of the post_type, e.g. post, page.
	OUTPUT: array of associative arrays where each associative array describes 
		a custom field to be used for the $content_type specified.
	FUTURE: read these arrays from the database.
	*/
	private static function _get_custom_fields($post_type) {
		if (isset(CCTM::$data['post_type_defs'][$post_type]['custom_fields']))
		{
			return CCTM::$data['post_type_defs'][$post_type]['custom_fields'];
		}
		else
		{
			return array();
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * This determines if the user is editing an existing post.
	 *
	 * @return boolean
	 */
	private static function _is_existing_post()
	{
		if ( substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'],'/')+1) == 'post.php' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	//------------------------------------------------------------------------------
	/**
	 * This determines if the user is creating a new post.
	 *
	 * @return boolean
	 */
	 private static function _is_new_post()
	{
		if ( substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'],'/')+1) == 'post-new.php' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	//! Public Functions	
	//------------------------------------------------------------------------------
	/**
	* Create the new Custom Fields meta box
	* TODO: allow customization of the name, instead of just 'Custom Fields', and also
	* of the wrapper div.
	*/
	public static function create_meta_box() {
		$content_types_array = CCTM::get_active_post_types();
		foreach ( $content_types_array as $content_type ) {
			add_meta_box( 'cctm_default'
				, __('Custom Fields', CCTM_TXTDOMAIN )
				, 'StandardizedCustomFields::print_custom_fields'
				, $content_type
				, 'normal'
				, 'high'
				, $content_type 
			);
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * WP only allows users to select PUBLISHED pages of the same post_type in their hierarchical
	 * menus.  And there are no filters for this whole thing save at the end to filter the generated 
	 * HTML before it is sent to the browser. Arrgh... this is grossly inefficient!!
	 * It's inefficient, but here we optionally pimp out the HTML to offer users sensible choices for
	 * hierarchical parents.
	 *
	 * @param	string	incoming html element for selecting a parent page, e.g.
	 *						<select name="parent_id" id="parent_id">
	 *					        <option value="">(no parent)</option>
	 *					        <option class="level-0" value="706">Post1</option>
	 *						</select>	
	 *
	 * See http://wordpress.org/support/topic/cannot-select-parent-when-creatingediting-a-page	 
	 */
	public static function customized_hierarchical_post_types( $html ) {
		global $wpdb, $post;
		
		// Otherwise there be errors on the Settings --> Reading page
		if (empty($post)) {
			return $html;
		}

		$post_type = $post->post_type;
		
		
		// customize if selected
		if (isset(CCTM::$data['post_type_defs'][$post_type]['hierarchical'])
			&& CCTM::$data['post_type_defs'][$post_type]['hierarchical'] 
			&& CCTM::$data['post_type_defs'][$post_type]['cctm_hierarchical_custom']) {
			// filter by additional parameters
			if ( CCTM::$data['post_type_defs'][$post_type]['cctm_hierarchical_includes_drafts'] ) {
				$args['post_status'] = 'publish,draft,pending';	
			}
			else {
				$args['post_status'] = 'publish';
			}
			
			$args['post_type'] = CCTM::$data['post_type_defs'][$post_type]['cctm_hierarchical_post_types'];
			// We gotta ensure ALL posts are returned.
			// See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=114
			$args['numberposts'] = -1;

			$posts = get_posts($args);

			$html = '<select name="parent_id" id="parent_id">
				<option value="">(no parent)</option>
			';
			foreach ( $posts as $p ) {
				$is_selected = '';
				if ( $p->ID == $post->post_parent ) {
					$is_selected = ' selected="selected"';	
				}
				$html .= sprintf('<option class="level-0" value="%s"%s>%s (%s)</option>', $p->ID, $is_selected, $p->post_title, $p->post_type);
			}
			$html .= '</select>';
		}
		return $html;
	}

	//------------------------------------------------------------------------------
	/**
	 * We use this to print out the large icon
	 * http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=188
	 */
	public static function print_admin_header() {		
		$post_type = CCTM::get_value($_GET, 'post_type');
		if (!empty($post_type)) {
			// Show the big icon: http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=136
			if ( isset(CCTM::$data['post_type_defs'][$post_type]['use_default_menu_icon']) 
				&& CCTM::$data['post_type_defs'][$post_type]['use_default_menu_icon'] == 0 ) { 
				$baseimg = basename(CCTM::$data['post_type_defs'][$post_type]['menu_icon']);
				// die($baseimg); 
				if ( file_exists(CCTM_PATH . '/images/icons/32x32/'. $baseimg) ) {
					printf('
					<style>
						#icon-edit, #icon-post {
						  background-image:url(%s);
						  background-position: 0px 0px;
						}
					</style>'
					, CCTM_URL . '/images/icons/32x32/'. $baseimg);
				}
			}	
		}
	}

	/*------------------------------------------------------------------------------
	Display the new Custom Fields meta box inside the WP manager.
	INPUT:
	@param object $post passed to this callback function by WP. 
	@param object $callback_args will always have a copy of this object passed (I'm not sure why),
		but in $callback_args['args'] will be the 7th parameter from the add_meta_box() function.
		We are using this argument to pass the content_type.
	
	@return null	this function should print form fields.
	------------------------------------------------------------------------------*/
	public static function print_custom_fields($post, $callback_args='') 
	{
	
		$post_type = $callback_args['args']; // the 7th arg from add_meta_box()
		$custom_fields = self::_get_custom_fields($post_type);
		$output = '';		
				
		// Show the big icon: http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=136
		if ( isset(CCTM::$data['post_type_defs'][$post_type]['use_default_menu_icon']) 
			&& CCTM::$data['post_type_defs'][$post_type]['use_default_menu_icon'] == 0 ) { 
			$baseimg = basename(CCTM::$data['post_type_defs'][$post_type]['menu_icon']);
			// die($baseimg); 
			if ( file_exists(CCTM_PATH . '/images/icons/32x32/'. $baseimg) ) {
				$output .= sprintf('
				<style>
					#icon-edit, #icon-post {
					  background-image:url(%s);
					  background-position: 0px 0px;
					}
				</style>'
				, CCTM_URL . '/images/icons/32x32/'. $baseimg);
			}
		}


		// If no custom content fields are defined, or if this is a built-in post type that hasn't been activated...
		if ( empty($custom_fields) )
		{
			return;
		}
		
		foreach ( $custom_fields as $cf ) {
			if (!isset(CCTM::$data['custom_field_defs'][$cf])) {
				// throw error!!
				continue;
			}
			$def = CCTM::$data['custom_field_defs'][$cf];
			$output_this_field = '';
			CCTM::include_form_element_class($def['type']); // This will die on errors
			$field_type_name = CCTM::classname_prefix.$def['type'];
			$FieldObj = new $field_type_name(); // Instantiate the field element
			
			if ( self::_is_new_post() ) {	
				$FieldObj->props = $def;
				$output_this_field = $FieldObj->get_create_field_instance();
			}
			else {
				$current_value = htmlspecialchars( get_post_meta( $post->ID, $def['name'], true ) );
				$FieldObj->props = $def;
				$output_this_field =  $FieldObj->get_edit_field_instance($current_value);
			}
						
			$output .= $output_this_field;
		}
		
		// Print the nonce: this offers security and it will help us know when we should do custom saving logic in the save_custom_fields function
		$output .= '<input type="hidden" name="_cctm_nonce" value="'. wp_create_nonce('cctm_create_update_post') . '" />';

 		// Print the form
 		print '<div class="form-wrap">';		
	 	print $output;
	 	print '</div>';
 
	}


	/*------------------------------------------------------------------------------
	Remove the default Custom Fields meta box. Only affects the content types that
	have been activated.
	INPUTS: sent from WordPress
	------------------------------------------------------------------------------*/
	public static function remove_default_custom_fields( $type, $context, $post ) 
	{
		$content_types_array = CCTM::get_active_post_types();
		foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
			foreach ( $content_types_array as $content_type ) {
				remove_meta_box( 'postcustom', $content_type, $context );
			}
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Save the new Custom Fields values. If the content type is not active in the 
	 * CCTM plugin or its custom fields are not being standardized, then this function 
	 * effectively does nothing.
	 *
	 * WARNING: This function is also called when the wp_insert_post() is called, and
	 * we don't want to step on its toes. We want this to kick in ONLY when a post 
	 * is inserted via the WP manager. 
	 * see http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=52
	 * 
	 * @param	integer	$post_id id of the post these custom fields are associated with
	 * @param	object	$post  the post object
	 */
	public static function save_custom_fields( $post_id, $post ) 
	{

		// Bail if you're not in the admin editing a post
		if (!self::_is_existing_post() && !self::_is_new_post() ) {
			return;
		}
		
		// Bail if this post-type is not active in the CCTM
		if ( !isset(CCTM::$data['post_type_defs'][$post->post_type]['is_active']) 
			|| CCTM::$data['post_type_defs'][$post->post_type]['is_active'] == 0) {
			return;
		}
	
		// Bail if there are no custom fields defined in the CCTM
		if ( empty(CCTM::$data['post_type_defs'][$post->post_type]['custom_fields']) ) {
			return;
		}
		
		// See issue http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=80
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
			return $post_id;
		}

		// Use this to ensure you save custom fields only when saving from the edit/create post page
		$nonce = CCTM::get_value($_POST, '_cctm_nonce');
		if (! wp_verify_nonce($nonce, 'cctm_create_update_post') ) {
			return;
		}

		if ( !empty($_POST) ) {			
			$custom_fields = self::_get_custom_fields($post->post_type);
			foreach ( $custom_fields as $field_name ) {
				if (!isset(CCTM::$data['custom_field_defs'][$field_name]['type'])) {
					continue;
				}
				$field_type = CCTM::$data['custom_field_defs'][$field_name]['type'];
				
				if (CCTM::include_form_element_class($field_type)) {
					$field_type_name = CCTM::classname_prefix.$field_type;
					$FieldObj = new $field_type_name(); // Instantiate the field element
					$FieldObj->props = CCTM::$data['custom_field_defs'][$field_name];
					$value = $FieldObj->save_post_filter($_POST, $field_name);
					// Custom fields can return a literal null if they don't ever save data to the db.
					if ($value !== null) {
						update_post_meta( $post_id, $field_name, $value );
					}
				}
				else {
					// error!
				}
			}			
		}
	}


} // End Class



/*EOF*/