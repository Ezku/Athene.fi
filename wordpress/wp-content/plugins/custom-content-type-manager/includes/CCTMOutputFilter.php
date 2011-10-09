<?php
/**
 * @package CCTMOutputFilter
 *
 * Abstract class for standardizing output filters.
 */
abstract class CCTMOutputFilter {

	/**
	 * Most filters should be publicly visible, but some should only be used via direct invocation 
	 */
	public $show_in_menus = true;
	
	/**
	 * What kind of data can this filter accept? string|array|mixed
	 */
	public $input_type;
	
	/**
	 * What kind of data does this filter output? string|array|mixed 
	 */
	public $output_type;
	
	
	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments
	 * @return mixed
	 */
	abstract public function filter($input, $options=null);


	/**
	 * @return string	a description of what the filter is and does.
	 */
	abstract public function get_description();


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	abstract public function get_example($fieldname='my_field');


	/**
	 * @return string	the human-readable name of the filter.
	 */
	abstract public function get_name();

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	abstract public function get_url();
		
}
/*EOF*/