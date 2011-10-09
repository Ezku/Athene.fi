<?php
/**
* CCTM_image
*
* Implements an field that stores a reference to an image (i.e. an attachment post that is an image)
*
*/
class CCTM_image extends CCTMFormElement
{
	/** 
	* The $props array acts as a template which defines the properties for each instance of this type of field.
	* When added to a post_type, an instance of this data structure is stored in the array of custom_fields. 
	* Some properties are required of all fields (see below), some are automatically generated (see below), but
	* each type of custom field (i.e. each class that extends CCTMFormElement) can have whatever properties it needs
	* in order to work, e.g. a dropdown field uses an 'options' property to define a list of possible values.
	* 
	* 
	*
	* The following properties MUST be implemented:
	*	'name' 	=> Unique name for an instance of this type of field; corresponds to wp_postmeta.meta_key for each post
	*	'label'	=> 
	*	'description'	=> a description of this type of field.
	*
	* The following properties are set automatically:
	*
	* 	'type' 			=> the name of this class, minus the CCTM_ prefix.
	* 	'sort_param' 	=> populated via the drag-and-drop behavior on "Manage Custom Fields" page.
	*/
	public $props = array(
		'label' => '',
		'button_label' => '',
		'name' => '',
		'description' => '',
		'class' => '',
		'extra'	=> '',
		'default_value' => '',
		'output_filter' => 'to_image_src',
		// 'type'	=> '', // auto-populated: the name of the class, minus the CCTM_ prefix.
		// 'sort_param' => '', // handled automatically
	);
	
	public $supported_output_filters = array('to_image_src','to_image_tag','to_image_array');

	//------------------------------------------------------------------------------
	/**
	* This function provides a name for this type of field. This should return plain
	* text (no HTML). The returned value should be localized using the __() function.
	* @return	string
	*/
	public function get_name() {
		return __('Image',CCTM_TXTDOMAIN);	
	}
	
	//------------------------------------------------------------------------------
	/**
	* This function gives a description of this type of field so users will know 
	* whether or not they want to add this type of field to their custom content
	* type. The returned value should be localized using the __() function.
	* @return	string text description
	*/
	public function get_description() {
		return __('Image fields are used to store references to any image that has been uploaded via the WordPress media uploader.',CCTM_TXTDOMAIN);
	}
	
	//------------------------------------------------------------------------------
	/**
	* This function should return the URL where users can read more information about
	* the type of field that they want to add to their post_type. The string may
	* be localized using __() if necessary (e.g. for language-specific pages)
	* @return	string 	e.g. http://www.yoursite.com/some/page.html
	*/
	public function get_url() {
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Image';
	}
	

	//------------------------------------------------------------------------------
	/**
	 * @param mixed $current_value	current value for this field (an integer ID).
	 * @return string	
	 */
	public function get_edit_field_instance($current_value) {
	
		global $post;
		
		$media_html = '';
		$preview_html = '';
		#$controller_url = CCTM_URL.'/post-selector.php?post_type=attachment&b=1&post_mime_type=';
		$controller_url = CCTM_URL.'/post-selector.php?post_type=attachment&b=1&post_mime_type=image';
		$click_label = __('Choose Image');
		if ($this->props['button_label']) {
			$click_label = $this->props['button_label'];
		}
		$remove_label = __('Remove');
		
		// It has a value
		if ( !empty($current_value) )
		{
			$attachment_post = get_post($current_value, ARRAY_A);
			$attachment_post['post_id'] = $attachment_post['ID'];
			$attachment_post['view'] = __('View');
			$attachment_post['site_url'] = get_site_url();			
			$tpl = file_get_contents( CCTM_PATH.'/tpls/post_selector/preview_html.tpl');
			$attachment_post['preview_html'] = wp_get_attachment_image( $current_value, 'thumbnail', true );
			$preview_html = CCTM::parse($tpl, $attachment_post);
		}
		
		$output = $this->wrap_label();
		$output .= '
			<input type="hidden" id="'.$this->get_field_id().'" name="'.$this->get_field_id().'" value="'.$current_value.'" />
			<br />
			<div id="'.$this->get_field_id().'_media">'.$preview_html.'</div>
			<br class="clear" />
			<a href="'.$controller_url.'&fieldname='.$this->get_field_id().'" name="'.$click_label.'" class="thickbox button">'
			.$click_label.'</a> 
			<span class="button" onclick="javascript:remove_relation(\''.$this->get_field_id().'\',\''.$this->get_field_id().'_media\')">'.$remove_label.'</span>
			<br class="clear" /><br />';
		$output .= $this->wrap_description($this->props['description']);
		
		return $this->wrap_outer($output);
	}


