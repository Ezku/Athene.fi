<?php
/**
* CCTM_wysiwyg
*
* Implements a color selector input (a text field with special javascript attached).
* http://blog.meta100.com/post/600571131/mcolorpicker
*
*/
class CCTM_colorselector extends FormElement
{

	/** 
	* The $props array acts as a template which defines the properties for each instance of this type of field.
	* When added to a post_type, an instance of this data structure is stored in the array of custom_fields. 
	* Some properties are required of all fields (see below), some are automatically generated (see below), but
	* each type of custom field (i.e. each class that extends FormElement) can have whatever properties it needs
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
		'name' => '',
		'description' => '',
		'class' => '',
		'extra'	=> '',
		'default_value' => '',
		// 'type'	=> '', // auto-populated: the name of the class, minus the CCTM_ prefix.
		// 'sort_param' => '', // handled automatically
	);

	//------------------------------------------------------------------------------
	/**
	* This function provides a name for this type of field. This should return plain
	* text (no HTML). The returned value should be localized using the __() function.
	* @return	string
	*/
	public function get_name() {
		return __('Color Picker',CCTM_TXTDOMAIN);	
	}
	
	//------------------------------------------------------------------------------
	/**
	* Used to drive a thickbox pop-up when a user clicks "See Example"
	*/
	public function get_example_image() {
		return '';
	}
	
	//------------------------------------------------------------------------------
	/**
	* This function gives a description of this type of field so users will know 
	* whether or not they want to add this type of field to their custom content
	* type. The returned value should be localized using the __() function.
	* @return	string text description
	*/
	public function get_description() {
		return __('Color Picker fields implement a <input type="color"> element with a special Javascript color selection popup.',CCTM_TXTDOMAIN);
	}
	
	//------------------------------------------------------------------------------
	/**
	* This function should return the URL where users can read more information about
	* the type of field that they want to add to their post_type. The string may
	* be localized using __() if necessary (e.g. for language-specific pages)
	* @return	string 	e.g. http://www.yoursite.com/some/page.html
	*/
	public function get_url() {
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/ColorSelector';
	}
	

	//------------------------------------------------------------------------------
	/**
	 *
	 * @param string $current_value	current value for this field.
	 * @return string	
	 <label for="[+name+]" class="cctm_label cctm_textarea_label" id="cctm_label_[+name+]">[+label+]</label>
			<textarea name="[+name+]" class="cctm_textarea" id="[+name+]" [+extra+]>[+value+]</textarea>
	 */
	public function get_edit_field_instance($current_value) {
		# print_r($this->props); exit;
		$output = sprintf('
			%s
			<input type="color" name="%s" class="%s" id="%s" value="%s" data-hex="true" %s/>
			'
			, $this->wrap_label()
			, $this->get_field_name()
			, $this->get_field_class($this->name, 'text') . ' ' . $this->class
			, $this->get_field_id()
			, $current_value
			, $this->extra
		);
		
		$output .= $this->wrap_description($this->props['description']);
		
		return $this->wrap_outer($output);
	}

	//------------------------------------------------------------------------------
	/**
	 *
	 * @param mixed $def	field definition; see the $props array
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
			 	
		// Default Value
		$out .= '<div class="'.self::wrapper_css_class .'" id="default_value_wrapper">
			 	<label for="default_value" class="cctm_label cctm_text_label" id="default_value_label">'
			 		.__('Default Value', CCTM_TXTDOMAIN) .'</label>
			 		<input type="color" name="default_value" class="'.$this->get_field_class('default_value','text').'" id="default_value" value="'. htmlspecialchars($def['default_value'])
			 		.'" data-hex="true"/>
			 	' . $this->get_translation('default_value') .'
			 	</div>';

		// Extra
		$out .= '<div class="'.self::wrapper_css_class .'" id="extra_wrapper">
			 		<label for="extra" class="'.self::label_css_class.'">'
			 		.__('Extra', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="extra" class="'.$this->get_field_class('extra','text').'" id="extra" value="'
			 			.htmlspecialchars($def['extra']).'"/>
			 	' . $this->get_translation('extra').'
			 	</div>';

		// Class
		$out .= '<div class="'.self::wrapper_css_class .'" id="class_wrapper">
			 	<label for="class" class="'.self::label_css_class.'">'
			 		.__('Class', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="class" class="'.$this->get_field_class('class','text').'" id="class" value="'
			 			.htmlspecialchars($def['class']).'"/>
			 	' . $this->get_translation('class').'
			 	</div>';

		// Description	 
		$out .= '<div class="'.self::wrapper_css_class .'" id="description_wrapper">
			 	<label for="description" class="'.self::label_css_class.'">'
			 		.__('Description', CCTM_TXTDOMAIN) .'</label>
			 	<textarea name="description" class="'.$this->get_field_class('description','textarea').'" id="description" rows="5" cols="60">'.htmlentities($def['description']).'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';
		return $out;
	}
}


/*EOF*/