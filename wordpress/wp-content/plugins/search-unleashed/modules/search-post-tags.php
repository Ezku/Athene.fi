<?php
/**
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */
class Search_Post_Tags extends Search_Post_Module {
	var $show = true;
	
	function gather( $data ) {
		$newtags = array ();
		$tags    = get_the_tags( $data->ID );
		if ( $tags ) {
		
			foreach ( (array)$tags AS $tag ) {
				$newtags[] = $tag->name;
			}
		}
		
		return implode( ' ', $newtags );
	}

	function highlight( $post, $words, $content ) {
		if ( $this->show ) {
			$highlight = new Highlighter( $this->gather( $post ), $words, true );

			if ( $highlight->has_matches() ) {
				$highlight->mark_words();
		
				return '<p><strong>'.__( 'Tags', 'search-unleashed' ).':</strong> '.$highlight->get().'</p>';
			}
		}
	
		return '';
	}

	function name()	{
		return __( 'Tags', 'search-unleashed' );
	}
	
	function field_name() {
		return 'post_tags';
	}
	
	function load( $config ) {
		if ( isset( $config['show'] ) )
			$this->show = $config['show'];
	}

	function edit()	{
		?>
		<tr>
			<th align="right" valign="top"><?php _e( 'Show tag in results', 'search-unleashed' ); ?>:</th>
			<td>
				<input type="checkbox" name="show"<?php if ($this->show) echo ' checked="checked"' ?>/>
			</td>
		</tr>
		<?php
	}

	function save( $data ) {
		return array( 'show' => isset( $data['show'] ) ? true : false );
	}
}
