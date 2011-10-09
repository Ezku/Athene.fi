<?php
/**
 * @package CCTM_to_link_href
 * 
 * Obscures a string (e.g. an to_link_href address) to make it more difficult for it to 
 * be harvested by bots.
 */

class CCTM_to_link_href extends CCTMOutputFilter {

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
		return __('The <em>to_link_href</em> filter takes a post ID and converts a post ID into the link href to that post. Optionally, you can supply the href to a page that will be used if no valid input is detected.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return '<a href="<?php print_custom_field(\''.$fieldname.':to_link_href\',\'http://yoursite.com/default/page/\');?>">Click here</a>';
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Link href only', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/to_link_href_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/