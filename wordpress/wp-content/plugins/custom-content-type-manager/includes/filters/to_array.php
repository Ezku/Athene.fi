<?php
/**
 * @package CCTM_to_array
 * 
 * Converts input (usually a JSON encoded string) into an array
 */

class CCTM_to_array extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments
	 * @return mixed
	 */
	public function filter($input, $options=null) {
		if (is_array($input)) {
			return $input; // nothing to do here.
		}
		$output = json_decode($input, true);
		// See http://code.google.com/p/wordpress-custom-content-type-manager/issues/detail?id=121
		if ( !is_array($output) ) {
			return array($output);
		}
		else {
			return $output;
		}
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>to_array</em> filter converts a JSON encoded string to a PHP array. It should be used on any multi-select field or any other field that stores multiple values.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return '<?php 
$my_array = get_custom_field(\''.$fieldname.'\');
foreach ($my_array as $item) {
	print $item;
}
?>';
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Array', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/to_array_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/