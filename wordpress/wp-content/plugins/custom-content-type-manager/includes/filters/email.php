<?php
/**
 * @package CCTM_email
 * 
 * Obscures a string (e.g. an email address) to make it more difficult for it to 
 * be harvested by bots.
 */

class CCTM_email extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments
	 * @return mixed
	 */
	public function filter($input, $options=null) {
		$output = '';
		for ($i = 0; $i < strlen($input); $i++) { 
			$output .= '&#'.ord($input[$i]).';'; 
		}
		return $output;
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>email</em> filter obscures an email address so it is still readable by a human, but more difficult for it to be harvested by a spam-bot.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return "<?php print_custom_field('$fieldname:email'); ?>";
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Email', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/email_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/