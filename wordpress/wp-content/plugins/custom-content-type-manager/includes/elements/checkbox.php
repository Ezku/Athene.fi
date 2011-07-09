<?php
/**
* CCTM_checkbox
*
* Implements an HTML text input.
*
*/
class CCTM_checkbox extends FormElement
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
		// checked_by_default determines whether 'checked_value' or 'unchecked_value' is passed to 
		// the current value for new field instances.  This value should be 1 (checked) or 0 (unchecked)
		'checked_by_default' => '0', 
		'checked_value' => '1',
		'unchecked_value' => '0',
		'class' => '',
		'extra'	=> '',
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
		return __('Checkbox',CCTM_TXTDOMAIN);	
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
		return __('Checkbox fields implement the standard <input="checkbox"> element. 
			"Extra" parameters, e.g. "alt" can be specified in the definition.',CCTM_TXTDOMAIN);
	}

	//------------------------------------------------------------------------------
	/**
	* This function should return the URL where users can read more information about
	* the type of field that they want to add to their post_type. The string may
	* be localized using __() if necessary (e.g. for language-specific pages)
	* @return	string 	e.g. http://www.yoursite.com/some/page.html
	*/
	public function get_url() {
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Checkbox';
	}

	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param mixed $def associative array containing the full definition for this type of element.
	 * @param string HTML to be used in the WP manager for an instance of this type of element.
	 */
	public function get_create_field_instance() {
		if ( $this->props['checked_by_default']) {
			$current_value = $this->props['checked_value'];
		}
		else {
			$current_value = $this->props['unchecked_value'];		
		}
		return $this->get_edit_field_instance($current_value); // pass on to 
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 * @param string $current_value
	 * @return string
	 			<input type="checkbox" name="[+name+]" class="cctm_checkbox" id="[+id+]" value="[+checked_value+]" [+is_checked+] [+extra+]/> 
			<label for="[+id+]" class="cctm_label cctm_checkbox_label" id="cctm_label_[+name+]">[+label+]</label>';


		$output = sprintf('
			%s 
			<input type="text" name="%s" class="%s" id="%s" %s value="%s"/>
			'
			, $this->wrap_label()
			, $this->get_field_name()
			, $this->get_field_class($this->name, 'text') . ' ' . $this->class
			, $this->get_field_id()
			, stripslashes($this->extra)
			, $current_value
		);
		
		return $this->wrap_outer($output);

	 
	 */
	public function get_edit_field_instance($current_value) {

		$is_checked = '';
		if ($current_value == $this->checked_value) {
			$is_checked = 'checked="checked"';
		}

		$output = sprintf(' 
			<input type="checkbox" name="%s" class="%s" id="%s" value="%s" %s %s/>
			'
			, $this->get_field_name()
			, $this->get_field_class($this->name, 'checkbox') . ' ' . $this->class
			, $this->get_field_id()
			, $this->checked_value
			, $this->extra
			, $is_checked
		);

		$output .= $this->wrap_label();
		$output .= $this->wrap_description($this->props['description']);
		
		return $this->wrap_outer($output);
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param unknown $current_values
			<style>
			input.cctm_error { 
				background: #fed; border: 1px solid red;
			}
			</style>
	 */
	public function get_edit_field_definition($def) {
		$is_checked = '';
		if ($def['checked_by_default']) {
			$is_checked = 'checked="checked"';
		}		
	
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

		// Value when Checked			 	
		$out .= '<div class="'.self::wrapper_css_class .'" id="checked_value_wrapper">
				 <label for="checked_value" class="cctm_label cctm_text_label" id="checked_value_label">'
					. __('Value when checked', CCTM_TXTDOMAIN) .
			 	'</label>
				 <input type="text" name="checked_value" size="8" class="'.$this->get_field_class('checked_value','text').'" id="checked_value" value="'.htmlspecialchars($def['checked_value']) .'"/>'
				 . $this->get_translation('checked_value') .'
			 	</div>';
			 	
		// Value when Unchecked			 	
		$out .= '<div class="'.self::wrapper_css_class .'" id="unchecked_value_wrapper">
				 <label for="unchecked_value" class="cctm_label cctm_text_label" id="unchecked_value_label">'
					. __('Value when Unchecked', CCTM_TXTDOMAIN) .
			 	'</label>
				 <input type="text" name="unchecked_value" size="8" class="'.$this->get_field_class('unchecked_value','text').'" id="unchecked_value" value="'.htmlspecialchars($def['unchecked_value']) .'"/>'
				 . $this->get_translation('unchecked_value') .'
			 	</div>';
		// Is Checked by Default?
		$out .= '<div class="'.self::wrapper_css_class .'" id="checked_by_default_wrapper">
				 <label for="checked_by_default" class="cctm_label cctm_checkbox_label" id="checked_by_default_label">'
					. __('Checked by default?', CCTM_TXTDOMAIN) .
			 	'</label>
				 <br />
				 <input type="checkbox" name="checked_by_default" class="'.$this->get_field_class('checked_by_default','checkbox').'" id="checked_by_default" value="1" '. $is_checked.'/> <span>'.$this->descriptions['checked_by_default'].'</span>
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
			 	<textarea name="description" class="'.$this->get_field_class('description','textarea').'" id="description" rows="5" cols="60">'
			 		. htmlentities($def['description'])
			 	.'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';
		return $out;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Here we do some smoothing of the checkbox warts... normally if the box is not
	 * checked, no value is sent in the $_POST array.  But that's a pain in the ass
	 * when it comes time to read from the database, so here we toggle between 
	 * 'checked_value' and 'unchecked_value' to force a value under all circumstances.
	 *
	 * See parent function for full documentation.
	 *
	 * @param mixed   	$posted_data  $_POST data
	 * @param string	$field_name: the unique name for this instance of the field
	 * @return	string	whatever value you want to store in the wp_postmeta table where meta_key = $field_name	
	 */
	public function save_post_filter($posted_data, $field_name) {
		if ( isset($posted_data[ FormElement::post_name_prefix . $field_name ]) ) {
			return $this->checked_value;
		}
		else {
			return $this->unchecked_value;
		}
	}
}
/*EOF*/