<?php
/*
Plugin Name: Search Unleashed
Plugin URI: http://urbangiraffe.com/plugins/search-unleashed/
Description: Advanced search engine that provides full text searching across posts, pages, comments, titles, and URLs.  Searches take into account any data added by other plugins, and all search results are contextually highlighted. You can also highlight incoming searches from popular search engines.
Version: 1.0.6
Author: John Godley
Author URI: http://urbangiraffe.com/
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages(including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort(including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

if ( class_exists( 'SearchUnleashedPlugin' ) )
	return;
	
require dirname( __FILE__ ).'/plugin.php';
require dirname( __FILE__ ).'/models/widget.php';

/**
 * Search Unleashed plugin
 *
 * @package default
 **/

class SearchUnleashedPlugin extends Search_Plugin {
	var $incoming         = null;
	var $show_support     = false;
	var $has_loop_started = false;
	var $has_loop_ended   = false;
	var $last_post_id     = null;
	var $last_post_count  = 0;

	/**
	 * Constructor. Sets everything up
	 *
	 * @return void
	 **/
	function SearchUnleashedPlugin() {
		$this->register_plugin( 'search-unleashed', __FILE__ );

		if ( is_admin() ) {
			$this->add_action( 'admin_menu' );
			$this->add_action( 'admin_head' );
			$this->add_action( 'admin_footer' );

			$this->register_activation( __FILE__ );
			$this->register_deactivation( __FILE__ );
			$this->register_plugin_settings( __FILE__ );

			$this->add_action( 'wp_print_scripts' );
			$this->add_filter( 'contextual_help', 'contextual_help', 10, 2 );
			$this->add_filter( 'print_scripts_array' );

			// Ajax functions
			if ( defined( 'DOING_AJAX' ) ) {
				include_once dirname( __FILE__ ).'/ajax.php';
				$this->ajax = new SearchUnleashedAjax( $this, $this->get_options() );
			}
		}

		// Monitor save/delete posts and comments
		$this->add_action( 'save_post' );
		$this->add_action( 'delete_post' );
		$this->add_action( 'pre_get_posts' );

		$this->add_filter( 'wp_set_comment_status', 'wp_set_comment_status', 10, 2 );
		$this->add_filter( 'edit_comment' );

		// Insert our CSS if needed
		$this->add_action( 'wp_head', 'incoming' );
	}


	/**
	 * Displays the nice animated support logo
	 *
	 * @return void
	 **/
	function admin_footer() {
		if ( isset($_GET['page']) && $_GET['page'] == basename( __FILE__ ) ) {
			$options = $this->get_options();

			if ( !$options['support'] ) {
?>
<script type="text/javascript" charset="utf-8">
	jQuery(function() {
		jQuery('#support-annoy').animate( { opacity: 0.2, backgroundColor: 'red' } ).animate( { opacity: 1, backgroundColor: 'yellow' });
	});
</script>
<?php
			}
		}
	}

	/**
	 * WordPress contextual help. 2.7+
	 *
	 * @return string
	 **/
	function contextual_help( $help, $screen ) {
		if ( $screen == 'tools_page_search-unleashed' ) {
			$help .= '<h5>' . __( 'Search Unleashed Help' ) . '</h5><div class="metabox-prefs">';
			$help .= '<a href="http://urbangiraffe.com/plugins/search-unleashed/">'.__( 'Search Unleashed Documentation', 'search-unleashed' ).'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/support/forum/search-unleashed">'.__( 'Search Unleashed Support Forum', 'search-unleashed' ).'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/tracker/projects/search-unleashed/issues?set_filter=1&amp;tracker_id=1">'.__( 'Search Unleashed Bug Tracker', 'search-unleashed' ).'</a><br/>';
			$help .= '<a href="http://urbangiraffe.com/plugins/search-unleashed/faq/">'.__( 'Search Unleashed FAQ', 'search-unleashed' ).'</a><br/>';
			$help .= '<p>'.__( 'Please read the documentation and FAQ, and check the bug tracker, before asking a question.', 'search-unleashed' ).'</p>';
			$help .= '</div>';
		}

		return $help;
	}

