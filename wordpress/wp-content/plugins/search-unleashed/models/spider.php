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

include dirname( __FILE__ ).'/search-module.php';

/**
 * Gathers data from WordPress ready for feeding into a search engine
 *
 * @package default
 * @author John Godley
 **/
class SearchSpider {
	var $search_terms;
	var $exclude      = array();
	var $exclude_cats = array();
	var $modules      = array();
	var $post_sql     = '';
	var $blog_url     = '';
	var $options;

	/**
	 * Constructor. Extracts options and sets up SQL
	 *
	 * @param array $options Search Unleashed options
	 * @return void
	 **/
	function SearchSpider( $options ) {
		global $wpdb;
		
		$this->options      = $options;
		$this->exclude_cats = array_filter( explode( ',', $options['exclude_cat'] ) );
		$this->exclude      = array_filter( explode( ',', $options['exclude'] ) );
		$this->exclude[]    = 0;
		
		$type = $sql = array();
		if ( $options['pages'] )
			$type[] = "{$wpdb->posts}.post_type='page'";
			
		if ( $options['posts'] )
			$type[] = "{$wpdb->posts}.post_type='post'";
		
		if ( count( $type ) > 0 )
 			$sql[] = implode( ' OR ', $type );
		
		$this->post_sql = $this->comment_sql = ' WHERE ';
		if ( count( $this->exclude_cats ) > 0 ) {
			$this->post_sql  = " LEFT JOIN {$wpdb->term_relationships} AS rel ON ({$wpdb->posts}.ID=rel.object_id) LEFT JOIN {$wpdb->term_taxonomy} AS tax ON (tax.taxonomy='category' AND rel.term_taxonomy_id=tax.term_taxonomy_id) LEFT JOIN {$wpdb->terms} AS term ON (tax.term_id=term.term_id) WHERE ";
			$this->post_sql .= 'tax.term_id NOT IN ('.implode( ',', $this->exclude_cats ).") AND ";
			
			$this->comment_sql = str_replace( "{$wpdb->posts}.ID", "{$wpdb->comments}.comment_post_ID", $this->post_sql );
		}
		
		$this->post_sql    .= ' ('.implode( ') AND (', $sql ).") AND {$wpdb->posts}.ID NOT IN (".implode( ', ', $this->exclude ).") AND {$wpdb->posts}.post_type IN ( 'post', 'page' )";
		$this->comment_sql .= "{$wpdb->comments}.comment_type='' AND {$wpdb->comments}.comment_approved='1' AND {$wpdb->comments}.comment_post_ID NOT IN(".implode( ', ', $this->exclude ).")";
		$this->modules      = Search_Module_Factory::running();
		
		if ( $this->options['private'] == false )
			$this->post_sql .= " AND {$wpdb->posts}.post_status!='private'";

		if ( $this->options['draft'] == false )
			$this->post_sql .= " AND {$wpdb->posts}.post_status!='draft'";

		if ( $this->options['protected'] == false )
			$this->post_sql .= " AND {$wpdb->posts}.post_password=''";

		$this->blog_url     = get_option( 'home' );
	}

