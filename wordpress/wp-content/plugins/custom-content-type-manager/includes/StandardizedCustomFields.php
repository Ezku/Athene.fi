<?php
/*------------------------------------------------------------------------------
This plugin standardizes the custom fields for specified content types, e.g.
post, page, and any other custom post-type you register via a plugin.
------------------------------------------------------------------------------*/
class StandardizedCustomFields 
{
	/*
	This prefix helps ensure unique keys in the $_POST array. It is used only to 
	identify the form elements; this prefix is *not* used as part of the meta_key
	when saving the field names to the database. If you want your fields to be 
	hidden from built-in WordPress functions, you can name them individually 
	using "_" as the first character.
	
	If you omit a prefix entirely, your custom field names must steer clear of
	the built-in post field names (e.g. 'content').
	*/
	const field_name_prefix = 'custom_content_'; 
	
	// Which types of content do we want to standardize?
	public static $content_types_array = array('post');
	
	//! Private Functions
	//------------------------------------------------------------------------------
	/**
	 * This plugin is meant to be configured so it acts on a specified list of post
	 * types, e.g. post, page, or any custom content types that is registered.
	 * @return array	$active_post_types. Array of strings, each a valid post-type name, 
		e.g. array('post','page','your_custom_post_type')
	*/
	private static function _get_active_post_types() {
	
		$active_post_types = array();	
		$data = get_option( CCTM::db_key );
		if ( !empty($data) && is_array($data) )
		{
			$known_post_types = array_keys($data);	
			foreach ($known_post_types as $pt)
			{
				if ( CCTM::is_active_post_type($pt) )
				{
					$active_post_types[] = $pt;
				}
			}
		}
		
		return $active_post_types;
	}

	//------------------------------------------------------------------------------
	/**
	 * Get custom fields for this content type.
	 * @param string $post_type the name of the post_type, e.g. post, page.
	OUTPUT: array of associative arrays where each associative array describes 
		a custom field to be used for the $content_type specified.
	FUTURE: read these arrays from the database.
	*/
	private static function _get_custom_fields($post_type) {
		if (isset(CCTM::$data[$post_type]['custom_fields']))
		{
			// Sorting blows away the field_name key, so you have to walk back through the array and re-establish the key
			usort(CCTM::$data[$post_type]['custom_fields'], CCTM::sort_custom_fields('sort_param', 'strnatcasecmp'));
			foreach ( CCTM::$data[$post_type]['custom_fields'] as $i => $def ) {
				$field_name = CCTM::$data[$post_type]['custom_fields'][$i]['name'];
				CCTM::$data[$post_type]['custom_fields'][$field_name] = $def; // re-establish the key version.
				unset(CCTM::$data[$post_type]['custom_fields'][$i]); // kill the integer version
			} 
			return CCTM::$data[$post_type]['custom_fields'];
		}
		else
		{
			return array();
		}
	}

