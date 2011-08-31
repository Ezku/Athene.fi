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
class Search_Post_Meta extends Search_Post_Module {
	var $types = array();
	
	function gather( $data ) {
		return $this->the_post_custom( $data->ID );
	}
	
	function the_post_custom( $id ) {
		$custom = get_post_custom( $id );

		if ( count( $custom ) > 0 ) {
			$content = '';
			
			foreach ( $custom AS $field => $value ) {
				if ( in_array( $field, $this->types ) )
					$content .= ' '.implode( ' ', $value );
			}
			
			return $content.'';
		}
		
		return '';
	}
	
	function highlight( $post, $words, $content ) {
		$meta = $this->the_post_custom( $post->ID );

		if ( !empty( $meta ) ) {
			$highlight = new Highlighter( $meta, $words, true );
			
			if ( $highlight->has_matches() ) {
				$highlight->zoom( 40, 80 );
				$highlight->mark_words();

				return '<p><strong>'.__( 'Meta-data', 'search-unleashed' ).':</strong> '.$highlight->get().'</p>';
			}
		}
		
		return '';
	}
	
	function field_name() {
		return 'post_meta';
	}
	
	function name() {
		return __( 'Post/page meta-data', 'search-unleashed' );
	}
	
	function load( $config ) {
		if ( isset( $config['types'] ) )
			$this->types = $config['types'];
	}
	
	function edit() {
		global $wpdb;
		
		$available = $wpdb->get_results( "SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key NOT LIKE '%_wp%' GROUP BY meta_key ORDER BY meta_key" );
		?>
		<tr>
			<th><?php _e( 'Meta fields', 'search-unleashed' ); ?>:<br/>
				<span class="sub"><?php _e( 'Select the meta-data to search', 'search-unleashed' ); ?></span></th>
			<td>
				<?php if ( $available ) : ?>
					<ul class="sublist">
					<?php foreach ( $available AS $key ) :?>
					<li>
						<label>
							<input type="checkbox" name="key[]" value="<?php echo $key->meta_key ?>"<?php if ( in_array( $key->meta_key, $this->types ) ) echo ' checked="checked"' ?>/>
							<?php echo $key->meta_key ?>
						</label>
					</li>
					<?php endforeach; ?>
					</ul>
				<?php endif;?>
			</td>
		</tr>
		<?php
	}
	
	function save( $data ) {
		return array( 'types' => isset( $data['key'] ) ? $data['key'] : array() );
	}
	
	function get_keys() {
		$keys = array();
		
		foreach ( (array)$this->types AS $type ) {
			$keys[] = "'".$type."'";
		}
		
		return implode( ',', $keys );
	}
}
