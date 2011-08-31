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

/**
 * WordPress search engine
 * Uses LIKE for searching
 *
 * @package default
 **/

class DefaultEngine extends SearchEngine {
	var $and = array();

	/**
	 * Store an array of details and associate it with a post
	 *
	 * @param integer $post_id Post ID
	 * @param array $details Array of  field=>value
	 * @param object $raw Raw post/comment object
	 * @return void
	 **/
	function store( $post_id, $details, $raw ) {
	}

	/**
	 * This is where the magic happens - tap into the WordPress query and inject our own search
	 *
	 * @param string $request WordPress request SQL
	 * @return void
	 */
	function posts_request( $request ) {
		global $wpdb;
		
		$this->terms = get_query_var( 'search_terms' );
		
		// Get running modules
		include_once dirname( dirname( __FILE__ ) ).'/models/search-module.php';
		$modules = Search_Module_Factory::running();

		$extra = $join = $tax = array();
		
		// Search category names
		if ( isset( $modules['search_post_category'] ) ) {
			$tax[] = "tax.taxonomy='category'";
		}
		
		// Tags
		if ( isset( $modules['search_post_category'] ) ) {
			$tax[] = "tax.taxonomy='post_tag'";
		}
		
		if ( count( $tax ) > 0 ) {
			foreach ( $this->terms AS $term ) {
				$join[] = " LEFT JOIN $wpdb->term_relationships AS rel ON ($wpdb->posts.ID=rel.object_id) LEFT JOIN $wpdb->term_taxonomy AS tax ON (".implode( ' OR ', $tax )." AND rel.term_taxonomy_id=tax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS term ON (tax.term_id=term.term_id)";
				$extra[] = "term.slug LIKE '%$term%'";
			}
		}

		// Author
		if ( isset( $modules['search_post_author'] ) ) {
			foreach ( $this->terms AS $term ) {
				$extra[] = "($wpdb->users.display_name LIKE '%$term%')";
				$join[]  = "INNER JOIN $wpdb->users ON $wpdb->users.ID=$wpdb->posts.post_author";
			}
		}
		
		// Comments
		$comments = false;
		if ( isset( $modules['search_comment_author'] ) ) {
			$comments = true;
			foreach ( $this->terms AS $term ) {
				$extra[] = "($wpdb->comments.comment_author LIKE '%$term%')";
			}
		}

		if ( isset( $modules['search_comment_url'] ) ) {
			$comments = true;
			foreach ( $this->terms AS $term ) {
				$extra[] = "($wpdb->comments.comment_author_url LIKE '%$term%')";
			}
		}

		if ( isset( $modules['search_comment_content'] ) ) {
			$comments = true;

			foreach ( $this->terms AS $term ) {
				$extra[] = "($wpdb->comments.comment_content LIKE '%$term%')";
			}
		}
		
		if ( $comments )
			$join[] = "LEFT JOIN $wpdb->comments ON $wpdb->comments.comment_post_ID=$wpdb->posts.ID";
		
		// Easy one - search URL
		if ( isset( $modules['search_post_slug'] ) ) {
			foreach ( $this->terms AS $term ) {
				$extra[] = "($wpdb->posts.post_name LIKE '%$term%')";
			}
		}

		// Easy one - search excerpt
		if ( isset( $modules['search_post_excerpt'] ) ) {
			foreach ( $this->terms AS $term ) {
				$extra[] = "($wpdb->posts.post_excerpt LIKE '%$term%')";
			}
		}
		
		// Search post meta
		if ( isset( $modules['search_post_meta'] ) ) {
			$module  = $modules['search_post_meta'];
			
			foreach ( $this->terms AS $term ) {
				$extra[] = "($wpdb->postmeta.meta_value LIKE '%$term%' AND $wpdb->postmeta.meta_key IN (".$module->get_keys()."))";
				$join[]  = "INNER JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id=$wpdb->posts.ID";
			}
		}
		
		// Insert our extra stuff
		if ( count( $extra ) > 0 )
			$request = str_replace('1=1  AND ((', '1=1 AND (('.implode( ' OR ', $extra ).' OR ', $request);

		if ( count( $join ) > 0 )
			$request = str_replace('WHERE', implode( ' ', $join ).' WHERE', $request);

		$request = str_replace( 'SELECT', 'SELECT DISTINCT', $request );
		
		if ( is_array( $this->terms ) )
			$this->terms = implode( ' ', $this->terms );
		
		return $request;
	}

	
	/**
	 * Total number of items that have been indexed
	 *
	 * @return integer Count
	 **/
	function total() {
		global $wpdb;
		
		include_once dirname( dirname( __FILE__ ) ).'/models/search-module.php';
		$modules = Search_Module_Factory::running();

		$have_comments = false;

		foreach ( (array)$modules AS $module ) {
			if ( $module->is_comment() )
				$have_comments = true;
		}
	
		$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status!='revision' AND post_status!='attachment'" );

		if ( $have_comments )
			$count += $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved=1 AND comment_type=''" );
		return $count;
	}
	
	/**
	 * Reset the search index
	 *
	 * @return void
	 **/
	function reset() {
	}
	
	/**
	 * Remove an item from the search index
	 *
	 * @param integer $id Post ID
	 * @param string $field Optional field specifier (post/comment). If null (default) then delete from both post and comments table
	 * @return void
	 **/
	function remove( $id, $field = null ) {
	}

	/**
	 * Install the WP database tables
	 *
	 * @return void
	 **/
	function install_engine()	{
	}

	/**
	 * Remove the WP DB tables
	 *
	 * @return void
	 **/
	function remove_engine() {
	}
}