	/**
	 * Displays the plugin settings on the plugin page
	 *
	 * @param array Current links
	 * @return array Modified links
	 **/
	function plugin_settings( $links ) {
		$settings_link = '<a href="tools.php?page='.basename( __FILE__ ).'">'.__( 'Settings', 'search-unleashed' ).'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * WP admin header
	 *
	 * @return void
	 **/
	function admin_head() {
		if ( isset($_GET['page']) && $_GET['page'] == basename( __FILE__ ) )
			echo '<link rel="stylesheet" href="'.$this->url().'/admin.css" type="text/css" media="screen" title="no title" charset="utf-8"/>';
	}

	/**
	 * Queue up our JS
	 *
	 * @return void
	 **/
	function wp_print_scripts() {
		if ( isset($_GET['page']) && $_GET['page'] == basename( __FILE__ ) ) {
			wp_enqueue_script( 'search-unleashed', $this->url().'/js/admin.js', array( 'jquery-form' ), $this->version() );
		}
	}

	function print_scripts_array( $scripts ) {
		$farb = array_search( 'farbtastic', $scripts );

		if ( $farb && ( ( isset( $_GET['page'] ) && $_GET['page'] == 'search-unleashed.php') ) )
			unset( $scripts[$farb] );

		return $scripts;
	}
	
	/**
	 * Returns the current version of this plugin
	 *
	 * @return string Version
	 **/
	function version() {
		$plugin_data = implode( '', file( __FILE__ ) );

		if ( preg_match( '|Version:(.*)|i', $plugin_data, $version ) )
			return trim( $version[1] );
		return '';
	}

	/**
	 * Inject into the WP post collection phase
	 * Replace category and tag pages with our special search page.  Log any searches. Note that this is only called if the appropriate option is set
	 *
	 * @param string SQL
	 * @return string SQL
	 **/
	function posts_where( $where ) {
		if ( is_category() )
			set_query_var( 's', single_cat_title( '', false ) );
		elseif ( is_tag() )
			set_query_var( 's', single_tag_title( '', false ) );

		// Log the search
		$options = $this->get_options();
		if ( $options['expiry'] != -1 ) {
			$log = new Search_Log();

			$log->record( get_query_var( 's' ) );
			$log->expire( $options['expiry'] );
		}
		
		return $where;
	}

	/**
	 * Determine if the current page is a search
	 * Uses the options to replace category/tag pages
	 *
	 * @return boolean
	 **/
	function is_search() {
		if ( is_search() )
			return true;

		$options = $this->get_options();
		if ( $options['replace_category'] && is_category() )
			return true;

		if ( $options['replace_tag'] && is_tag() )
			return true;

		return false;
	}

	/**
	 * Main search injection
	 * Inject into the post retrieval phase at the earliest opportunity.  If this is a search then setup a search engine and hook into various WP filters
	 *
	 * @return void
	 **/
	function pre_get_posts() {
		if ( !is_admin() && $this->is_search() ) {
			$this->add_filter( 'posts_where', 'posts_where', 1 );

			include_once dirname( __FILE__ ).'/models/search-log.php';
			include_once dirname( __FILE__ ).'/models/search-engine.php';

			$options      = $this->get_options();
			$this->engine = SearchEngine::get_engine( $options, $options['search_engine'] );

			$this->add_action( 'loop_start' );
			$this->add_action( 'loop_end' );

			// Update the number of searches performed with SU
			$options['count']++;
			update_option( 'search_unleashed', $options );

			// Do we need to highlight anything?
			if ( $options['highlight_search'] )	{
				include_once dirname( __FILE__ ).'/models/highlighter.php';
				include_once dirname( __FILE__ ).'/models/spider.php';

				$this->add_filter( 'the_content', 'the_excerpt', 15 );
				$this->add_filter( 'the_excerpt', 'the_excerpt', 15 );
				$this->add_action( 'wp_head' );

				if ( isset( $options['active']['search_post_title'] ) )
					$this->add_filter( 'the_title' );

				// If we are in the default theme then force it to display post content
				if ( $options['force_display'] == true || get_option( 'template' ) == 'default' )
					$this->add_action( 'the_time' );
			}

			// Display support message
			if ( $options['support'] == false )
				$this->show_support = true;

			// Change the page title?
			if ( $options['page_title'] )
				$this->add_filter( 'wp_title', 'wp_title', 10, 2 );
				
			remove_filter( 'pre_get_posts', array( &$this, 'pre_get_posts' ) );
		}
	}

	/**
	 * Determines if the search database needs upgrading
	 *
	 * @param string
	 * @return void
	 **/
	function database_upgrade() {
		$version = intval( get_option( 'search_unleashed_version' ) );

		if ( $version !== 3 ) {
			include_once dirname( __FILE__ ).'/models/database.php';

			$db = new SU_Database();
			$db->upgrade( $version, 3 );
		}
	}

	/**
	 * Swaps the current search engine for another
	 * Ensures that all data is cleaned up and removed before swapping
	 *
	 * @param string ID for new search engine
	 * @param string ID for old search engine
	 * @return void
	 **/
	function swap_engine( $new = '', $old = '' ) {
		// Swap search engine
		include_once dirname( __FILE__ ).'/models/search-engine.php';
		
		$options = $this->get_options();
		
		if ( $old ) {
			$engine = SearchEngine::get_engine( $options, $old );
			$engine->remove_engine();
		}
		
		if ( $new ) {
			$engine = SearchEngine::get_engine( $options, $new );
		 	$engine->install_engine();
		}
	}

	/**
	 * Called when the plugin is first activated
	 * Creates an empty search engine
	 *
	 * @return void
	 **/
	function activate() {
		include_once dirname( __FILE__ ).'/models/database.php';
		
		$db = new SU_Database();
		$db->install();
		
		$options = $this->get_options();
		$this->swap_engine( $options['search_engine'] );
	}

	/**
	 * Called when the plugin is deactivated
	 * Removes any search data
	 *
	 * @return void
	 **/
	function deactivate() {
		include_once dirname( __FILE__ ).'/models/database.php';

		$db = new SU_Database();
		$db->remove();

		$options = $this->get_options();
		$this->swap_engine( '', $options['search_engine'] );
	}

	/**
	 * Determines if current page is from an incoming search
	 *
	 * @return void
	 **/
	function incoming() {
		$options = $this->get_options();

		if ( ( is_single() || is_page() ) && $options['incoming'] != 'none' ) {
			include_once dirname( __FILE__ ).'/models/highlighter.php';
			include_once dirname( __FILE__ ).'/models/search-log.php';
			include_once dirname( __FILE__ ).'/models/incoming-search.php';

			$this->incoming = new Incoming_Search( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );

			if ( $this->incoming->is_incoming_search() ) {
				$log = new Search_Log();
				$log->record( $this->incoming->search, $this->incoming );

				$this->wp_head();

				$this->add_filter( 'the_content', 'highlight_incoming', 15 );
				$this->add_filter( 'the_excerpt', 'highlight_incoming', 15 );

				if ( $options['incoming'] == 'all' )
					$this->add_filter( 'the_title', 'highlight_incoming_title' );

				$this->add_filter( 'get_comment_text',   'highlight_incoming_text', 8 );
				$this->add_filter( 'get_comment_author', 'highlight_incoming_text', 8 );
				$this->add_filter( 'the_tags',           'highlight_incoming_text' );
			}
		}
	}

	/**
	 * Highlights a section of text from an incoming search engine
	 * We only highlight inside the post loop
	 *
	 * @param string Text
	 * @return string Highlighted text
	 **/
	function highlight_incoming_text( $text ) {
		if ( in_the_loop() ) {
			$highlight = new Highlighter( $text, $this->incoming->get_search() );

			return $highlight->mark_words( true );
		}

		return $text;
	}

	/**
	 * Highlights a section of text from an incoming search engine
	 * We only highlight inside the post loop
	 *
	 * @param string Text
	 * @return string Highlighted text
	 **/
	function highlight_incoming( $text ) {
		if ( in_the_loop() && !$this->has_loop_ended ) {
			if ( is_single() && $this->has_loop_started )
				$this->has_loop_ended = true;

			$text = $this->capture( 'incoming_local', array( 'words' => $this->incoming->get_search(), 'engine' => $this->incoming ) ).$this->highlight_incoming_text( $text );
		}

		return $text;
	}

	/**
	 * Disables filters
	 * Removes certain filters from the_content hook
	 *
	 * @param array Filters
	 * @return void
	 **/
	function disable_filters( $filters ) {
		global $wp_filter;

		foreach ( $wp_filter['the_content'] AS $level => $filterset ) {
			foreach ( $filterset AS $id => $stuff ) {
				if ( in_array( $id, $filters ) )
					unset( $wp_filter['the_content'][$level][$id] );
			}
		}
	}

	/**
	 * Save post hook
	 * Called when WP saves a post and updates the search index
	 *
	 * @param string
	 * @return void
	 **/
	function save_post( $id ) {
		global $wpdb;

		// Get full details for the post
		$options = $this->get_options();
		$post    = $wpdb->get_row( "SELECT {$wpdb->posts}.*,{$wpdb->users}.user_login,{$wpdb->users}.user_nicename,{$wpdb->users}.display_name FROM {$wpdb->posts} LEFT JOIN {$wpdb->users} ON {$wpdb->posts}.post_author={$wpdb->users}.ID WHERE {$wpdb->posts}.ID=".$id );

		$this->disable_filters( $options['disabled_filters'] );

		// If this isnt a revision
		if ( $post->post_type != 'revision' ) {
			// Create search engine and search spider
			include_once dirname( __FILE__ ).'/models/spider.php';
			include_once dirname( __FILE__ ).'/models/search-engine.php';

			$spider = new SearchSpider( $options );
			$engine = SearchEngine::get_engine( $options, $options['search_engine'] );

			$engine->remove( $post->ID, 'post' );

			if ( $spider->is_allowed( $post ) )
				$engine->store( $post->ID, $spider->gather_for_post( $post ), $post );

			$engine->flush();
		}
	}

	/**
	 * Delete post hook
	 * Called when WP deletes a post.  We remove the post from the search index
	 *
	 * @param string Post ID
	 * @return void
	 **/
	function delete_post( $id ) {
		include_once dirname( __FILE__ ).'/models/search-engine.php';

		$options = $this->get_options();
		
		$engine = SearchEngine::get_engine( $options, $options['search_engine'] );
		$engine->remove( $id );
		$engine->flush();
	}

	/**
	 * Internal update comment function
	 * Called to re-index a comment
	 *
	 * @param string Comment ID
	 * @param object Comment
	 * @param array Search Unleashed options
	 * @return void
	 **/
	function update_comment( $comment_ID, $comment, $options ) {
		include_once dirname( __FILE__ ).'/models/spider.php';
		include_once dirname( __FILE__ ).'/models/search-engine.php';

		$spider = new SearchSpider( $options );
		$engine = SearchEngine::get_engine( $options, $options['search_engine'] );

		$this->disable_filters( $options['disabled_filters'] );

		$engine->remove( $comment_ID, 'comment' );

		if ( !empty( $comment ) && $spider->is_allowed( get_post( $comment->comment_post_ID ) ) )
			$engine->store( $comment->comment_post_ID, $spider->gather_for_comment( $comment ), $comment );

		$engine->flush();
	}

	/**
	 * Edit comment hook
	 * Called when WP edits a comment
	 *
	 * @param string Comment ID
	 * @return void
	 **/
	function edit_comment( $id ) {
		$comment = get_comment( $id );
		$this->update_comment( $id, $comment, $this->get_options() );
	}

	/**
	 * Set comment status hook
	 * Called when WP changes a comment's status
	 *
	 * @param string Comment ID
	 * @param string Comment status
	 * @return void
	 **/
	function wp_set_comment_status( $id, $status = '' ) {
		$comment = get_comment( $id );

		if ( $comment->comment_approved == 'approve' || $comment->comment_approved == 1 )
			$this->update_comment( $comment->comment_ID, $comment, $this->get_options() );
		else
			$this->update_comment( $comment->comment_ID, array(), $this->get_options() );
	}

	/**
	 * Post title hook
	 * Highlight searches inside the post title
	 *
	 * @param string $title Post title
	 * @param string $sep Title separator
	 * @return void
	 **/
	function wp_title( $title, $sep ) {
		global $wp_query;

		$pre = '';
		if ( $wp_query->max_num_pages > 1 ) {
			$paged = get_query_var( 'paged' );
			$max   = $wp_query->max_num_pages;

			if ( $paged == 0 )
				$paged = 1;

			$pre = sprintf( __( ' (page %d of %d)', 'search-unleashed' ), $paged, $max );
		}

		return sprintf( __( 'Search results for \'%s\'', 'search-unleashed' ), htmlspecialchars( get_query_var( 's' ) ) ).$pre;
	}

	/**
	 * Theme head hook
	 * Insert any highlighting CSS
	 *
	 * @return void
	 **/
	function wp_head() {
		$options = $this->get_options();
		if ( $options['include_css'] )
			$this->render_admin( 'css', array( 'highlight' => $options['highlight'] ) );
	}

	/**
	 * Time hook
	 * We inject here because it is the only way in some themes (for example, 'default') to insert search results
	 *
	 * @param string $time Time
	 * @return string Time
	 **/
	function the_time( $time ) {
		if ( in_the_loop() ) {
			global $post;
			return $time.'</small>'.$this->the_excerpt( $post->post_content ).'<small>';
		}

		return $time;
	}

	/**
	 * Highlights incoming search engine titles
	 *
	 * @param string $title Title
	 * @return string Title
	 **/
	function highlight_incoming_title( $title ) {
		// Only if loop_start has been sent
		if ( in_the_loop() ) {
			global $post;

			if ( $this->last_post_id != $post->ID ) {
				$this->last_post_id    = $post->ID;
				$this->last_post_count = 0;
			}

			$options = $this->get_options();

			$this->last_post_count++;
			if ( $this->last_post_count - 1 != $options['theme_title_position'] )
				return $title;

			if ( $this->engine ) {
				$high = new Highlighter( $text, $this->engine->get_terms() );
				return $high->mark_words();
			}
		}

		return $title;
	}

	/**
	 * Hook in to start of WP post loop
	 *
	 * @return void
	 **/
	function loop_start() {
		$this->has_loop_started = true;
	}

	/**
	 * Hook in to end of WP post loop
	 *
	 * @return void
	 **/
	function loop_end() {
		$this->has_loop_ended = true;
	}

	/**
	 * Highlighting for post titles.
	 * Note that the_title may be called several times in a theme to display a single post. For this reason we have a 'theme_title_position' so that we only
	 * highlight the title on the nth call to this function, per post display
	 *
	 * @param string $text
	 * @return string Title
	 */
	function the_title( $text )	{
		// Only if loop_start has been sent
		if ( in_the_loop() ) {
			global $post;

			// Reset our variables if this is the first time on this post
			if ( $this->last_post_id != $post->ID ) {
				$this->last_post_id    = $post->ID;
				$this->last_post_count = 0;
			}

			$options = $this->get_options();

			// Compare position
			$this->last_post_count++;
			if ( $this->last_post_count - 1 == $options['theme_title_position'] ) {
				$high = new Highlighter( $text, $this->engine->get_terms() );
				$high->mark_words();

				return $high->get();
			}
		}

		return $text;
	}

	/**
	 * Hook WP excerpt
	 * Inject our highlighted terms, if appropriate.  We only do this inside the post loop so we dont accidently highlight other content
	 *
	 * @param string $text Excerpt text
	 * @return string Highlight excerpt
	 **/
	function the_excerpt( $text ) {
		if ( in_the_loop() )	{
			// Replace the excerpt with the content
			global $post;

			$pre = '';
			$this->in_search = true;
			if ($this->show_support)
				$pre = $this->capture_admin( 'annoy_front' );

			remove_filter( 'the_content', array( &$this, 'the_excerpt' ), 15 );
			$this->show_support = false;

			return $pre.$this->engine->highlight( $post, apply_filters( 'the_content', $post->post_content ) );
		}

		return $text;
	}

	/**
	 * Determine if we are pre or post WP 2.5
	 *
	 * @return boolean
	 **/
	function is_25() {
		global $wp_version;

		if ( version_compare( '2.5', $wp_version ) <= 0 )
			return true;
		return false;
	}

	/**
	 * Display admin submenu
	 *
	 * @return void
	 **/
	function submenu( $inwrap = false ) {
		// Decide what to do
		$sub = isset( $_GET['sub'] ) ? $_GET['sub'] : '';
	  $url = explode( '&', $_SERVER['REQUEST_URI'] );
	  $url = $url[0];

		if ( !$this->is_25() && $inwrap == false )
			$this->render_admin( 'submenu', array( 'url' => $url, 'sub' => $sub, 'class' => 'id="subsubmenu"' ) );
		else if ( $this->is_25() && $inwrap == true )
			$this->render_admin( 'submenu', array( 'url' => $url, 'sub' => $sub, 'class' => 'class="subsubsub"', 'trail' => ' | ' ) );

		return $sub;
	}

	/**
	 * Hook admin menu
	 *
	 * @return void
	 **/
	function admin_menu() {
		$this->database_upgrade();

    add_management_page( __( 'Search Unleashed', 'search-unleashed' ), __( 'Search Unleashed', 'search-unleashed' ), 'administrator', basename( __FILE__ ), array( $this, 'admin_spider' ) );
	}

	/**
	 * Main administration page
	 *
	 * @return void
	 **/
	function admin_spider() {
		if ( current_user_can( 'administrator' ) ) {
			include_once dirname( __FILE__ ).'/models/spider.php';

			$sub = $this->submenu();

			if ( $sub == '' )
				$this->admin_index();
			elseif ( $sub == 'log' )
				$this->admin_log();
			elseif ( $sub == 'options' )
				$this->admin_options();
			elseif ( $sub == 'modules' )
				$this->render_admin( 'modules', array( 'types' => Search_Module_Factory::available(), 'options' => $this->get_options() ) );
			elseif ( $sub == 'filters' )
				$this->admin_filters();
			elseif ( $sub == 'support' )
				$this->render_admin( 'support' );
		}
		else
			$this->render_message( __( 'You are not allowed access to this resource', 'search-unleashed' ) );
	}

	/**
	 * Filter admin page
	 *
	 * @return void
	 **/
	function admin_filters() {
		global $wp_filter;

		$options = $this->get_options();

		$filters = array();
		foreach ( $wp_filter['the_content'] AS $filt ) {
			foreach ( $filt AS $f => $data ) {
				if ( is_array( $data['function'] ) ) {
					$funcname = get_class( $data['function'][0] ).'::'.$data['function'][1];

					if ( in_array( substr( strtolower( $funcname ), 0, 3 ), array( 'hsm', 'hss' ) ) )
						$filters['HeadSpace::'.$data['function'][1]] = $f;
					else
						$filters[$funcname] = $f;
				}
				else
					$filters[$f] = $f;
			}
		}

		if ( isset($_POST['save'] ) && check_admin_referer( 'searchunleashed-options' ) ) {
			$options['disabled_filters'] = $saved = array();

			if ( isset( $_POST['filters'] ) )
				$saved = $_POST['filters'];

			foreach ( $filters AS $filter ) {
				if ( !in_array( $filter, $saved ) )
					$options['disabled_filters'][] = $filter;
			}

			update_option( 'search_unleashed', $options );
		}

		$this->render_admin( 'filters', array( 'filters' => $filters, 'disabled_filters' => $options['disabled_filters'] ) );
	}

	/**
	 * Log admin page
	 *
	 * @return void
	 **/
	function admin_log() {
		include dirname( __FILE__ ).'/models/search-log.php';
		include dirname( __FILE__ ).'/models/pager.php';

		if ( isset( $_POST['delete'] ) ) {
			Search_Log::delete_all();
			$this->render_message( __( 'All logs have been deleted.', 'search-unleashed' ) );
		} elseif ( isset( $_POST['action2'] ) && $_POST['action2'] == 'delete' ) {
			$ids = array_map( 'intval', $_POST['checkall'] );
			Search_Log::delete_all( $ids );
		}

		$pager = new Search_Pager( $_GET, $_SERVER['REQUEST_URI'], 'searched_at', 'DESC' );
		$this->render_admin( 'log', array( 'logs' => Search_Log::get( $pager ), 'pager' => $pager ) );
	}

	/**
	 * Options admin page
	 *
	 * @return void
	 **/
	function admin_options() {
		if ( isset( $_POST['save'] ) && check_admin_referer( 'searchunleashed-options' ) ) {
			$options = $this->get_options();

			// Swap search engine
			if ( $_POST['search_engine'] != $options['search_engine'] )
				$this->swap_engine( $_POST['search_engine'], $options['search_engine'] );

			$options['support']               = isset( $_POST['support'] ) ? true : false;
			$options['include_css']           = isset( $_POST['include_css'] ) ? true : false;
			$options['incoming']              = $_POST['incoming'];
			$options['page_title']            = isset( $_POST['page_title'] ) ? true : false;
			$options['highlight_search']      = isset( $_POST['highlight_search'] ) ? true : false;
			$options['force_display']         = isset( $_POST['force_display'] ) ? true : false;
			$options['protected']             = isset( $_POST['protected'] ) ? true : false;
			$options['private']               = isset( $_POST['private'] ) ? true : false;
			$options['draft']                 = isset( $_POST['draft'] ) ? true : false;
			$options['pages']                 = isset( $_POST['pages'] ) ? true : false;
			$options['posts']                 = isset( $_POST['posts'] ) ? true : false;
			$options['replace_tag']           = isset( $_POST['replace_tag'] ) ? true : false;
			$options['replace_category']      = isset( $_POST['replace_category'] ) ? true : false;
			$options['highlight']             = array_map( array( &$this, 'highlight_code' ), $_POST['highlight'] );
			$options['exclude']               = implode( ',', array_map( 'intval', explode( ',', $_POST['exclude'] ) ) );
			$options['exclude_cat']           = implode( ',', array_map( 'intval', explode( ',', $_POST['exclude_cat'] ) ) );
			$options['expiry']                = intval( $_POST['expiry'] );
			$options['theme_title_position']  = intval( $_POST['theme_title_position'] );
			$options['search_engine']         = $_POST['search_engine'];

			update_option( 'search_unleashed', $options );
			$this->render_message( __( 'Your options have been saved', 'search-unleashed' ) );
		}
		else if (isset ($_POST['delete']) && check_admin_referer( 'searchunleashed-delete_plugin' ) ) {
			SU_Database::remove( __FILE__ );
			
			// Deactivate the plugin
			$current = get_option('active_plugins');
			array_splice ($current, array_search (basename (dirname (__FILE__)).'/'.basename (__FILE__), $current), 1 );
			update_option('active_plugins', $current);
			
			$this->render_message( __( 'Search Unleashed has been removed', 'search-unleashed' ) );
			return;
		}

		$engines = array(
			'default' => __( 'Default WordPress', 'search-unleashed' ),
			'indexed' => __( 'Indexed WordPress', 'search-unleashed' ),
			'mysql'   => __( 'MySQL Fulltext', 'search-unleashed' ),
			'lucene'  => __( 'Lucene', 'search-unleashed' )
		);

		$this->render_admin( 'options', array( 'engines' => $engines, 'options' => $this->get_options() ) );
	}

	/**
	 * Index admin page
	 *
	 * @return void
	 **/
	function admin_index() {
		include_once dirname( __FILE__ ).'/models/search-engine.php';

		$options = $this->get_options();
		$engine  = SearchEngine::get_engine( $options, $options['search_engine'] );
		$last    = get_option( 'search_unleashed_last' );

		$this->render_admin( 'index', array( 'total' => $engine->total(), 'options' => $options, 'engine' => $engine ) );
	}

	/**
	 * Function to clean a string for CSS color hex
	 *
	 * @return string
	 **/
	function highlight_code( $code ) {
		$code = preg_replace( '/[^0-9A-Fa-f]/', '', $code );

		if ( strlen( $code ) < 6 && $code != '' )
			$code .= str_repeat( '0', 6 - strlen( $code ) );
		else if ( strlen( $code ) > 6 )
			$code = substr( $code, 0, 6 );

		return strtoupper( $code );
	}

	/**
	 * Get Search Unleashed options
	 * This takes saved options and combines it with defaults
	 *
	 * @return array Options
	 **/
	function get_options() {
		$options = get_option( 'search_unleashed' );
		if ( $options === false )
			$options = array();

		if ( !isset( $options['highlight'][0] ) || $options['highlight'][0] == '' )
			$options['highlight'][0] = 'FFFF00';

		if ( !isset( $options['highlight'][1] ) || $options['highlight'][1] == '' )
			$options['highlight'][1] = 'F7B34F';

		if ( !isset( $options['highlight'][2] ) || $options['highlight'][2] == '' )
			$options['highlight'][2] = 'A0F74F';

		if ( !isset( $options['highlight'][3] ) || $options['highlight'][3] == '' )
			$options['highlight'][3] = '4FCFF7';

		if ( !isset( $options['highlight'][4] ) || $options['highlight'][4] == '' )
			$options['highlight'][4] = 'F7C7F1';

		$defaults = array	(
			'force_display'        => false,
			'highlight_search'     => true,
			'count'                => 0,
			'theme_title_position' => 1,
			'protected'            => false,
			'draft'                => false,
			'posts'                => true,
			'pages'                => true,
			'private'              => false,
			'support'              => false,
			'exclude'              => '',
			'exclude_cat'          => '',
			'search_engine'        => 'default',
			'incoming'             => 'all',
			'page_title'           => true,
			'include_css'          => true,
			'expiry'               => 0,
			'replace_tag'          => false,
			'replace_category'     => false,
			'disabled_filters'     => array(),
			'active'               => array( 'search_post_content' => 'search_post_content', 'search_post_title' => 'search_post_title', 'search_post_excerpt' => 'search_post_excerpt' )
		);

		foreach ( $defaults AS $name => $value ) {
			if ( !isset( $options[$name] ) )
				$options[$name] = $value;
		}

		return $options;
	}
	
	function locales() {
		$locales = array();
		$readme  = @file_get_contents( dirname( __FILE__ ).'/readme.txt' );
		if ( $readme ) {
			if ( preg_match_all( '/^\* (.*?) by \[(.*?)\]\((.*?)\)/m', $readme, $matches ) ) {
				foreach ( $matches[1] AS $pos => $match ) {
					$locales[$match] = '<a href="'.$matches[3][$pos].'">'.$matches[2][$pos].'</a>';
				}
			}
		}
		
		ksort( $locales );
		return $locales;
	}
}


/**
 * Instantiate the plugin
 *
 * @global
 **/

global $search_spider;
$search_spider = new SearchUnleashedPlugin;

/**
 * Dummy functions for hosts without appropriate modules
 *
 * @param string
 * @return void
 **/
if ( !function_exists( 'mb_strlen' ) ) {
	function mb_strlen( $str, $encoding = '' )	{
		return strlen( $str );
	}
}

if ( !function_exists( 'mb_substr' ) ) {
	function mb_substr( $str, $start, $length = '', $encoding = '' ) {
		return substr( $str, $start, $length );
	}
}

function strip_html( $text ) {
	if ( version_compare( phpversion(), '5.0.0', '>=' ) )
		$text = @html_entity_decode( $text, ENT_NOQUOTES, get_option( 'blog_charset' ) );    // Remove all HTML
	return wp_kses( stripslashes( $text ), array() );    // Remove all HTML
}

function preg_encoding( $text ) {
	static $utf8 = false;

	if ( !$utf8 ) {
		$encoding = get_option( 'blog_charset' );
		if ( strtolower( $encoding ) == 'utf-8' )
			$utf8 = true;
	}

	if ( $utf8 )
		return $text.'u';
	return $text;
}
