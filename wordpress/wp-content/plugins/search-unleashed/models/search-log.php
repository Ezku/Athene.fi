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

include_once dirname( __FILE__ ).'/incoming-search.php';

/**
 * Provides phrase logging
 *
 * @package default
 **/
class Search_Log {
	/**
	 * Constructor. Unpack data from the DB
	 *
	 * @param array $data DB array
	 * @return void
	 **/
	function Search_Log( $data = '' ) {
		if ( is_array( $data ) ) {
			foreach ( $data AS $key => $value )
				$this->$key = $value;
				
			$this->searched_at = mysql2date( 'U', $this->searched_at );
		}
	}
	
	/**
	 * Returns IP lookup
	 *
	 * @return string HTML for lookup
	 **/
	function ip() {
		return sprintf( '<a href="http://urbangiraffe.com/map/?ip=%s&amp;from=drainhole">%s</a>', htmlspecialchars( $this->ip ), htmlspecialchars( $this->ip ) );
	}
	
	/**
	 * Returns search phrase
	 *
	 * @return string HTML for phrase
	 **/
	function phrase()	{
		return sprintf( '<a href="%s" target="_blank">%s</a>', get_bloginfo( 'home' ).'?s='.urlencode( $this->phrase ), htmlspecialchars( $this->phrase ) );
	}
	
	/**
	 * Returns search referrer
	 *
	 * @return string HTML for referrer
	 **/
	function referrer() {
		if ( $this->referrer ) {
			$engine = new Incoming_Search( $this->referrer );
			return sprintf( '<a href="%s">%s</a>', $this->referrer, ucfirst( $engine->engine ) );
		}
		
		return '';
	}
	
	/**
	 * Logs a pharse
	 *
	 * @param string $phrase Search phrase
	 * @param string $referrer Search referrer
	 * @return void
	 **/
	function record( $phrase, $referrer = '' ) {
		global $wpdb;
		
		$phrase = $wpdb->escape( trim( $phrase ) );

		if ( isset( $_SERVER['REMOTE_ADDR'] ) )
		  $ip = $_SERVER['REMOTE_ADDR'];
		else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
		  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	
		$ip = substr( $ip, 0, 18 );
		if ( $referrer ) {
			if ( $referrer->engine != 'local' ) {
				$ref = $wpdb->escape( $referrer->referrer );
				$wpdb->query( "INSERT INTO {$wpdb->prefix}search_phrases (phrase,ip,searched_at,referrer) VALUES ('$phrase','$ip',NOW(),'$ref')" );
			}
		}
		else
			$wpdb->query( "INSERT INTO {$wpdb->prefix}search_phrases (phrase,ip,searched_at) VALUES ('$phrase','$ip',NOW())" );
	}
	
	/**
	 * Get a subset of the search logs
	 *
	 * @param SearchPager $pager Pager object
	 * @return array Array of Search_Log items
	 **/
	function get( &$pager )	{
		global $wpdb;
		
		$rows = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}search_phrases ".$pager->to_limits( '', array( 'phrase', 'ip' ) ), ARRAY_A );
		$pager->set_total( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
		
		$data = array();
		if ( $rows ) {
			foreach ( $rows AS $row )
				$data[] = new Search_Log( $row );
		}
		
		return $data;
	}
	
	/**
	 * Expires log entries older than the given number of days
	 *
	 * @param integer $days Expire anything over this number of days
	 * @return void
	 **/
	function expire( $days ) {
		global $wpdb;
		
		$wpdb->query( "DELETE FROM {$wpdb->prefix}search_phrases WHERE DATEDIFF(NOW(),searched_at) > $days" );
	}
	
	/**
	 * Remove log entries
	 *
	 * @param array $ids Optional array of IDs to delete, otherwise everything
	 * @return void
	 **/
	function delete_all( $ids = '' ) {
		global $wpdb;
		
		if ( $ids )
			$wpdb->query ("DELETE FROM {$wpdb->prefix}search_phrases WHERE id IN(".implode( ',', $ids ).')' );
		else
			$wpdb->query ("DELETE FROM {$wpdb->prefix}search_phrases");
	}
}
