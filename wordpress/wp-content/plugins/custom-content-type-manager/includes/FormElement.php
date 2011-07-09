<?php
/**
 * @package FormElement
 *
 * This class can be extended for each type of custom field, e.g. dropdown, textarea, etc.
 * so that instances of these field types can be created and attached to a post_type.
 * The notion of a "class" or "object" has two layers here: First there is a general class
 * of form element (e.g. dropdown) which is implemented inside of a given post_type. E.g.
 * a "State" dropdown might be attached to an "Address" post_type. Secondly, instances of 
 * the post_type create instances of the "State" field are created with each "Address" post.
 * The second layer here is really another way of saying that each field has its own value.
 *
 * The functions in this class serve the following primary purposes:
 *		1.	Generate forms which allow a custom field definition to be created and edited.
 * 		2. 	Generate form elements which allow an instance of custom field to be displayed
 *			when a post is created or edited
 *		3.	Retrieve and filter the meta_value stored for a given post and return it to the
 *			theme file, e.g. if an image id is stored in the meta_value, the filter function
 *			can translate this id into a full image tag.
 *
 * When a new type of custom field is defined, all the abstract functions must be implemented.
 * This is how we force the children classes to implement their own behavior. Bruhaha.
 * Usually the forms to create and edit a definition or element are the same, but if needed,
 * there are separate functions to create and edit a definition or value.
 * 
 */


abstract class FormElement {

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
	public $element_i = 0; // used to increment CSS ids as we wrap multiple elements
	
	// Contains reusable localized descriptions of common field definition elements, e.g. 'label'
	public $descriptions = array();
	
	// Stores any errors with fields.  The format here is array( 'field_name' => array('Error msg1','Error msg2') )
	public $errors = array();

	// the magic __set and __get are mapped to keys in this array.
	public $props = array();

	// Any extension of this class can list zero or many function names from the OutputFilters class
	// This determines which (if any) output filters are available. 'none' (i.e. straight input==>output) 
	// is always available.
	public $supported_output_filters = array();
	
	// Definition vars from $props that you don't want any dev in a child class to change
	// during runtime.
	private $protected_instance_vars = array('sort_param', 'name');

	// Added to each key in the $_POST array, e.g. $_POST['cctm_firstname']
	const post_name_prefix 	= 'cctm_';
	const css_class_prefix 	= 'cctm_';
	const css_id_prefix 	= 'cctm_';

	
	/* Always include this CSS class in generated input labels, e.g. 
	<label for="xyz" class="cctm_label cctm_text_label" id="xyz_label">
		Address</label>
	*/
	const wrapper_css_class 		= 'cctm_element_wrapper';
	const label_css_class 			= 'cctm_label';
	const label_css_id_prefix 		= 'cctm_label_';
	const css_class_description 	= 'cctm_description';
	const error_css 				= 'cctm_error'; // used for validation errors
	
