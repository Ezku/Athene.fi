<?php
/**
* CCTM_date
*
* Implements a date input using the jQuery datepicker: 
* http://jqueryui.com/demos/datepicker/
*
*/
class CCTM_date extends FormElement
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
		'date_format'	=> '',
		'default_value' => '',
		'eval_default_value' => 0,
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
		return __('Date',CCTM_TXTDOMAIN);	
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
		return __('Use date fields to store dates, including years, months, and days.',CCTM_TXTDOMAIN);
	}
	
	//------------------------------------------------------------------------------
	/**
	* This function should return the URL where users can read more information about
	* the type of field that they want to add to their post_type. The string may
	* be localized using __() if necessary (e.g. for language-specific pages)
	* @return	string 	e.g. http://www.yoursite.com/some/page.html
	*/
	public function get_url() {
		return 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Date';
	}
	

	//------------------------------------------------------------------------------
	/**
	* Optionally evals the default value
	*/
	public function get_create_field_instance() {
		if( $this->props['evaluate_default_value'] ) {			
			$default_value = $this->default_value;
			$this->default_value = eval("return $default_value;"); 
		}
		
		$output .= $this->wrap_description($this->props['description']);
		
		return $this->get_edit_field_instance($this->default_value); 
	}


	//------------------------------------------------------------------------------
	/**
	 *
	 * @param mixed $current_value	current value for this field.
	 * @return string
	 */
	public function get_edit_field_instance($current_value) {
		#print_r($this->props); exit;
		$output = '
			<script>
				jQuery(function() {
					jQuery("#'.$this->get_field_id().'").datepicker({
						dateFormat : "'.$this->props['date_format'].'"
					});
				});
			</script>';
			
		$output .= sprintf('
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
	}

	//------------------------------------------------------------------------------
	/**
	 *
	 * @param mixed $def	field definition; see the $props array
	 */
	public function get_edit_field_definition($def) {
		$is_checked = '';
		if ($def['evaluate_default_value']) {
			$is_checked = 'checked="checked"';
		}
		
		// Option - select
		$date_format = array();
		if ( $def['date_format'] == 'mm/dd/yy' ) {
			$date_format['mm/dd/yy'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'yyyy-mm-dd' ) {
			$date_format['yyyy-mm-dd'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'yy-mm-dd' ) {
			$date_format['yy-mm-dd'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'd M, y' ) {
			$date_format['d M, y'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'd MM, y' ) {
			$date_format['d MM, y'] = 'selected="selected"';
		}
		if ( $def['date_format'] == 'DD, d MM, yy' ) {
			$date_format['DD, d MM, yy'] = 'selected="selected"';
		}
		if ( $def['date_format'] == "'day' d 'of' MM 'in the year' yy" ) {
			$date_format["'day' d 'of' MM 'in the year' yy"] = 'selected="selected"';
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
			 	
		// Default Value
		$out .= '<div class="'.self::wrapper_css_class .'" id="default_value_wrapper">
			 	<label for="default_value" class="cctm_label cctm_text_label" id="default_value_label">'
			 		.__('Default Value', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="default_value" class="'.$this->get_field_class('default_value','text').'" id="default_value" value="'. htmlspecialchars($def['default_value'])
			 		.'"/>
			 	' . $this->get_translation('default_value') .'
			 	</div>';
		// Evaluate Default Value (use PHP eval)
		$out .= '<div class="'.self::wrapper_css_class .'" id="evaluate_default_value_wrapper">
				 <label for="evaluate_default_value" class="cctm_label cctm_checkbox_label" id="evaluate_default_value_label">'
					. __('Use PHP eval to calculate the default value?', CCTM_TXTDOMAIN) .
			 	'</label>
				 <br />
				 <input type="checkbox" name="evaluate_default_value" class="'.$this->get_field_class('evaluate_default_value','checkbox').'" id="evaluate_default_value" value="1" '. $is_checked.'/> '
				 	.$this->descriptions['evaluate_default_value'].'
			 	</div>';

		
		// Extra
		$out .= '<div class="'.self::wrapper_css_class .'" id="extra_wrapper">
			 		<label for="extra" class="'.self::label_css_class.'">'
			 		.__('Extra', CCTM_TXTDOMAIN) .'</label>
			 		<input type="text" name="extra" class="'.$this->get_field_class('extra','text').'" id="extra" value="'
			 			.htmlentities($def['extra']).'"/>
			 	' . $this->get_translation('extra').'
			 	</div>';

		// Date Format
		$out .= '<div class="'.self::wrapper_css_class .'" id="date_format_wrapper">
			 		<label for="date_format" class="'.self::label_css_class.'">'
			 		.__('Date Format', CCTM_TXTDOMAIN) .'</label>
					<select id="date_format" name="date_format">
						<option value="mm/dd/yy" '.$date_format['mm/dd/yy'].'>Default - mm/dd/yy</option>
						<option value="yy-mm-dd" '.$date_format['yy-mm-dd'].'>MySQL - yyyy-mm-dd</option>
						<option value="yy-mm-dd" '.$date_format['yy-mm-dd'].'>ISO 8601 - yy-mm-dd</option>
						<option value="d M, y" '.$date_format['d M, y'].'>Short - d M, y</option>
						<option value="d MM, y" '.$date_format['d MM, y'].'>Medium - d MM, y</option>
						<option value="DD, d MM, yy" '.$date_format['DD, d MM, yy'].'>Full - DD, d MM, yy</option>
						<option value="\'day\' d \'of\' MM \'in the year\' yy" '.$date_format["'day' d 'of' MM 'in the year' yy"].'>With text - \'day\' d \'of\' MM \'in the year\' yy</option>
						<option value="d.m.yy" '.$date_format['d.m.yy'].'>Finnish - d.m.yy</option>
					</select>
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
			 		.htmlentities($def['description'])
			 	.'</textarea>
			 	' . $this->get_translation('description').'
			 	</div>';
		return $out;
	}
}


/*EOF*/