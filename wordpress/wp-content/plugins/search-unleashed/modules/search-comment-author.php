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
class Search_Comment_Author extends Search_Comment_Module {
	function gather( $data ) {
		return apply_filters( 'comment_author', apply_filters( 'get_comment_author', $data->comment_author ) );
	}
	
	function highlight( $post, $words, $content ) {
		if ( isset( $post->comment_id ) ) {
			$comment   = get_comment( $post->comment_id );
			$highlight = new Highlighter( $this->gather( $comment ), $words, true );

			if ( $highlight->has_matches() ) {
				$highlight->mark_words();
			
				return '<p><strong>'.__( 'Comment author', 'search-unleashed' ).':</strong> '.$highlight->get().'</p>';
			}
		}
		
		return '';
	}
	
	function field_name() {
		return 'comment_author';
	}
	
	function name () {
		return __( 'Comment author', 'search-unleashed' );
	}
}
