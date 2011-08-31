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

class IndexedEngine extends SearchEngine {
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
		global $wpdb;

		$fields = $values = array();
		foreach( (array)$details AS $field => $value ) {
			$fields[]  = $field;
			$values[] = "'".$wpdb->escape( $value['data'] )."'";
		}

		if ( isset( $raw->comment_post_ID ) )
			$wpdb->query( "INSERT INTO {$wpdb->prefix}search_comment (post_id,comment_id,".implode( ',', $fields ).") VALUES('$post_id','".$raw->comment_ID."',".implode( ',', $values ).")" );
		else
			$wpdb->query( "INSERT INTO {$wpdb->prefix}search_post (post_id,".implode( ',', $fields ).") VALUES('$post_id',".implode( ',', $values ).")" );
	}

	/**
	 * This is where the magic happens - tap into the WordPress query and inject our own search
	 *
	 * @param string $request WordPress request SQL
	 * @return void
	 */
	function posts_request( $request ) {
		global $wpdb;

		// Extract search terms
		$_GET['s'] = trim( get_query_var( 's' ) );
		$this->terms = get_query_var( 'search_terms' );

		if ( strlen( $_GET['s'] ) > 0 ) {
			// Form SQL
			$term = $wpdb->escape( $_GET['s'] );
			$and  = $module_sql = array();
		
			include_once dirname( dirname( __FILE__ ) ).'/models/search-module.php';
			$modules = Search_Module_Factory::running();
		
			$have_comments = false;
			foreach ( (array)$modules AS $module ) {
				if ( $module->is_post() )
					$prefix = $wpdb->prefix.'search_post';
				else {
					$have_comments = true;
					$prefix = $wpdb->prefix.'search_comment';
				}
			
				foreach ( (array)$this->terms AS $term ) {
					$module_sql[] = $prefix.'.'.$module->field_name()." LIKE '%$term%'";

					if ( isset( $_GET[$module->field_name()] ) ) {
						$value = $module->field_value( $_GET[$module->field_name()] );
						if ( $value !== false )
							$and[] = $prefix.'.'.$module->field_name()." LIKE '%".$wpdb->escape( $value )."%'";
					}
				}
			}
		
			if ( $have_comments )
				$this->fields .= ",{$wpdb->prefix}search_comment.comment_id";
			
			$sql = "SELECT DISTINCT SQL_CALC_FOUND_ROWS ".$this->fields." FROM {$wpdb->posts} LEFT JOIN {$wpdb->prefix}search_post ON {$wpdb->posts}.ID={$wpdb->prefix}search_post.post_id ";

			if ( $have_comments )
				$sql .= " LEFT JOIN {$wpdb->prefix}search_comment ON {$wpdb->posts}.ID={$wpdb->prefix}search_comment.post_id ";

			$sql .= ' WHERE ('.implode( ' OR ', $module_sql ).') ';
			$sql .= $this->get_restricted_posts();
		
			if ( count( $and ) > 0 )
				$sql .= ' AND '.implode( ' AND ', $and );
				
			$sql .= " GROUP BY {$wpdb->posts}.ID ";
			$sql .= " ORDER BY ".$this->orderby.' ';
			$sql .= $this->limits;

			return $sql;
		}

		$this->terms = implode( ' ', $this->terms );
		return str_replace( 'WHERE', 'WHERE 1=2 AND ', $request );
	}
	
	/**
	 * Return SQL to restrict the post according to user's options
	 *
	 * @return string SQL fragment
	 **/
	function get_restricted_posts() {
		global $current_user, $wpdb;
		
		$sql = array( "{$wpdb->posts}.post_status='publish'" );

		if ( !$this->protected )
			$sql[] = "{$wpdb->posts}.post_password=''";

		if ( $current_user && $this->private )
			$sql[] = "({$wpdb->posts}.post_status='private' AND {$wpdb->posts}.post_author=".$current_user->ID.')';
		
		if ( $this->draft )
			$sql[] = "{$wpdb->posts}.post_status='draft'";
			
		return 'AND ('.implode( " OR ", $sql ).') ';
	}
	
	/**
	 * Total number of items that have been indexed
	 *
	 * @return integer Count
	 **/
	function total() {
		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}search_post" );
		if ( $this->have_comments() )
			$count += $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->prefix}search_comment" );
		return $count;
	}
		
	function have_comments() {
		include_once dirname( dirname( __FILE__ ) ).'/models/search-module.php';
		$modules = Search_Module_Factory::running();
	
		$comments = 0;
		foreach ( (array)$modules AS $module ) {
			if ( $module->is_comment() )
				return true;
		}
	}
	
	
	/**
	 * Reset the search index
	 *
	 * @return void
	 **/
	function reset() {
		global $wpdb;
		
		$wpdb->query( "TRUNCATE {$wpdb->prefix}search_post" );
		
		if ( $this->have_comments() )
			$wpdb->query( "TRUNCATE {$wpdb->prefix}search_comment" );
	}
	
	/**
	 * Remove an item from the search index
	 *
	 * @param integer $id Post ID
	 * @param string $field Optional field specifier (post/comment). If null (default) then delete from both post and comments table
	 * @return void
	 **/
	function remove( $id, $field = null ) {
		global $wpdb;
		
		if ( $field == 'post' || $field == null )
			$wpdb->query( "DELETE FROM {$wpdb->prefix}search_post WHERE post_id=$id" );
			
		if ( $field == 'comment' || $field == null )
			$wpdb->query( "DELETE FROM {$wpdb->prefix}search_comment WHERE post_id=$id" );
	}

	/**
	 * Install the WP database tables
	 *
	 * @return void
	 **/
	function install_engine()	{
		global $wpdb;

		$this->remove_engine();

		include_once dirname( dirname( __FILE__ ) ).'/models/search-module.php';
		$modules = Search_Module_Factory::running();
		
		$post    = "CREATE TABLE `{$wpdb->prefix}search_post` (`post_id` int(11) unsigned NOT NULL,";
		$comment = "CREATE TABLE `{$wpdb->prefix}search_comment` (`post_id` int(11) unsigned NOT NULL,`comment_id` int(11) unsigned NOT NULL,";

		foreach ( $modules AS $module ) {
			if ( $module->is_post() )
				$post .= "`".$module->field_name()."` text DEFAULT NULL,\n";
			else
				$comment .= "`".$module->field_name()."` text DEFAULT NULL,\n";
		}
		
		$post    .= "PRIMARY KEY (`post_id`))";
		$comment .= "PRIMARY KEY (`post_id`,`comment_id`))";
		
		$wpdb->query( $post );
		$wpdb->query( $comment );
	}

	/**
	 * Remove the WP DB tables
	 *
	 * @return void
	 **/
	function remove_engine() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}search_post" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}search_comment" );
	}
}
