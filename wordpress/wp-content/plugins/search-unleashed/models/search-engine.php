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
 * Base search engine
 *
 * @package default
 **/
class SearchEngine {
	var $terms = array();
	var $fields  = null;
	var $limits  = null;
	var $orderby = null;
	var $phrases = array();
	
	/**
	 * Constructor. Hook into various WP db calls
	 *
	 * @param array $options Search Unleashed options
	 * @return void
	 **/
	function SearchEngine( $options ) {
		$this->protected = $options['protected'];
		$this->private   = $options['private'];
		$this->draft     = $options['draft'];
		
		add_filter( 'posts_fields',  array( &$this, 'posts_fields' ) );
		add_filter( 'post_limits',   array( &$this, 'post_limits' ) );
		add_filter( 'posts_request', array( &$this, 'posts_request' ) );
		add_filter( 'posts_orderby', array( &$this, 'posts_orderby' ) );
	}

	function extract_phrases( $search ) {
		$search = preg_replace_callback( "/['\"](.*?)['\"]/", array( &$this, 'exact_words' ), $search );
		$phrases = explode( ' ', $search );
		
		return array_filter( array_merge( $this->phrases, $phrases ) );
	}
	
	function exact_words( $matches ) {
		$this->phrases[] = $matches[1];
		return '';
	}
	
	/**
	 * Extract ORDER BY
	 *
	 * @param string $order SQL
	 * @return string SQL
	 **/
	function posts_orderby( $order ) {
		$this->orderby = $order;
		return $order;
	}
	
	/**
	 * Record fields in use
	 *
	 * @param string $fields SQL
	 * @return string SQL
	 **/
	function posts_fields( $fields ) {
		$this->fields = $fields;
		return $fields;
	}

	/**
	 * Extract LIMIT
	 *
	 * @param string $limits SQL
	 * @return string SQL
	 **/
	function post_limits( $limits ) {
		$this->limits = $limits;
		return $limits;
	}
	
	/**
	 * Extract SQL
	 *
	 * @param string $request SQL
	 * @return string SQL
	 **/
	function posts_request( $request ) {
		return $request;
	}
	
	/**
	 * Return search phrase
	 *
	 * @return array
	 **/
	function get_terms() {
		return $this->terms;
	}
	
	/**
	 * Store details
	 *
	 * @return void
	 **/
	function store( $post_id, $details ) {
	}

	/**
	 * Reset index
	 *
	 * @return void
	 **/
	function reset() {
	}

	/**
	 * Flush data to index
	 *
	 * @return void
	 **/
	function flush() {
	}
	
	/**
	 * Returns the specified search engine
	 *
	 * @param array $options Search Unleashed options
	 * @param string $engine Engine name
	 * @return Search_Engine
	 **/
	function get_engine( $options, $engine ) {
		include_once dirname( __FILE__ ).'/search-engine.php';

		do_action( 'su_get_engine', $engine );

		if ( $engine == 'mysql' ) {
			include_once dirname( dirname( __FILE__ ) ).'/engines/mysql.php';
			return new MysqlEngine( $options );
		}
		elseif ( $engine == 'lucene' ) {
			include_once dirname( dirname( __FILE__ ) ).'/engines/lucene.php';
			return new LuceneEngine( $options );
		}
		elseif ( $engine == 'indexed' ) {
			include_once dirname( dirname( __FILE__ ) ).'/engines/indexed.php';
			return new IndexedEngine( $options );
		}
		
		include_once dirname( dirname( __FILE__ ) ).'/engines/default.php';
		return new DefaultEngine( $options );
	}
	
	/**
	 * Return total number of items to index
	 *
	 * @return void
	 **/
	function total() {
		return 0;
	}

	/**
	 * Modules highlight appropriate content for a post
	 *
	 * @param Object $post WP post object
	 * @param string $content Post content
	 * @return void
	 **/
	function highlight( $post, $content ) {
		$modules = Search_Module_Factory::running();
		$ordered = array ();

		foreach ( $modules AS $module ) {
			if ( $module->id() == strtolower( 'Search_Post_Content' ) )
				array_unshift( $ordered, $module );
			else
				$ordered[] = $module;
		}
		
		$text = '';
		foreach ($ordered AS $module) {
			$text .= $module->highlight( $post, $this->get_terms(), $content );
		}
		
		return $text;
	}
	
	function install_engine() {
	}
	
	function remove_engine() {
	}
}

