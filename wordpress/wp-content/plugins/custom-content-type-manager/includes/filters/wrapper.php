<?php
/**
 * @package CCTM_wrapper
 * 
 * Wraps an input string only if it isn't empty.
 */

class CCTM_wrapper extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	optional arguments defining how to wrap the input
	 * @return mixed
	 */
	public function filter($input, $options=null) {
		if (empty($options) || empty($input)) {
			return $input;
		}
		// array of before, after
		elseif( is_array($options)) {
			$before = '';
			$after = '';
			if (isset($options[0])) {
				$before = $options[0];
			}
			if (isset($options[1])) {
				$after = $options[1];
			}
			
			return $before . $input . $after;
		}
		// formatting string
		else {
			return CCTM::parse($options, array('content' => $input));
		}
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>wrapper</em> filter will wrap non-empty input. This allows you add extra markup to non-empty values and let empty values pass through. Pass this filter either a formatting string that uses the &#91;+content+&#93; placeholder, or supply a 2 element array that specifies text that will appear before and after the input text.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return "<?php print_custom_field('$fieldname:wrapper', array('<span class=\"my_class\"><strong>$fieldname</strong>:','</span>') ); ?>";
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Wrapper', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/wrapper_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/