	/*------------------------------------------------------------------------------
	This determines if the user is creating a new post (of any type, e.g. a new page).
	This is used so we know if and when to use the default values for any field.
	INPUT: none; the current page is read from the server URL.
	OUTPUT: boolean
	------------------------------------------------------------------------------*/
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
		$content_types_array = self::_get_active_post_types();
		foreach ( $content_types_array as $content_type ) {
			add_meta_box( 'custom-content-type-mgr-custom-fields'
				, __('Custom Fields', CCTM_TXTDOMAIN )
				, 'StandardizedCustomFields::print_custom_fields'
				, $content_type
				, 'normal'
				, 'high'
				, $content_type 
			);
		}
	}

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
	 
	 
		if( preg_match('/name="(parent_id|post_parent)"/', $output) && $post->post_type="articles" ) {
			$post_statuses = array('pending','publish');
			$post_exclude = is_numeric($_GET['post']) ? ' AND ID!='.$_GET['post']:'';
			$query = "SELECT * FROM ".$wpdb->posts." WHERE (post_type = 'page' AND (post_status='".implode("' OR post_status='",$post_statuses)."') AND $post_exclude ) ORDER BY menu_order, post_title ASC";
			$pages = $wpdb->get_results($query);
			$output = '';
			if ( ! empty($pages) ) {
				$output = "<select name=\"parent_id\" id=\"\">\n";
				$output .= "\t<option value=\"\">".__('(no parent)')."</option>\n";
				$output .= walk_page_dropdown_tree($pages, 0);
				$output .= "</select>\n";
			}
		}
	 
	 	CCTM::$data[$post_type]['cctm_hierarchical_post_types'] = array()
	 	CCTM::$data[$post_type]['cctm_hierarchical_post_status'] = array()
	 
	 */
	public static function customized_hierarchical_post_types( $html ) {
		global $wpdb, $post;
		$post_type = $post->post_type;
		
		// customize if selected
		if (isset(CCTM::$data[$post_type]['hierarchical'])
			&& CCTM::$data[$post_type]['hierarchical'] 
			&& CCTM::$data[$post_type]['cctm_hierarchical_custom']) {
			// filter by additional parameters
			if ( CCTM::$data[$post_type]['cctm_hierarchical_includes_drafts'] ) {
				$args['post_status'] = 'publish,draft,pending';	
			}
			else {
				$args['post_status'] = 'publish';
			}
			
			$args['post_type'] = CCTM::$data[$post_type]['cctm_hierarchical_post_types'];
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
		//return;
		$post_type = $callback_args['args']; // the 7th arg from add_meta_box()
		$custom_fields = self::_get_custom_fields($post_type);
		$output = '';		
				

		// If no custom content fields are defined, or if this is a built-in post type that hasn't been activated...
		if ( empty($custom_fields) )
		{
			$post_type = $post->post_type;
			$url = sprintf( '<a href="options-general.php?page='
				.CCTM::admin_menu_slug.'&'
				.CCTM::action_param.'=4&'
				.CCTM::post_type_param.'='.$post_type.'">%s</a>', __('Settings Page', CCTM_TXTDOMAIN ) );
			print '<p>';
			printf ( __('Custom fields can be added and configured using the %1$s %2$s', CCTM_TXTDOMAIN), CCTM::name, $url );
			print '</p>';
			return;
		}
		
		foreach ( $custom_fields as $def_i => &$field_def ) {
			$output_this_field = '';
			CCTM::include_form_element_class($field_def['type']); // This will die on errors
			$field_type_name = CCTM::FormElement_classname_prefix.$field_def['type'];
			$FieldObj = new $field_type_name(); // Instantiate the field element
			
			if ( self::_is_new_post() ) {	
				$FieldObj->props = $field_def;
				$output_this_field = $FieldObj->get_create_field_instance();
			}
			else {
				$current_value = htmlspecialchars( get_post_meta( $post->ID, $field_def['name'], true ) );
				$FieldObj->props = $field_def;
				$output_this_field =  $FieldObj->get_edit_field_instance($current_value);
			}
						
			$output .= $output_this_field;
		}

 		// Print the form
 		print '<div class="form-wrap">';
	 	wp_nonce_field('update_custom_content_fields','custom_content_fields_nonce');
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
		$content_types_array = self::_get_active_post_types();
		foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
			foreach ( $content_types_array as $content_type )
			{
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
	 * @param	integer	$post_id id of the post these custom fields are associated with
	 * @param	object	$post  the post object
	 */
	public static function save_custom_fields( $post_id, $post ) 
	{
		// Bail if this post-type is not active in the CCTM
		if ( !CCTM::is_active_post_type($post->post_type) ) {
			return;
		}
	
		// Bail if there are no custom fields defined in the CCTM
		#$data = get_option( CCTM::db_key );
		if ( empty(CCTM::$data[$post->post_type]['custom_fields']) ) {
			return;
		}
		
		// See issue http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=80
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
			return $post_id;
		}
				
		// The 2nd arg here is important because there are multiple nonces on the page
		if ( !empty($_POST) && check_admin_referer('update_custom_content_fields','custom_content_fields_nonce') ) {			
			$custom_fields = self::_get_custom_fields($post->post_type);
			foreach ( $custom_fields as $field_name => $field_def ) {
				$field_type = CCTM::$data[$post->post_type]['custom_fields'][$field_name]['type'];
				CCTM::include_form_element_class($field_type); // This will die on errors
	
				$field_type_name = CCTM::FormElement_classname_prefix.$field_type;
				$FieldObj = new $field_type_name(); // Instantiate the field element
				$FieldObj->props = CCTM::$data[$post->post_type]['custom_fields'][$field_name];
				$value = $FieldObj->save_post_filter($_POST, $field_name);
				update_post_meta( $post_id, $field_name, $value );
			}
			
		}
	}


} // End Class



/*EOF*/