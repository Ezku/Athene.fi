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
class Search_Post_Excerpt extends Search_Post_Module {
	var $before = 20;
	var $after  = 40;
	
	function gather( $data ) {
		return apply_filters( 'the_excerpt', $data->post_excerpt );
	}
	
	function highlight( $post, $words, $content )	{
		global $search_spider;
		
		// First check if the excerpt is not the same as the content
		$highlight  = new Highlighter( $content, $words, true );
		
		if ( $highlight->has_matches() && $search_spider->has_highlighted_content == false ) {
			$highlight->zoom( $this->before, $this->after );
			$highlight->mark_words();
			
			return '<p><strong>'.__( 'Excerpt', 'search-unleashed' ).':</strong> '.$highlight->get().'</p>';
		}
		
		return '';
	}
	
	function field_name() {
		return 'post_excerpt';
	}
	
	function name()
	{
		return __( 'Post/page excerpt', 'search-unleashed' );
	}
	
	function load( $config ) {
		if ( isset( $config['before'] ) )
			$this->before = $config['before'];
			
		if ( isset( $config['after'] ) )
			$this->after = $config['after'];
	}
	
	function edit() {
		global $wpdb;
		
		$available = $wpdb->get_results( "SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key NOT LIKE '%_wp%' GROUP BY meta_key ORDER BY meta_key" );
		?>
		<tr>
			<th><?php _e( 'Characters before first match', 'search-unleashed' ); ?>:</th>
			<td>
				<input type="text" name="before" value="<?php echo $this->before ?>"/>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Characters after first match', 'search-unleashed' ); ?>:</th>
			<td>
				<input type="text" name="after" value="<?php echo $this->after ?>"/>
			</td>
		</tr>
		<?php
	}
	
	function save( $data ) {
		return array( 'before' => intval( $data['before'] ), 'after' => intval( $data['after'] ) );
	}
}
