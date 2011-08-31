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

include_once dirname( __FILE__ ).'/indexed.php';

/**
 * Similar to IndexedEngine, but uses FULLTEXT rather than LIKE
 *
 * @package default
 **/

class MysqlEngine extends IndexedEngine {
	var $small = array();
	var $full  = array();
	
	/**
	 * This is where the magic happens - tap into the WordPress query and inject our own search
	 *
	 * @param string $request 
	 * @return void
	 */
	function posts_request( $request ) {
		global $wpdb, $current_user;
		
		$_GET['s'] = get_query_var( 's' );

		// Extract search terms
		$term = trim( $_GET['s'] );
		$term = preg_replace_callback( "/['\"](.*?)['\"]/", array( &$this, 'exact_words' ), $term );
		
		$term = preg_replace_callback( preg_encoding( '/(\w*)\s*AND\s*(\w*)/' ), array( &$this, 'logical_and' ), $term );
		$term = preg_replace( preg_encoding( '/(\w*)\s*OR\s*(\w*)/' ), '$1 $2', $term );

		// Split the words into small and full
		$words = array_filter( preg_split( '/[\s,]+/', trim( $term ) ) );
		foreach ( $words AS $word ) {
			if ( strlen( $word ) >= 4 )
				$this->full[] = $word;
			else
				$this->small[] = $word;
		}

		include_once dirname( dirname( __FILE__ ) ).'/models/search-module.php';
		$modules = Search_Module_Factory::running();

		$and = array();
		
		$have_comments = false;
		foreach ( (array)$modules AS $module ) {
			if ( $module->is_post() )
				$prefix = $wpdb->prefix.'search_post';
			else {
				$have_comments = true;
				$prefix = $wpdb->prefix.'search_comment';
			}
		
			if ( isset( $_GET[$module->field_name()] ) ) {
				$value = $module->field_value( $_GET[$module->field_name()] );
				if ( $value !== false )
					$and[] = $prefix.'.'.$module->field_name()." LIKE '%".$wpdb->escape( $value )."%'";
			}
		}
	
		if ( $have_comments )
			$this->fields .= ",{$wpdb->prefix}search_comment.comment_id";

		// Any fulltext searches?
		$priorities = $fields = array();
		if ( count( $this->full ) > 0 ) {
			$term        = implode( ' ', $this->full );
			$term        = trim( $term );
			$this->terms = $this->full;
			
			$scores = array();
			foreach ( (array)$modules AS $module ) {
				if ( $module->is_post() )
					$item = $wpdb->prefix.'search_post.'.$module->field_name();
				else
					$item = $wpdb->prefix.'search_comment.'.$module->field_name();

				$fields[] = $item;
				$scores[] = sprintf( "(%2.2f * (MATCH(%s) AGAINST ('%s' IN BOOLEAN MODE)))", $module->priority, $item, $wpdb->escape( $term ) );
			}
	
			$this->fields  .= ',MAX('.implode( ' + ', $scores ).') AS score';
			$this->orderby  = 'score DESC,'.$this->orderby;
		}
			
		// Form SQL
		$sql = "SELECT DISTINCT SQL_CALC_FOUND_ROWS ".$this->fields." FROM {$wpdb->posts} LEFT JOIN {$wpdb->prefix}search_post ON {$wpdb->posts}.ID={$wpdb->prefix}search_post.post_id ";

		if ( $have_comments )
			$sql .= " LEFT JOIN {$wpdb->prefix}search_comment ON {$wpdb->posts}.ID={$wpdb->prefix}search_comment.post_id ";

		$sql .= ' WHERE 1=1 ';
		$sql .= $this->get_restricted_posts();
		
		if ( count( $and ) > 0 )
			$sql .= ' AND '.implode( ' AND ', $and );
		
		// Add small words
		foreach ( (array)$this->small AS $small ) {
			$this->terms[] = $small;
			
			foreach ( (array)$modules AS $module ) {
				if ( $module->is_post() )
					$prefix = $wpdb->prefix.'search_post';
				else
					$prefix = $wpdb->prefix.'search_comment';

				$priorities[]  = $prefix.'.'.$module->field_name()." LIKE '%".$wpdb->escape( $small )."%'";
			}
		}

		if ( count( $this->full ) > 0 )
			$priorities[] = sprintf( "(MATCH(".implode( ',', $fields ).") AGAINST ('%s' IN BOOLEAN MODE))", $wpdb->escape( $term ) );

		$sql .= ' AND ('.implode( ' OR ', $priorities ).') ';
		
		if ( count( $this->full ) > 0 )
			$sql .= " GROUP BY {$wpdb->posts}.ID HAVING score ";
			
		$sql .= "ORDER BY ".$this->orderby.' ';
		$sql .= $this->limits;

		return $sql;
	}
	
	/**
	 * Convert 'x AND y' into FULLTEXT
	 *
	 * @param array $matches Regex match array
	 * @return string Replacement string
	 **/
	function logical_and( $matches ) {
		$this->full[] = '+'.$matches[1].' +'.$matches[2];
		return '';
	}
	
	/**
	 * Record search phrases in quotes
	 *
	 * @param array $matches Regex match array
	 * @return string Replacement string
	 **/
	function exact_words( $matches ) {
		$this->small[] = $matches[1];
		return '';
	}

	/**
	 * Return the search terms as an array of regular expressions
	 *
	 * @return array Search terms
	 **/
	function get_terms() {
		$terms = array();

		// Convert words into proper patterns
		foreach ( (array)$this->terms AS $word ) {
			if ( substr( $word, 0, 1 ) != '-' ) {
				$word = preg_quote( $word, '/' );
				$word = str_replace( '\\*', '\w*', $word );

				if ( trim( $word ) != '' )
					$terms[] = '\b'.$word.'\b';
			}
		}

		return $terms;
	}
	
	/**
	 * Install the FULLTEXT DB tables
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

		$post_sql = $comment_sql = array();
		foreach ( $modules AS $module ) {
			if ( $module->is_post() )
				$post_sql[] = "`".$module->field_name()."` text DEFAULT NULL";
			else
				$comment_sql[] = "`".$module->field_name()."` text DEFAULT NULL";
		}
		
		$post    .= implode( ",\n", $post_sql );
		$post    .= ",PRIMARY KEY (`post_id`)";
		
		if ( count( $post_sql ) > 0 )
			$post .= ",\nFULLTEXT KEY `fulltext` (".str_replace( ' text DEFAULT NULL', '', implode( ",", $post_sql ) ).")";
			
		$post .= ') ENGINE=MyISAM';
		
		$post = str_replace( ',,', ',', $post );
		$wpdb->query( $post );

		if ( count( $comment_sql ) > 0 ) {
			$comment .= implode( ",\n", $comment_sql );
			$comment .= ",PRIMARY KEY (`post_id`,`comment_id`),\n";
			$comment .= "FULLTEXT KEY `fulltext` (".str_replace( ' text DEFAULT NULL', '', implode( ",", $comment_sql ) ).")) ENGINE=MyISAM";
			$wpdb->query( $comment );
		}
	}
}
