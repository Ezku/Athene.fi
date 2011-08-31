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
class Incoming_Search {
	var $engine;
	var $search;
	var $referrer;
	
	function Incoming_Search( $referrer )	{
		if ( isset( $referrer ) && $referrer ) {
			$this->referrer     = $referrer;
			$this->this->search = '';
			
			$referrer = preg_replace( '@(http|https)://@', '', stripslashes( urldecode( $referrer ) ) );
			$args     = explode( '?', $referrer );
			$query    = array();

			if ( count( $args ) > 1 )
				parse_str( $args[1], $query );

			if ( substr( $referrer, 0, strlen( $_SERVER['SERVER_NAME'] ) ) == $_SERVER['SERVER_NAME'] && ( isset( $query['s'] ) || strpos( $referrer, '/search/' ) !== false ) ) {
				$this->engine = 'local';
				
				if ( isset( $query['s'] ) )
					$this->search = $query['s'];
				else
					$this->search = str_replace( '/search/', '', str_replace( $_SERVER['SERVER_NAME'], '', $referrer ) );
			}	elseif ( strpos( $referrer, 'google' ) !== false ) {
				$this->engine =  'google';
				$this->search = $query['q'];
				$this->search = preg_replace( '/\w+\:(.*)/', '$1', $this->search );
			} elseif ( strpos( $referrer, 'yahoo' ) !== false ) {
				$this->engine =  'yahoo';
				$this->search = $query['p'];
			} elseif ( strpos( $referrer, 'sogou' ) !== false ) {
				$this->engine =  'sogou';
				$this->search = $query['query'];
			} elseif ( strpos( $referrer, 'baidu' ) !== false ) {
				$this->engine =  'baidu';
				$this->search = $query['wd'];
			} elseif ( strpos( $referrer, 'lycos' ) !== false ) {
				$this->engine =  'lycos';
				$this->search = $query['query'];
			} elseif ( strpos( $referrer, 'altavista' ) !== false ) {
				$this->engine =  'altavista';
				$this->search = $query['q'];
			} elseif ( strpos( $referrer, 'search.msn' ) !== false || strpos( $referrer, 'search.live' ) !== false ) {
				$this->engine =  'msn';
				$this->search = $query['q'];
			} elseif ( strpos( $referrer, 'yandex' ) !== false ) {
				$this->engine =  'yandex';
				$this->search = $query['text'];
				
				// Yandex arrives in CP1251 format, so we need to convert to whatever format the blog is in
				if ( function_exists( 'mb_convert_encoding' ) )
					$this->search = mb_convert_encoding( $this->search, get_option( 'blog_charset' ), 'cp1251' );
				elseif ( function_exists( 'iconv' ) )
					$this->search = iconv( 'cp1251', get_option( 'blog_charset' ), $this->search );
			}
		}
	}

	function get_engine() {
		return $this->engine;
	}
	
	function get_search() {
		return explode( ' ', $this->search );
	}
	
	function get_engine_name() {
		$names = array (
			'msn'       => __( 'MSN', 'search-unleashed' ),
			'altavista' => __( 'Altavista', 'search-unleashed' ),
			'lycos'     => __( 'Lycos', 'search-unleashed' ),
			'baidu'     => __( 'Baidu', 'search-unleashed' ),
			'sogou'     => __( 'Sogou', 'search-unleashed' ),
			'google'    => __( 'Google', 'search-unleashed' ),
			'yandex'    => __( 'Yandex', 'search-unleashed' ),
			'local'     => __( 'Local', 'search-unleased' )
		);
		
		return $names[$this->engine];
	}
	
	function is_incoming_search() {
		return ( $this->engine && $this->search ) ? true : false;
	}
}
