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
class Search_Post_Content extends Search_Post_Module {
	var $before  = 100;
	var $after   = 400;
	var $noindex = false;
	
	function gather( $data ) {
		if ( $this->noindex === false ) {
			$content = apply_filters( 'the_content', $data->post_content );
			$content = apply_filters( 'the_real_content', $content, $data );
			return str_replace( ']]>', ']]&gt;', $content );
		}
		
		return '';
	}
	
	function field_name() {
		return 'post_content';
	}
	
	function highlight( $post, $words, $content ) {
		$high = new Highlighter( $content, $words, true );
		
		if ( $this->noindex === false ) {
			global $search_spider;
		
			$search_spider->has_highlighted_content = true;
			$content = apply_filters( 'search_unleashed_content', $content, $post );

			$high->zoom( $this->before, $this->after );
			$high->mark_words();
		}
		else
			$high->zoom( $this->before, $this->after );
		
		return $high->reformat( $high->get() );
	}
	
	function name() {
		return __( 'Post/page content', 'search-unleashed' );
	}
	
	function load( $config ) {
		if ( isset( $config['before'] ) )
			$this->before = $config['before'];
			
		if ( isset( $config['after'] ) )
			$this->after = $config['after'];
			
		if ( isset( $config['noindex'] ) )
			$this->noindex = $config['noindex'];
	}
	
	function edit() {
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
			<th><label for="noindex"><?php _e( 'Do not index content, but show in results', 'search-unleashed' ); ?></label></th>
			<td>
				<input type="checkbox" name="noindex" id="noindex"<?php if ( $this->noindex ) echo ' checked="checked"' ?>/>
			</td>
		</tr>
		<?php
	}
	
	function save( $data ) {
		return array( 'before' => intval( $data['before'] ), 'after' => intval( $data['after'] ), 'noindex' => isset( $data['noindex'] ) ? true : false);
	}
}
