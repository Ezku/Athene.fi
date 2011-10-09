<?php
/**
 * @package CCTM_to_image_array
 * 
 * Obscures a string (e.g. an to_image_array address) to make it more difficult for it to 
 * be harvested by bots.
 */

class CCTM_to_image_array extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments
	 * @return mixed
	 */
	public function filter($input, $options=null) {
		return wp_get_attachment_image_src( $input, $options, true);
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>to_image_array</em> breaks down a referenced image into an array of its component parts: src, width, height.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return '<?php 
list($src, $w, $h) = get_custom_field(\''.$fieldname.':to_image_array\');
?>

<img src="<?php print $src; ?>" height="<?php print $h; ?>" width="<?php print $w ?>" />';
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Array of image src, width, height', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/to_image_array_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/