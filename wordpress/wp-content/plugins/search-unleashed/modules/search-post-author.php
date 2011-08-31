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
class Search_Post_Author extends Search_Post_Module {
	var $link = true;
	
	function gather( $data )
	{
		return $data->display_name;
	}
	
	function get_permalink( $id ) {
		return str_replace( get_bloginfo( 'home' ), '', get_permalink( $id ) );
	}
	
	function get_author( $word, $post ) {
		if ( $this->link === true )
			return sprintf( '<a href="%s">%s</a>', get_author_posts_url( $post->post_author ), $word );

		return $word;
	}
	
	function highlight( $post, $words, $content ) {
		$highlight = new Highlighter( get_the_author_meta('display_name', $post->post_author ), $words, true );

		if ( $highlight->has_matches() ) {
			$highlight->mark_words();
			
			return '<p><strong>'.__( 'Author', 'search-unleashed' ).':</strong> '.$this->get_author( $highlight->get(), $post ).'</p>';
		}
		
		return '';
	}
	
	function name()
	{
		return __( 'Post/page author', 'search-unleashed' );
	}
	
	function field_name() {
		return 'post_author';
	}
	
	function load( $config ) {
		if ( isset( $config['link'] ) )
			$this->link = $config['link'];
	}

	function edit() {
		?>
		<tr>
			<th><?php _e( 'Display link to author page', 'search-unleashed' ); ?>:</th>
			<td>
				<input type="checkbox" name="link"<?php if ( $this->link ) echo ' checked="checked"' ?>/>
			</td>
		</tr>
		<?php
	}

	function save( $data ) {
		return array( 'link' => isset( $data['link'] ) ? true : false );
	}
}
