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
 * Loads the search modules. These functions can be called statically
 *
 * @package default
 **/
class Search_Module_Factory {
	/**
	 * Return array of active search modules
	 *
	 * @return array Array of modules
	 **/
	function running() {
		global $search_spider;
		
		$modules = array();
		$options = $search_spider->get_options();
		
		foreach ( $options['active'] AS $field ) {
			$modules[$field] = Search_Module_Factory::get( $field );
		}
		
		return array_filter( $modules );
	}

	/**
	 * Return array of all search modules
	 *
	 * @return array Array of modules
	 **/
	function available() {
		$options = get_option( 'search_unleashed' );

		$available = get_declared_classes();
		$files     = glob( implode( DIRECTORY_SEPARATOR, array( dirname( __FILE__ ), '..', 'modules', '*.php' ) ) );
		
		foreach ( (array)$files AS $file ) {
			if ( file_exists( $file ) )
				include_once $file;
		}

		$modules   = array();
		$available = array_diff( get_declared_classes(), $available );
		
		foreach ( (array)$available AS $name )	{
			$name   = strtolower( $name );
			$module = new $name();

			if ( isset( $options['active'][$name] ) )
				$module->active = true;
			
			$module->priority = isset( $options['modules'][$name]['priority'] ) ? $options['modules'][$name]['priority'] : 1.0;
			$modules[$name]   = $module;
		}
		
		return apply_filters( 'su_modules', $modules );
	}
	
	/**
	 * Return a specific module
	 *
	 * @param string $name Module name
	 * @return Search_Module Module
	 **/
	function get( $name ) {
		$filename = implode( DIRECTORY_SEPARATOR, array( dirname( __FILE__ ), '..', 'modules', strtolower( str_replace( '_', '-', $name ) ).'.php' ) );
		
		if ( file_exists( $filename ) ) {
			include_once $filename;
		
			$obj     = new $name;
			$options = get_option( 'search_unleashed' );

			if ( isset( $options['modules'][$name] ) ) {
				$obj->load( $options['modules'][$name] );
				$obj->priority = isset( $options['modules'][$name]['priority'] ) ? $options['modules'][$name]['priority'] : 1.0;
			}

			if ( isset( $options['active'][$name] ) )
				$obj->active = true;
				
			return $obj;
		}
		
		return false;
	}
}

/**
 * Search module base class
 *
 * @package default
 **/
class Search_Module {
	var $active   = false;
	var $priority = 1.0;
	
	/**
	 * Pretty-print name of module
	 *
	 * @return string Name
	 **/
	function name() {
	}
	
	/**
	 * ID for module
	 *
	 * @return void
	 **/
	function id() {
		return strtolower( get_class( $this ) );
	}

	/**
	 * Highlight a section of text
	 *
	 * @return void
	 **/
	function highlight( $post, $words, $content ) {
		return '';
	}

	/**
	 * Display edit box
	 *
	 * @return void
	 **/
	function edit() {	
	}
	
	/**
	 * Display module HTML
	 *
	 * @return void
	 **/
	function load() {
	}
	
	/**
	 * Save module config
	 *
	 * @param array $data Module config
	 * @return array Module config
	 **/
	function save_options( $data ) {
		$val = $this->save( $data );
		$val['priority'] = floatval( $_POST['priority'] );
		return $val;
	}
	
	/**
	 * Gather data
	 *
	 * @return void
	 **/
	function gather( $data ) {
		return false;
	}

	/**
	 * Is this a post module?
	 *
	 * @return boolean
	 **/
	function is_post() {
		return false;
	}
	
	/**
	 * Is this a comment module?
	 *
	 * @return boolean
	 **/
	function is_comment() {
		return false;
	}
	
	/**
	 * Is this module running?
	 *
	 * @return boolean
	 **/
	function is_active() {
		return $this->active;
	}
	
	function field_name() {
		return 'field';
	}

	/**
	 * Return module field name
	 *
	 * @return string
	 **/
	function field_value( $data ) {
		return $data;
	}
	
	/**
	 * Save
	 *
	 * @return boolean
	 **/
	function save( $data ) {
	}
}

/**
 * Parent class for post modules
 *
 * @package default
 **/
class Search_Post_Module extends Search_Module {
	function is_post() {
		return true;
	}
}

/**
 * Parent class for comment modules
 *
 * @package default
 **/
class Search_Comment_Module extends Search_Module {
	function is_comment() {
		return true;
	}
}