	//------------------------------------------------------------------------------
	/**
	 * This should return (not print) form elements that handle all the controls required to define this
	 * type of field.  The default properties correspond to this class's public variables,
	 * e.g. name, label, etc. The form elements you create should have names that correspond
	 * with the public $props variable. A populated array of $props will be stored alongside 
	 * the custom-field data for the containing post-type.
	 *
	 * @param mixed   $current_values should be an associative array.
	 * @return	string	HTML input fields
	 */
	public function get_edit_field_definition($def) {
		// Label
		$out = '<div class="'.self::wrapper_css_class .'" id="label_wrapper">
			 		<label for="label" class="'.self::label_css_class.'">'
			 			.__('Label', CCTM_TXTDOMAIN).'</label>
			 		<input type="text" name="label" class="'.self::css_class_prefix.'text" id="label" value="'.htmlspecialchars($def['label']) .'"/>
			 		' . $this->get_translation('label').'
			 	</div>';
		// Name
		$out .= '<div class="'.self::wrapper_css_class .'" id="name_wrapper">
				 <label for="name" class="cctm_label cctm_text_label" id="name_label">'
					. __('Name', CCTM_TXTDOMAIN) .
			 	'</label>
				 <input type="text" name="name" class="'.$this->get_field_class('name','text').'" id="name" value="'.htmlspecialchars($def['name']) .'"/>'
				 . $this->get_translation('name') .'
			 	</div>';
			
		// Initialize / defaults
		$preview_html = '';
		$click_label = __('Choose Image');
		$remove_label = __('Remove');
		$label = __('Default Image', CCTM_TXTDOMAIN);
		$controller_url = CCTM_URL.'/post-selector.php?post_type=attachment&b=1&post_mime_type=image';
			
		// Handle the display of the Default Image thumbnail
		if ( !empty($def['default_value']) ) {
			$preview_html = wp_get_attachment_image( $def['default_value'], 'thumbnail', true );
			$attachment_obj = get_post($def['default_value']);
			// Wrap it... the default value *should* just be an integer
			$preview_html .= '<span class="cctm_label">'.$attachment_obj->post_title.' <span class="cctm_id_label">('.htmlspecialchars($def['default_value']).')</span></span><br />';
			
		}


		// Button Label
		$out .= '<div class="'.self::wrapper_css_class .'" id="button_label_wrapper">
			 		<label for="button_label" class="'.self::label_css_class.'">'
			 			.__('Button Label', CCTM_TXTDOMAIN).'</label>
			 		<input type="text" name="button_label" class="'.self::css_class_prefix.'text" id="button_label" value="'.htmlspecialchars($def['button_label']) .'"/>
			 		' . $this->get_translation('button_label').'
			 	</div>';
			 	
		// Default Value 			
		$out .= '
			<div class="'.self::wrapper_css_class .'" id="default_value_wrapper">
				<span class="cctm_label cctm_media_label" id="cctm_label_default_value">'.$label.' <a href="'.$controller_url.'&fieldname=default_value" name="'.$label.'" class="thickbox button">'.$click_label.'</a>
				<span class="button" onclick="javascript:remove_relation(\'default_value\',\'default_value_media\');">'.$remove_label.'</span>
				</span> 
				
				<input type="hidden" id="default_value" name="default_value" value="'
					.htmlspecialchars($def['default_value']).'" /><br />
				<div id="default_value_media">'.$preview_html.'</div>
				
				<br />
			</div>';

			
		// Description	 
		$out .= '<div class="'.self::wrapper_css_class .'" id="description_wrapper">
			 	<label for="description" class="'.self::label_css_class.'">'
			 		.__('Description', CCTM_TXTDOMAIN) .'</label>
			 	<textarea name="description" class="'.$this->get_field_class('description','textarea').'" id="description" rows="5" cols="60">'
			 		. htmlspecialchars($def['description'])
			 		.'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';
			 	
		// Output Filter
		if ( !empty($this->supported_output_filters) ) { 
			$out .= $this->get_available_output_filters($def);
		}	 

		return $out;
	}

}


/*EOF*/