	/**
	 * Determine if a post can be indexed based upon current options
	 *
	 * @param object $post Wp post object
	 * @return boolean
	 **/
	function is_allowed( $post ) {
		// Correct post type
		if ( ( $post->post_type == 'post' && $this->options['posts'] == true ) || ( $post->post_type == 'page' && $this->options['pages'] == true ) ) {
			// Is it an excluded post?
			if ( !in_array( $post->ID, $this->exclude ) ) {
				// Is it an excluded category?
				$cats = get_the_category( $post->ID );
				foreach ( (array)$cats AS $cat ) {
					if ( in_array( $cat->cat_ID, $this->exclude_cats) )
						return false;
				}

				// Check post status
				if ( $this->options['private'] == false && $post->post_status == 'private' )
					return false;

				if ( $this->options['draft'] == false && $post->post_status == 'draft' )
					return false;

				if ( $this->options['protected'] == false && $post->post_password != '' )
					return false;

				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Gather all data for a post as name-value pairs
	 *
	 * @param object $post_entry WP post object
	 * @return array
	 **/
	function gather_for_post( $post_entry ) {
		global $post, $wpdb;
		
		$post = $post_entry;    // Enables plugins to work properly

		// Gather all details for this post
		$fields = array();

		foreach ( $this->modules AS $module ) {
			// Gather data for this module
			// Add into details array
			if ( $module->is_post() )
				$fields[$module->field_name()] = array( 'data' => $this->clean_for_search( $module->gather( $post_entry ) ), 'priority' => $module->priority );
		}
		
		return $fields;
	}
	
	/**
	 * Gather all data for a comment as name-value pairs
	 *
	 * @param object $comment WP comment object
	 * @return array
	 **/
	function gather_for_comment( $comment ) {
		global $wpdb;
		
		// Gather all details for this post
		$fields = array();
		
		foreach ( $this->modules AS $module ) {
			// Gather data for this module
			// Add into details array
			if ( $module->is_comment() )
				$fields[$module->field_name()] = array( 'data' => $this->clean_for_search( $module->gather( $comment ) ), 'priority' => $module->priority );
		}

		return $fields;
	}
	
	/**
	 * Determine if we have any post modules
	 *
	 * @return boolean
	 **/
	function have_posts() {
		foreach ( $this->modules AS $module ) {
			if ( $module->is_post() )
				return true;
		}
		
		return false;
	}

	/**
	 * Determine if we have any commment modules
	 *
	 * @return boolean
	 **/
	function have_comments() {
		foreach ( $this->modules AS $module ) {
			if ( $module->is_comment() )
				return true;
		}
		
		return false;
	}

	/**
	 * Gather data from a subset of the WP tables
	 * The total set is posts+comments (depending which options are enabled). The function will return this combined result as the total number of items to index
	 *
	 * @param integer $offset Current offset (out of total)
	 * @param integer $count Number of items to index next
	 * @param SearchEngine $engine Search engine to store items
	 * @return array Number of items remaining, total number of items
	 **/
	function index( $offset, $count, $engine ) {
		global $wpdb;

		$total_posts = $total_comments = 0;
		
		// Calculate how many in total
		if ( $this->have_posts() )
			$total_posts = $wpdb->get_var( "SELECT COUNT(DISTINCT {$wpdb->posts}.ID) FROM {$wpdb->posts} ".$this->post_sql );

		if ( $this->have_comments() )
			$total_comments = $wpdb->get_var( "SELECT COUNT(DISTINCT {$wpdb->comments}.comment_ID) FROM {$wpdb->comments} ".$this->comment_sql );

		$grand_total = $total_posts + $total_comments;
		
		// What to index?  We don't bother trying to span posts/comments - just go up to the limit of each
		if ( $total_posts > 0 && $offset < $total_posts) {
			// More posts to index
			$relative = $offset;
			$rows = $wpdb->get_results( "SELECT DISTINCT {$wpdb->posts}.*,{$wpdb->users}.user_login,{$wpdb->users}.user_nicename,{$wpdb->users}.display_name FROM {$wpdb->posts} LEFT JOIN {$wpdb->users} ON {$wpdb->posts}.post_author={$wpdb->users}.ID ".$this->post_sql." ORDER BY {$wpdb->posts}.ID LIMIT $relative,$count" );

			foreach ( (array)$rows AS $row ) {
				$engine->store( $row->ID, $this->gather_for_post( $row ), $row );
			}

			return array( $grand_total - count( $rows ) - $offset, $grand_total );
		}
		elseif ( $total_comments > 0 && $offset < $grand_total ) {
			$relative = $offset - $total_posts;

			$rows = $wpdb->get_results( "SELECT DISTINCT comment_ID,comment_post_ID,comment_author,comment_author_url,comment_content FROM {$wpdb->comments} ".$this->comment_sql." ORDER BY comment_ID LIMIT $relative,$count" );

			foreach ( (array)$rows AS $row ) {
				$engine->store( $row->comment_post_ID, $this->gather_for_comment( $row ), $row );
			}

			return array( $grand_total - count( $rows ) - $offset, $grand_total );
		}

		// Nothing left
		return array( 0, $grand_total );
	}

	/**
	 * Clean a piece of text suitable for indexing
	 * This removes all HTML, removes most entities and empty spaces
	 *
	 * @param string $text Text to clean
	 * @return string Cleaned text
	 **/
	function clean_for_search( $text ) {
		// Save HREF and ALT attributes
		preg_match_all( '/ href=["\'](.*?)["\']/iu', $text, $href );
		preg_match_all( '/ alt=["\'](.*?)["\']/iu', $text, $alt );
		preg_match_all( '/ title=["\'](.*?)["\']/iu', $text, $title );

		// Remove comments and JavaScript
		$text = preg_replace( preg_encoding( '/<script(.*?)<\/script>/s' ), '', $text );
		$text = preg_replace( preg_encoding( '/<!--(.*?)-->/s' ), '', $text );

		$text = str_replace( '<', ' <', $text );   // Insert a space before HTML so the strip will have seperate words
		$text = preg_replace( '/&#\d*;/', '', $text );
		$text = addslashes( wp_kses( stripslashes( strip_html( $text ) ), array() ) );
		$text = preg_replace( preg_encoding( '/&\w*;/' ), ' ', $text );                    // Removes entities
		$text = str_replace( "'", '', $text );
		$text = str_replace( '&shy;', '', $text );
		$text = preg_replace( preg_encoding( '/[\'!;#$%&\,_\+=\?\(\)\[\]\{\}\"<>`]/' ), ' ', $text );

		if ( count( $href ) > 0 )
			$text .= ' '.implode( ' ', $href[1] );
		
		if ( count( $alt ) > 0 )
			$text .= ' '.implode( ' ', $alt[1] );
			
		if ( count( $title ) > 0 )
			$text .= ' '.implode( ' ', $title[1] );

		while ( preg_match( preg_encoding( '/\s{2}/' ), $text, $matches ) > 0 )
			$text = preg_replace( preg_encoding( '/\s{2}/' ), ' ', $text );
		
		$text = str_replace( '"', '', $text );
		$text = str_replace( $this->blog_url, '', $text );
		return stripslashes( trim( $text ) );
	}
}

