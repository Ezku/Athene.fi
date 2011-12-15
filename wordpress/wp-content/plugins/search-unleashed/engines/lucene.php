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

// Setup Zend path
set_include_path( get_include_path().PATH_SEPARATOR.dirname( __FILE__ ) );

// Include Zend Lucene
include dirname( __FILE__ ).'/Zend/Search/Lucene.php';

/**
 * Provides Zend Lucene indexing for WordPress
 *
 * @package default
 **/
class LuceneEngine extends SearchEngine {
	private $lucene     = null;
	private $post_ids   = array();
	private $query      = null;
	private $total_hits = 0;
	private $cache_path;

	/**
	 * Sets up Zend Lucene
	 *
	 * @param array $options Search Unleashed options
	 * @return void
	 **/
	function __construct( $options ) {
		$this->cache_path = WP_CONTENT_DIR . '/cache/';

		add_filter( 'posts_where', array( &$this, 'posts_where' ) );
		parent::__construct( $options );
	}
	
	/**
	 * Open Lucene
	 *
	 * @return void
	 **/
	private function open() {
		if ( $this->lucene == null ) {
			try {
				$this->lucene = Zend_Search_Lucene::open( $this->cache_path.'search-unleashed' );
				
			} catch( Zend_Search_Lucene_Exception $e ) {
				return false;
			}
		}
		
		return $this->lucene;
	}
	
	/**
	 * Get Lucene cache location
	 *
	 * @return void
	 **/
	private function cache_path() {
		return WP_CONTENT_DIR . '/cache/';
	}
	
	/**
	 * Remove post limits
	 *
	 * @param string
	 * @return void
	 **/
	function post_limits( $limit ) {
		return '';
	}
	
	/**
	 * Return SQL to restrict the post according to user's options
	 *
	 * @return string SQL fragment
	 **/
	function get_restricted_posts() {
		global $current_user;
		
		$sql = array( 'private_post_status:(publish)' );

		if ( $current_user && $this->private )
			$sql[] = '(private_post_status:(private) AND private_post_author:('.$current_user->ID.')';
		
		if ( $this->draft )
			$sql[] = "private_post_status:(draft)";

		if ( !$this->protected )
			return '(private_post_password:(no) AND ('.implode( ' OR ', $sql ).'))';

		return '('.implode( ' OR ', $sql ).')';
	}
	
	/**
	 * Hook the posts 'where' and search Lucene
	 * We modify the search SQL to return the posts found from Lucene
	 *
	 * @param string $where WordPress SQL
	 * @return string SQL
	 **/
	function posts_where( $where ) {
		$_GET['s']   = get_query_var( 's' );
		$this->terms = array( $_GET['s'] );

		$lucene = $this->open();
		if ( $lucene ) {
			$modules = Search_Module_Factory::running();
			$where = 'AND 1=2';  // Nothing was found

			try {
				$this->terms = array_filter( preg_split( '/[\s,]+/', trim( get_query_var( 's' ) ) ) );
				$this->query = new Zend_Search_Lucene_Search_Query_Boolean();

				$have_comments = false;

				// Add queries for all the modules
				foreach ( (array)$modules AS $module ) {
					if ( $module->is_comment() )
						$have_comments = true;
						
					$sub = Zend_Search_Lucene_Search_QueryParser::parse( get_query_var( 's' ), get_option( 'blog_charset' ) );
					$this->query->addSubquery( $sub, true );
					
					if ( isset( $_GET[$module->field_name()] ) ) {
						$value = $module->field_value( $_GET[$module->field_name()] );
						// XXX remove any braces from and value
						if ( $value !== false ) {
							$sub = Zend_Search_Lucene_Search_QueryParser::parse( $module->field_name().':('.$value.')', get_option( 'blog_charset' ) );
							$this->query->addSubquery( $sub, true );
						}
					}
				}
				
				// Add restrictions for status
				$this->query->addSubquery( Zend_Search_Lucene_Search_QueryParser::parse( $this->get_restricted_posts() ), true );

				// Do the Lucene query
				$hits = new ArrayObject( $lucene->find( $this->query ) );
				if ( count( $hits ) > 0 ) {
					global $wpdb;

					$page  = get_query_var( 'paged' ) ? get_query_var( 'paged' ) - 1 : 0;
					$start = get_query_var( 'posts_per_page' ) * ( $page );

					$this->total_hits = count( $hits );
					if ( $have_comments )
						$hits = new LimitIterator( $hits->getIterator(), 0 );
					else
						$hits = new LimitIterator( $hits->getIterator(), $start );

					foreach ( $hits AS $hit ) {
						$this->post_ids[] = $hit->post_id;

						if ( !$have_comments && count( $this->post_ids ) >= get_query_var( 'posts_per_page' ) )
							break;
					}

					if ( $have_comments ) {
						$this->post_ids   = array_unique( $this->post_ids );
						$this->total_hits = count( $this->post_ids );
						$this->post_ids   = array_slice( $this->post_ids, $start, get_query_var( 'posts_per_page' ) );
					}

					$where = "AND ID IN (".implode( ',', $this->post_ids ).')';
					add_filter( 'the_posts', array( &$this, 'the_posts' ) );
				}
			} catch( Zend_Search_Lucene_Exception $e ) {
			}
		}

		return $where;
	}
	
