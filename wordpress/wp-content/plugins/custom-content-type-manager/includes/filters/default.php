<?php
/**
 * @package CCTM_default
 * 
 * Adds a default value if the input value is empty.
 */

class CCTM_default extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments to return if the input is empty
	 * @return mixed
	 */
	public function filter($input, $options=null) {
		if (empty($input)) {
			return $options;
		}
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>default</em> filter kicks in only if the input is empty: whatever you specify as an option will be returned only if the input is empty.  This is one way to establish default values for your fields.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return "<?php print_custom_field('$fieldname:default', '<em>unknown</em>'); ?>";
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Set Default Value', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/default_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/