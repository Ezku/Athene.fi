<?php
/**
 * @package CCTM_to_link
 * 
 * Obscures a string (e.g. an to_link address) to make it more difficult for it to 
 * be harvested by bots.
 */

class CCTM_to_link extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments
	 * @return mixed
	 */
	public function filter($input, $options=null) {
		if ($input) {
			$post = get_post($input);
			$link_text = $post->post_title;
			if (!empty($options)) {
				$link_text = $option;
			}
			return sprintf('<a href="%s">%s</a>', $post->guid, $link_text);
		}
		else {
			return '';
		}	
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>to_link</em> filter takes a post ID and converts it into a full anchor tag. Be default, the post title will be used as the clickable text, but you can supply text to override this text.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return "<?php print_custom_field('$fieldname:to_link', 'Click here'); ?>";
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Full link &lt;a&gt; tag', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/to_link_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/