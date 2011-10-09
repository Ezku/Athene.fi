<?php
/**
 * @package CCTM_formatted_list
 * 
 * 
 */

class CCTM_formatted_list extends CCTMOutputFilter {

	/**
	 * Apply the filter.
	 *
	 * @param 	mixed 	input
	 * @param	mixed	formatting parameters
	 * @return mixed
	 */
	public function filter($input, $options=null) {
		
		$array = array();
		if (is_array($input)) {
			$array = $input;
		}
		else {
			$input = json_decode(html_entity_decode($input), true );
			if (is_array($input)) {
				$array = $input;
			}
			else {
				$array = array($input);
			}
		}
				
		if ( !empty($options) && is_array($options) ) {
			$out = '';
			// format each value
			if ( isset($options[0]) ) {
				foreach ( $array as $v ) {
					$hash['value'] = $v;
					$out .= CCTM::parse($options[0], $hash);
				}
			}
			else {
				// ??? user supplied an associative array?
				return 'Options array in incorrect format!';
			}
			
			// wrap the output
			if ( isset($options[1]) ) {
				$hash['content'] = $out;
				return CCTM::parse($options[1], $hash);		
			}			
		}
		// Simple string separator 
		elseif (!empty($options) && !is_array($options) ) {
			foreach ( $array as $i => $item ) {
				$array[$i] = htmlspecialchars($item);
			}
			return implode($options, $array);
		}
		// Default behavior: use a comma
		else {
			return implode(', ', $array);
		}
	}


	/**
	 * @return string	a description of what the filter is and does.
	 */
	public function get_description() {
		return __('The <em>formatted_list</em> filter converts a JSON array into a formatted string such as an HTML list. See the info page for formatting options.', CCTM_TXTDOMAIN);
	}


	/**
	 * Show the user how to use the filter inside a template file.
	 *
	 * @return string 	a code sample 
	 */
	public function get_example($fieldname='my_field') {
		return "<?php print_custom_field('$fieldname:formatted_list', array('<li>[+value+]</li>','<ul>[+content+]</ul>') ); ?>";
	}


	/**
	 * @return string	the human-readable name of the filter.
	 */
	public function get_name() {
		return __('Formatted List', CCTM_TXTDOMAIN);
	}

	/**
	 * @return string	the URL where the user can read more about the filter
	 */
	public function get_url() {
		return __('http://code.google.com/p/wordpress-custom-content-type-manager/wiki/formatted_list_OutputFilter', CCTM_TXTDOMAIN);
	}
		
}
/*EOF*/