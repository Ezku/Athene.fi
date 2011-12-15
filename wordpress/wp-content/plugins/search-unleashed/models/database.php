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
 * Search Unleashed generic database
 *
 * @package default
 **/

class SU_Database {
	/**
	 * Upgrade database
	 *
	 * @return void
	 **/
	function upgrade( $old, $new ) {
		if ( $old < 3 ) {
			$this->remove();
		}
			
		$this->install();
		
		update_option( 'search_unleashed_version', $new );
	}

	/**
	 * Installs DB tables
	 *
	 * @return void
	 **/
	function install() {
		global $wpdb;

		$sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}search_phrases (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `phrase` varchar(200) NOT NULL default '',
		  `ip` varchar(18) NOT NULL,
		  `searched_at` datetime NOT NULL,
		  `referrer` varchar(255) default NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM CHARSET=utf8";
		
		if ( version_compare( mysql_get_server_info(), '4.0.18', '<' ) ) {
			foreach ( $sql AS $pos => $line )
				$sql[$pos] = str_replace( 'ENGINE=MyISAM ', '', $line );
		}
		
		foreach ( $sql AS $pos => $line )
			$wpdb->query( $line );
	}

	/**
	 * Removes DB tables
	 *
	 * @return void
	 **/
	function remove( $options = false ) {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}search_phrases" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}search" );
		
		if ( $options == true ) {
			delete_option( 'search_unleashed' );
		}
	}
}
