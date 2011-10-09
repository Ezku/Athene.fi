<?php
/**
 * @package CCTM_to_image_src
 * 
 * Converts input (usually a JSON encoded string) into an array
 */

class CCTM_to_image_src extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments
	 * @return mixed
	 */
	public function filter($input, $options='') {
		// we do this b/c default behavior is to return THIS post's guid if the $value is empty
		if ($input) {
			$post = get_post($input);
			return $post->guid;
		}
		else {
			return $options;
		}
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>to_image_src</em> filter converts a JSON encoded string to a PHP array. It should be used on any multi-select field or any other field that stores multiple values. You can optionally supply a default image src that will be used if there is no valid input.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return '<img src="<?php print_custom_field(\''.$fieldname.':to_image_src\'); ?>" />';
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Image src', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/to_image_src_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/