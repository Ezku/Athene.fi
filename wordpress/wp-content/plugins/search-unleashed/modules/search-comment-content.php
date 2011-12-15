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

class Search_Comment_Content extends Search_Comment_Module {
	var $before  = 20;
	var $after   = 40;
	var $include = 'link';
	
	function gather( $data ) {
		return apply_filters( 'comment_text', apply_filters( 'get_comment_text', $data->comment_content ) );
	}
	
	function highlight( $post, $words, $content ) {
		if ( isset( $post->comment_id ) ) {
			$comment   = get_comment( $post->comment_id );
			$highlight = new Highlighter( $this->gather( $comment ), $words, true );

			if ( $highlight->has_matches() ) {
				$highlight->zoom( $this->before, $this->after );
				$highlight->mark_words();
			
				if ( $this->include == 'content' )
					return sprintf( __( '<p><strong>Comment:</strong> %s</p>', 'search-unleashed' ), $highlight->get() );
				else if ($this->include == 'link')
					return sprintf( __( '<p><strong>Comment by %s:</strong> %s</p>', 'search-unleashed' ), $this->get_comment_author_link( $comment ), $highlight->get() );
				else if ($this->include == 'name')
					return sprintf( __( '<p><strong>Comment by %s:</strong> %s</p>', 'search-unleashed' ), apply_filters( 'comment_author', apply_filters( 'get_comment_author', $comment->comment_author ) ), $highlight->get() );
			}
		}
		
		return '';
	}
	
	function get_comment_author_link( $comment ) {
		$url    = apply_filters( 'get_comment_author_url', $comment->comment_author_url );
		$author = apply_filters( 'comment_author', apply_filters( 'get_comment_author', $comment->comment_author ) );

		if ( empty( $url ) || 'http://' == $url )
			$return = $author;
		else
			$return = "<a href='$url' rel='external nofollow'>$author</a>";
		return apply_filters( 'get_comment_author_link', $return );
	}
	
	function name () {
		return __( 'Comment content', 'search-unleashed' );
	}
	
	function field_name() {
		return 'comment_content';
	}

	function load( $config ) {
		if ( isset( $config['before'] ) )
			$this->before = $config['before'];
			
		if ( isset( $config['after'] ) )
			$this->after = $config['after'];
			
		if ( isset( $config['include'] ) )
			$this->include = $config['include'];
	}
	
	function edit () {
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
		<tr>
			<th><?php _e( 'Include in match', 'search-unleashed' ); ?>:</th>
			<td>
				<select name="include">
					<option value="content"<?php if ($this->include == 'content') echo ' selected="selected"' ?>><?php _e( 'Just content', 'search-unleashed' ); ?></option>
					<option value="name"   <?php if ($this->include == 'name') echo ' selected="selected"' ?>><?php _e( "Content &amp; author's name", 'search-unleashed' ); ?></option>
					<option value="link"   <?php if ($this->include == 'link') echo ' selected="selected"' ?>><?php _e( "Content, author's name, and link to author's site", 'search-unleashed' ); ?></option>
				</select>
			</td>
		</tr>
		<?php
	}
	
	function save( $data ) {
		return array( 'before' => intval( $data['before'] ), 'after' => intval( $data['after'] ), 'include' => $data['include'] );
	}
}