	/**
	 * Inject into WordPress posts loop
	 *
	 * @param string
	 * @return void
	 **/
	public function the_posts( $posts ) {
		if ( count( $this->post_ids ) > 0 ) {
			global $wp_query;
			
			$wp_query->found_posts   = $this->total_hits;
			$wp_query->max_num_pages = ceil( $this->total_hits / get_query_var( 'posts_per_page' ) );
			
			$new = array();
			foreach ( $posts AS $post )
				$new[$post->ID] = $post;
			
			$posts = array();
			foreach ( $this->post_ids AS $id )
				$posts[] = $new[$id];
		}
		
		return $posts;
	}
	
	/**
	 * Store our post/comment details array in Lucene
	 *
	 * @param integer $post_id Post ID
	 * @param array $details Array of  field=>value
	 * @param object $raw Raw post/comment object
	 * @return void
	 **/
	public function store( $post_id, $details, $data ) {
		$lucene = $this->open();

		if ( ini_get('safe_mode') == 0 )
			@set_time_limit( 0 );
		
		if ( $lucene ) {
			try {
				$doc = new Zend_Search_Lucene_Document();
				$doc->addField( Zend_Search_Lucene_Field::Keyword( 'post_id', $post_id ) );

				// Add post-specific details
				if ( isset( $data->post_status ) ) {
					$doc->addField( Zend_Search_Lucene_Field::Keyword( 'private_post_status', $data->post_status ) );
					$doc->addField( Zend_Search_Lucene_Field::Keyword( 'private_post_password', $data->post_password ? 'yes' : 'no' ) );
					$doc->addField( Zend_Search_Lucene_Field::Keyword( 'private_post_date', $data->post_date ) );
					$doc->addField( Zend_Search_Lucene_Field::Keyword( 'private_post_author', $data->post_author ) );
				}
				elseif ( isset( $data->comment_ID ) ) {
					$doc->addField( Zend_Search_Lucene_Field::Keyword( 'comment_id', $data->comment_ID ) );
				}

				foreach ( $details AS $field => $value ) {
					$zend = Zend_Search_Lucene_Field::UnStored( $field, $value['data'], get_option( 'blog_charset' ) );
					$zend->boost = $value['priority'];

					$doc->addField( $zend );
				}

				@$lucene->addDocument( $doc );
			} catch( Zend_Search_Lucene_Exception $e ) {
				return false;
			}
		}
	}

	/**
	 * Fully flush and optimize Lucene
	 *
	 * @param string
	 * @return void
	 **/
	public function full_flush() {
		$lucene = $this->open();

		if ( $lucene ) {
			try {
				$lucene->optimize();
			} catch( Zend_Search_Lucene_Exception $e ) {
				return false;
			}
		}
	}
	
	/**
	 * Standard flush to commit changes to Zend
	 *
	 * @param boolean $full Full flush or not
	 * @return void
	 **/
	public function flush( $full = false ) {
		$lucene = $this->open();

		if ( $lucene ) {
			try {
				$lucene->commit();
				
				if ( $full )
					$lucene->optimize();
				else {
					wp_clear_scheduled_hook( 'lucene_full_flush' );
					wp_schedule_single_event( time() + 3600, 'lucene_full_flush' );
				}
					
			} catch( Zend_Search_Lucene_Exception $e ) {
				return false;
			}
		}
	}
	
	/**
	 * Total number of items that have been indexed
	 *
	 * @return integer Count
	 **/
	public function total() {
		$lucene = $this->open();
		
		if ( $lucene ) {
			return $lucene->numDocs();
		}
	}
	
	/**
	 * Reset the search index
	 *
	 * @return void
	 **/
	public function reset() {
		try {
			return Zend_Search_Lucene::create( $this->cache_path().'search-unleashed' );
		} catch( Zend_Search_Lucene_Exception $e ) {
			return false;
		}
	}
	
	/**
	 * Remove an item from the search index
	 *
	 * @param integer $id Post ID
	 * @param string $field Optional field specifier (post/comment). If null (default) then delete from both post and comments table
	 * @return void
	 **/
	public function remove( $id, $field = 'post_id' ) {
		$lucene = $this->open();

		if ( $lucene ) {
			try {
				$hits = $lucene->find( $field.':'.$id );

				foreach ( (array)$hits AS $hit ) {
					$lucene->delete( $hit->id );
				}
			} catch( Zend_Search_Lucene_Exception $e ) {
				return false;
			}
		}
	}
	
	/**
	 * Install the Lucene directory
	 *
	 * @return void
	 **/
	public function install_engine() {
		$this->reset();
	}
	
	/**
	 * Remove the Lucene directory
	 *
	 * @return void
	 **/
	public function remove_engine() {
		$files = glob( $this->cache_path().'search-unleashed/*' );
		
		foreach ( (array)$files AS $file ) {
			@unlink( $file );
		}
		
		@rmdir( $this->cache_path().'search-unleashed' );
	}
	
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
}

function lucene_full_flush() {
	global $search_spider;
	
	$engine = $search_spider->get_engine();
	$engine->full_flush();
}
