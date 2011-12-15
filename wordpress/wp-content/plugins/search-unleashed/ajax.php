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
 * AJAX functionality for Search Unleashed
 *
 * @package default
 **/
class SearchUnleashedAjax extends Search_Plugin {
	var $options;
	var $plugin;
	
	/**
	 * Constructor.  Register Ajax commands
	 *
	 * @param object $plugin Search Unleashed object
	 * @param array $options Search Unleashed options
	 * @return void
	 **/
	function SearchUnleashedAjax( $plugin, $options ) {
		$this->options = $options;
		$this->plugin  = $plugin;
		
		$this->register_plugin( 'search-unleashed', __FILE__ );
		
		$this->register_ajax( 'su_module_off' );
		$this->register_ajax( 'su_module_on' );
		$this->register_ajax( 'su_module_edit' );
		$this->register_ajax( 'su_module_load' );
		$this->register_ajax( 'su_module_save' );
		$this->register_ajax( 'su_index' );
	}
	
	/**
	 * Displays a module
   *   module ID - $_POST['id]
	 *
	 * @return void
	 **/
	function su_module_load() {
		if ( current_user_can( 'administrator' ) && check_ajax_referer( 'searchunleashed-module' ) ) {
			require dirname( __FILE__ ).'/models/search-module.php';

			$this->render_admin( 'module', array( 'module' => Search_Module_Factory::get( $_POST['id'] ) ) );
			die();
		}
	}
	
	/**
	 * Save a module config
	 *   module ID - $_POST['id]
	 *
	 * @return void
	 **/
	function su_module_save() {
		if ( current_user_can( 'administrator' ) && check_ajax_referer( 'searchunleashed-module_save' ) ) {
			require dirname( __FILE__ ).'/models/search-module.php';

			$module = Search_Module_Factory::get( $_POST['id'] );

			$this->options['modules'][$module->id()] = $module->save_options( $_POST );
			update_option( 'search_unleashed', $this->options );

			$module = Search_Module_Factory::get( $_POST['id'] );
			$this->render_admin( 'module', array( 'module' => $module ) );
			die();
		}
	}
	
	/**
	 * Re-index
	 *   Current index offset - $_POST['offset']
	 *   Index limit          - $_POST['limit']
	 *
	 * @return void
	 **/
	function su_index() {
		if ( current_user_can( 'administrator' ) && check_ajax_referer( 'searchunleashed-index' ) ) {
			require dirname( __FILE__ ).'/models/spider.php';
			require dirname( __FILE__ ).'/models/search-engine.php';

			ob_start();
			
			$spider = new SearchSpider( $this->options );
			$engine = SearchEngine::get_engine( $this->options, $this->options['search_engine'] );
			$offset = intval( $_POST['offset'] );
		
			if ( $offset == 0 )
				$engine->reset();

			$this->plugin->disable_filters( $this->options['disabled_filters'] );
			
			// Return string formatted: 0|1 Status
			// Where 0 = more data to come
			//       1 = finished
			//       Status = Status message
			list( $remaining, $total ) = $spider->index( $offset, intval( $_POST['limit'] ), $engine );

			ob_end_clean();
			
			$percent = 100;
			if ($total > 0)
				$percent = ( ( $total - $remaining ) / $total ) * 100;

			if ( $remaining > 0 )
				echo sprintf('%d %d%% ', $remaining, $percent).sprintf( __( '%d of %d / %d%%', 'search-unleashed'), $total - $remaining, $total, $percent );
			else {
				echo '0 100% '.__('Finished!', 'search-unleashed');
				$engine->flush( true );
			}

			die();
		}
	}
	
	/**
	 * Display edit box for module
	 *   module ID - $_POST['id]
   *
	 * @return void
	 **/
	function su_module_edit() {
		if ( current_user_can( 'administrator' ) && check_ajax_referer( 'searchunleashed-module' ) ) {
			require dirname( __FILE__ ).'/models/search-module.php';

			$this->render_admin( 'module_edit', array( 'module' =>Search_Module_Factory::get( $_GET['id'] ) ) );
			die();
		}
	}
	
	/**
	 * Enable a module
	 *   module ID - $_POST['id]
   *
	 * @return void
	 **/
	function su_module_on() {
		if ( current_user_can( 'administrator' ) && check_ajax_referer( 'searchunleashed-module' ) ) {
			$this->options['active'][$_POST['module']] = $_POST['module'];
			$this->options['active'] = array_filter( $this->options['active'] );
			update_option ('search_unleashed', $this->options);
		
			$this->plugin->swap_engine( $this->options['search_engine'], $this->options['search_engine'] );
		}
	}
	
	/**
	 * Disable a module
	 *   module ID - $_POST['id]
	 *
	 * @return void
	 **/
	function su_module_off() {
		if ( current_user_can( 'administrator' ) && check_ajax_referer( 'searchunleashed-module' ) ) {
			unset ($this->options['active'][$_POST['module']]);
			update_option ('search_unleashed', $this->options);
		
			$this->plugin->swap_engine( $this->options['search_engine'], $this->options['search_engine'] );
		}
	}
}