	//------------------------------------------------------------------------------
	/**
	 * Add additional items if necessary, e.g. localizations of the $props by 
	 * tying into the parent constructor, e.g.  
	 *
	 * 	public function __construct() {
	 *		parent::__construct();
	 *		$this->props['special_stuff'] = __('Translate me');
	 *	}
	 * 	
	 */	
	 public function __construct() {
				
		// Run-time Localization
		
		$this->descriptions['button_label'] = __('How should the button be labeled?', CCTM_TXTDOMAIN);
		$this->descriptions['class'] = __('Add a CSS class to instances of this field. Use this to customize styling in the WP manager.', CCTM_TXTDOMAIN);
		$this->descriptions['extra'] = __('Any extra attributes for this text field, e.g. <code>size="10"</code>', CCTM_TXTDOMAIN);
		$this->descriptions['default_option'] = __('The default option will appear selected. Make sure it matches a defined option.', CCTM_TXTDOMAIN);
		$this->descriptions['default_value'] = __('The default value is presented to users when a new post is created.', CCTM_TXTDOMAIN);
		$this->descriptions['description'] = __('The description is visible when you view all custom fields or when you use the <code>get_custom_field_meta()</code> function.');
		$this->descriptions['description'] .= __('The following html tags are allowed:')
			. '<code>'.htmlentities(CCTM::$allowed_html_tags).'</code>';
		$this->descriptions['evaluate_default_value'] = __('You can check this box if you want to enter a bit of PHP code into the default value field.');
		$this->descriptions['label'] = __('The label is displayed when users create or edit posts that use this custom field.', CCTM_TXTDOMAIN);
		$this->descriptions['name'] = __('The name identifies the meta_key in the wp_postmeta database table. The name should contain only letters, numbers, and underscores. You will use this name in your template functions to identify this custom field.', CCTM_TXTDOMAIN);
		$this->descriptions['checked_value'] = __('What value should be stored in the database when this checkbox is checked?', CCTM_TXTDOMAIN);
		$this->descriptions['unchecked_value'] =  __('What value should be stored in the database when this checkbox is unchecked?', CCTM_TXTDOMAIN);
		$this->descriptions['checked_by_default'] =  __('Should this field be checked by default?', CCTM_TXTDOMAIN);
		$this->descriptions['output_filter'] =  __('How should values be displayed in your theme files?', CCTM_TXTDOMAIN);
		
	}


	//------------------------------------------------------------------------------
	/**
	 * @param string $k
	 * @return string
	 */
	public function __get($k) {
		if ( isset($this->props[$k]) ) {
			return $this->props[$k];
		}
		else {
			return ''; // Error?
		}
	}

	//------------------------------------------------------------------------------
	/**
	 * @param string $k
	 * @return boolean
	 */

	public function __isset($k) {
		if ( isset($this->props[$k]) ) {
			return true;
		}
		else {
			return false; 
		}
	}

	//------------------------------------------------------------------------------
	/**
	 *
	 *
	 * @param string $k representing the attribute name
	 * @param mixed $v value for the requested attribute
	 */
	public function __set($k, $v) {
		if ( !in_array($k, $this->protected_instance_vars) ) {
			$this->props[$k] = $v;
		}
	}


	//! Abstract Functions
	//------------------------------------------------------------------------------
	/**
	* This function gives a description of this type of field so users will know 
	* whether or not they want to add this type of field to their custom content
	* type. The string should be no longer than 255 characters. 
	* The returned value should be localized using the __() function.
	* @return	string	plain text description
	*/
	abstract public function get_description();

	//------------------------------------------------------------------------------
	/**
	* get_example_image
	* 
	* This function should return a URL to a sample image so users can see an example
	* of this type of field in action. The image should be in a web-friendly format:
	* (jpg, png, gif) and it should be respectfully small in dimensions and filesize.
	*
	* @return	string	e.g. 'http://yoursite.com/images/example.jpg'
	*/
	abstract public function get_example_image();

	//------------------------------------------------------------------------------
	/**
	* This function provides a name for this type of field. This should return plain
	* text (no HTML). The string should be no longer than 32 characters.
	* The returned value should be localized using the __() function.
	* @return	string
	*/
	abstract public function get_name();
		
	//------------------------------------------------------------------------------
	/**
	* This function should return the URL where users can read more information about
	* the type of field that they want to add to their post_type. The string may
	* be localized using __() if necessary (e.g. for language-specific pages)
	* @return	string 	e.g. http://www.yoursite.com/some/page.html
	*/
	abstract public function get_url();
	

	
	//------------------------------------------------------------------------------
	/**
	 * get_create_field_instance
	 * 
	 * This generates the field elements when a user creates a new post that uses a 
	 * field of this type.  In most cases, the form elements generated for a new post
	 * are identical to the form elements generated when editing a post, so the default
	 * behavior is to set the current value to the default value and hand this off to 
	 * the get_edit_field_instance() function.
	 *
	 * Override this function in the rare cases when you need behavior that is specific 
	 * to when you first create a post (e.g. to specify a special default value). 
	 * Most of the time, the create/edit functions are nearly identical.
	 *
	 * @return string HTML field(s)
	 */
	public function get_create_field_instance() {
		return $this->get_edit_field_instance($this->default_value); 
	}

