<?php
/**
 * @package CCTM_to_image_tag
 * 
 * Converts input (usually a JSON encoded string) into an array
 */

class CCTM_to_image_tag extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments
	 * @return mixed
	 */
	public function filter($input, $options='full') {
		return wp_get_attachment_image($input, $options);
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>to_image_tag</em> returns a full image tag for your image field. This is the default output filter for image fields starting with version 0.8.9. You can supply an option of "thumbnail", "medium", "large", "full" or a 2-item array representing width and height in pixels, e.g. array(32,32)', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return '<?php print_custom_field(\''.$fieldname.':to_image_tag\'); ?>';
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Full &lt;img&gt; tag', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/to_image_tag_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/