	/**
	 * get_edit_field_instance
	 *
	 * The form returned is what is displayed when a user is creating a post that contains
	 * an instance of this field type.
	 *
	 * @param	string	$current_value is the current value for the field, as stored in the 
	 *					wp_postmeta table for the post being edited.
	 * @return	string	HTML element.
	 */
	abstract public function get_edit_field_instance($current_value);

	//------------------------------------------------------------------------------
	/**
	 * This should return (not print) form elements that handle all the controls 
	 * required to define this type of field.  The default properties correspond to 
	 * this class's public variables, e.g. name, label, etc. The form elements you 
	 * create should have names that correspond to the public $props variable. A 
	 * populated array of $props will be stored alongside the custom-field data for 
	 * the parent post-type. (See notes on the CCTM data structure).
	 * 
	 * Override this function in the rare cases when you need behavior that is specific 
	 * to when you first define a field definition. Most of the time, the create/edit 
	 * functions are nearly identical. When you create a field definition, the
	 * current values are the values hard-coded into the $props array at the top
	 * of the child FieldElement class; when editing a field definition, the current
	 * values are read from the database (the array should be the same structure as 
	 * the $props array, but the values may differ).
	 *
	 * @return	string	HTML input fields
	 */
	public function get_create_field_definition() {
		return $this->get_edit_field_definition( $this->props );
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
	abstract public function get_edit_field_definition($current_values);




	//! Protected Functions
	//------------------------------------------------------------------------------
	/**
	 * Generate a CSS class for this type of field, typically keyed off the actual HTML
	 * input type, e.g. text, textarea, submit, etc.
	 * 
	 * This is dynamic so we can flag fields that have failed error validation.
	 
	 cctm_text
	 cctm_my_text_field
	 cctm_error
	 
	 *
	 * @param string  $id: unique id for the field 
	 * @return string a string representing a CSS class.
	 *
	 */
	protected function get_field_class( $id, $input_type='text' ) {
		// cctm_text
		// TODO!!! 
		$css_arr = array();
		# in_array(mixed needle, array haystack [, bool strict])
		$errors = array_keys($this->errors);
		if ( in_array( $id, $errors ) ) {
			$css_arr[] = self::error_css;
		}

		$css_arr[] = self::css_class_prefix . $id;
		$css_arr[] = self::css_class_prefix . $input_type;
		return implode(' ', $css_arr);
	}

	//------------------------------------------------------------------------------
	/**
	 * We need special behavior when we are creating and editing posts because 
	 * WP uses all kinds of form inputs and classes, so it's easy for names and
	 * CSS classes to collide.
	 *
	 * @return string
	 */
	protected function get_field_id() {
		$backtrace = debug_backtrace();
		$calling_function = $backtrace[1]['function'];
		switch ($calling_function) {
			case 'get_create_field_instance':
			case 'get_edit_field_instance':
			case 'wrap_label':
				return self::css_id_prefix . $this->name;
				break;
			case 'get_edit_field_definition':
			case 'get_create_field_definition':
			default: 
				return $this->name;
		}
	}

	//------------------------------------------------------------------------------
	/**
	* get_field_name
	*
	* This function gets an input's name for use while a post is being edited or created.
	* We offer this function so we can pre-pend the names with a custom prefix to ensure
	* that no naming collisions occur inside the $_POST array.
	*
	* Behavior is determined by the function that calls this: see 
	* http://bytes.com/topic/php/answers/221-function-global-var-return-name-calling-function
	* @param	string	$name is the name of a field, e.g. 'my_name' in <input type="text" name="my_name" />
	* @return	string	A name safe for the context in which it was called.
	*/
	protected function get_field_name() {
		$backtrace = debug_backtrace();
		$calling_function = $backtrace[1]['function'];
		
		switch ($calling_function) {
			case 'get_create_field_instance':
			case 'get_edit_field_instance':
			case 'wrap_label':
				return self::post_name_prefix . $this->name;
				break;
			case 'get_edit_field_definition':
			case 'get_create_field_definition':
			default: 
				return $this->name;
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Wraps a description with a unified bit of styling.
	 */
	protected function wrap_description($str) {
		return sprintf('<span class="cctm_description">%s</span>', $str);
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Use this function to wrap the HTML for a single form element in a div.
	 * @param	string	$html	The HTML that generates the info for a particular element,
	 *							typically an HTML <input> and its <label>
	 * @param	string	$class	Optional CSS class to further define the wrapper <div>
	 * @return	string	The input $html wrapped in a div.
	 */
	protected function wrap_element($html, $class='') {
		$wrapper = '
		<div class="cctm_element_wrapper %s" id="custom_field_wrapper_%s">
		%s
		</div>';
		$this->element_i = $this->element_i + 1;
		return sprintf($wrapper, $class, $this->element_i, $html);	
	}
	

	/**
	 * This function returns an HTML label that wraps the label attribute for the instance of
	 * of this element.
	 * I added some carriage returns here for readability in the generated HTML
	 *
<label for="description" class="cctm_label cctm_textarea_label" id="cctm_label_description">Description</label>	 
	 * @param	string $additional_class any extra CSS class(es) you want to pass to this label
	 * @return string	HTML representing the label for this field.
	 */
	protected function wrap_label($additional_class='') {
		$wrapper = '
		<label for="%s" class="%s" id="%s">
			%s
		</label>
		';
		return sprintf($wrapper
			, $this->get_field_id()
			, trim(self::label_css_class . ' ' . self::css_class_prefix . $this->props['type'] . ' '.$additional_class)
			, self::label_css_id_prefix . $this->props['name']
			, $this->props['label']
		);  # TODO: __('label', ????) localized
	}


	//------------------------------------------------------------------------------
	/**
	 * This wraps the $input in a div with appropriate styling.
	 *
	 * @param string  $input is the contents of the field, needing
	 * @return sting	HTML representing the full HTML content for this field instance.
	 */
	protected function wrap_outer($input) {
		$wrapper = '
		<div class="cctm_element_wrapper" id="custom_field_%s">
		%s
		</div>';
		return sprintf($wrapper, $this->props['name'], $input);
	}



	//! Public Functions
	//------------------------------------------------------------------------------
	/**
	* Formats errors
	* @return string HTML describing any errors tracked in the class $errors variable
	*/
	public function format_errors() {
		$error_str = '';
		foreach ( $this->errors as $tmp => $errors ) {
			foreach ( $errors as $e ) {
				$error_str .= '<li>'.$e.'</li>
				';	
			}				
		}

		return sprintf('<div class="error">
			<h3>%1$s</h3>
			<ul style="margin-left:30px">
				%2$s
			</ul>
			</div>'
			, __('There were errors in your custom field definition.', CCTM_TXTDOMAIN)
			, $error_str
		);
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Generate select dropdown for listing and selecting the active output filter.
	 * @param mixed	$def is the existing field definition
	 */
	public function get_available_output_filters($def) {
	
		include_once('OutputFilters.php');
		$OutputFilters = new OutputFilters();
		
		$out = '<div class="'.self::wrapper_css_class .'" id="output_filter_wrapper">
			 	<label for="output_filter" class="cctm_label cctm_select_label" id="output_filter_label">'
			 		.__('Output Filter', CCTM_TXTDOMAIN) .'
			 		<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/OutputFilters" target="_blank"><img src="'.CCTM_URL .'/images/question-mark.gif" width="16" height="16" /></a>
			 		</label>';
		
		$out .= '<select name="output_filter" class="'
				.$this->get_field_class($this->name, 'select') . ' ' . $this->class.'" id="'.$this->get_field_id().'">
				<option value="">'.__('None (raw)').'</option>
				';
			foreach ($this->supported_output_filters as $opt) {
				$is_selected = '';
				if ( $def['output_filter'] == $opt ) {
					$is_selected = 'selected="selected"';
				}
				$out .= '<option value="'.$opt.'" '.$is_selected.'>'.$OutputFilters->descriptions[$opt] .'</option>';
			}
		$out .= '</select>
			' . $this->get_translation('output_filter') 
			  .'</div>';

		return $out;
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Get the full image tag for this field-type's icon.  The icon should be 48x48.
	 * Default behavior is to look inside the images/custom-fields directory
	 *
	 * @return string full HTML <img> tag for this field-type's icon.
	 */
	public function get_icon() {

		$field_type = str_replace(
			CCTM::FormElement_classname_prefix,
			'',
			get_class($this) );
		$dir = CCTM::get_custom_icons_src_dir();
		$icon_src = $dir . $field_type .'.png';

		// Use the default image if necessary
		if ( !CCTM::is_valid_img($icon_src) ) {
			$icon_src = CCTM_URL.'/images/custom-fields/default.png';
		}

		return sprintf('<img src="%s" class="cctm-field-icon" id="cctm-field-icon-%s"/>', $icon_src, $field_type);
	}
	
	
	//------------------------------------------------------------------------------
	/**
	* A little clearing house for getting wrapped translations for various components
	*
	* @param	string	$item to identify which description you want.
	* @return	string	HTML localized description
	*/
	public function get_translation($item) {
		$tpl = '<span class="cctm_description">%s</span>';		 
		 return sprintf($tpl, $this->descriptions[$item]);
	}
 
 	//------------------------------------------------------------------------------
 	//------------------------------------------------------------------------------
 	/**
 	* @param	string	$html string, with linebreaks, quotes, etc.
 	* @return	string	Filtered: linebreaks removed, quotes escaped.
 	*/
 	public static function make_js_safe($html) {
 		$html = preg_replace("/\n\r|\r\n|\r|\n/",'',$html);
 		$html = preg_replace( '/\s+/', ' ', $html );
 		$html = addslashes($html);
 		$html = trim($html);
 	}
 	
	//------------------------------------------------------------------------------
	/**
	 * This function acts as a per-fieldtype filter for the front-end for any given
	 * FormElement so that any type of custom field can convert whatever value is stored
	 * in the datbase into a value that's appropriate for the front-end. 
	 * This function is called from the theme function: get_custom_field()
	 *
	 * The output of this function should be a string.  If you need more complex outputs,
	 * utilize the $extra parameters and set its values directly. It's passed by reference,
	 *  so any edits to $extra will be visible to the caller.
	 *
	 * Example of custom handling per field type:
	 * $img_atts = array();
	 * $img_html = get_custom_field('my_img_field', $img_atts);
	 * print $img_html; // prints <img src="/path/to/image.jpg" />
	 * print_r($img_atts); // prints Array('src'=>'/path/to/image.jpg', 'h'=>'100', 'w' => '50')
	 *
	 * Override this function to provide special output filtering on a
	 *  field-type basis.
	 *
	 * @param string  $value is whatever was stored in the database for this field for the current post
	 * @param mixed $options (reference)
	 * @return string
	 */
	public function output_filter($value, $options) {

		if ( !empty($this->supported_output_filters) && !empty($this->props['output_filter']) ) {
			include_once('OutputFilters.php');
			$OutputFilters = new OutputFilters();
			$filter = $this->props['output_filter']; 		
			return $OutputFilters->$filter($value,$options);
		}
		else
		{
			return $value;
		}	
	}

	
	//------------------------------------------------------------------------------
	/**
	 * This function allows for custom handling of submitted post/page data just before
	 * it is saved to the database. Data validation and filtering should happen here,
	 * although it's difficult to enforce any validation errors.
	 *
	 * Note that the field name in the $_POST array is prefixed by FormElement::post_name_prefix,
	 * e.g. the value for you 'my_field' custom field is stored in $_POST['cctm_my_field']
	 * (where FormElement::post_name_prefix = 'cctm_').
	 *
	 * Output should be whatever string value you want to store in the wp_postmeta table
	 * for the post in question. This function will be called after the post/page has
	 * been submitted: this can be loosely thought of as the "on save" event
	 *
	 * @param mixed   	$posted_data  $_POST data
	 * @param string	$field_name: the unique name for this instance of the field
	 * @return	string	whatever value you want to store in the wp_postmeta table where meta_key = $field_name	
	 */
	public function save_post_filter($posted_data, $field_name) {
		if ( isset($posted_data[ FormElement::post_name_prefix . $field_name ]) ) {
			return trim($posted_data[ FormElement::post_name_prefix . $field_name ]);
		}
		else {
			return '';
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	 * Validate and sanitize any submitted data. Used when editing the definition for 
	 * this type of element. Default behavior here is require only a unique name and 
	 * label. Override this if customized validation is required: usually you'll want
	 * to override and still reference the parent:
	 * 		public function save_definition_filter($posted_data, $post_type) {
	 *			$posted_data = parent::save_definition_filter($posted_data, $post_type);
	 *			// your code here...
	 *			return $posted_data;
	 *		}
	 *
	 *
	 * @param	array	$posted_data = $_POST data
	 * @param	string	$post_type the string defining this post_type
	 * @return	array	filtered field_data that can be saved OR can be safely repopulated
	 *					into the field values.
	 */
	public function save_definition_filter($posted_data, $post_type) {
	
		if ( empty($posted_data['name']) ) {
			$this->errors['name'][] = __('Name is required.', CCTM_TXTDOMAIN);
		}
		else {
			// Are there any invalid characters? 1st char. must be a letter (req'd for valid prop/func names)
			if ( !preg_match('/^[a-z]{1}[a-z_0-9]*$/i', $posted_data['name'])) {
				$this->errors['name'][] = sprintf(
					__('%s contains invalid characters. The name may only contain letters, numbers, and underscores, and it must begin with a letter.', CCTM_TXTDOMAIN)
					, '<strong>'.$posted_data['name'].'</strong>');
				$posted_data['name'] = preg_replace('/[^a-z_0-9]/', '', $posted_data['name']);
			}
			// Is the name too long?
			if ( strlen($posted_data['name']) > 20 ) {
				$posted_data['name'] = substr($posted_data['name'], 0 , 20);
				$this->errors['name'][] = __('The name is too long. Names must not exceed 20 characters.', CCTM_TXTDOMAIN);
			}
			// Run into any reserved words?
			if ( in_array($posted_data['name'], CCTM::$reserved_field_names ) ) {
				$this->errors['name'][] = sprintf(
					__('%s is a reserved name.', CCTM_TXTDOMAIN)
					, '<strong>'.$posted_data['name'].'</strong>');
				$posted_data['name'] = '';	
			}
			
			// Is that name already in use? 
			// if the original field_name is not empty, then we're editing an existing field.
			// if it's an edit, the name changed, and it's equal to an existing name ==> error.
			if ( !empty($this->original_name)
				&& $this->original_name != $posted_data['name'] // i.e. if the name changed
				&& is_array(CCTM::$data[$post_type]['custom_fields']) 
				&& in_array( $posted_data['name'], array_keys(CCTM::$data[$post_type]['custom_fields']) ) ) {
					$this->errors['name'][] = sprintf( __('The name %s is already in use. Please choose another.', CCTM_TXTDOMAIN), '<em>'.$posted_data['name'].'</em>');
					$posted_data['name'] = '';
			}
		}
		
		
		// You may need to do this for any textarea fields. Saving a '</textarea>' tag
		// in your description field can wreak everything.
		if ( !empty($posted_data['description']) ) {
			$posted_data['description'] = strip_tags($posted_data['description'], CCTM::$allowed_html_tags);
		}

		$posted_data = CCTM::striptags_deep($posted_data);
		if ( get_magic_quotes_gpc() ) {
			$posted_data = CCTM::stripslashes_deep($posted_data);
		}

/*
		if ( empty($posted_data['label']) ) {
			$this->errors['label'][] = __('Label is required.', CCTM_TXTDOMAIN);
		}
		else {
			// print 'aqui' ; exit;
			$posted_data['label'] = stripslashes($posted_data['label']);
		}
*/
					
		return $posted_data; // filtered data
	}


}
/*EOF FormElement.php */