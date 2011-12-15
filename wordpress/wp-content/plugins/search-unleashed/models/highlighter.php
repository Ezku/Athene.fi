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
 * Text highlighting
 *
 * @package default
 **/
class Highlighter {
	var $first_match = -1;
	var $text        = '';
	var $words       = array ();
	
	/**
	 * Constructor.
	 *
	 * @param string $text Original text
	 * @param array $words Array of words to highlight
	 * @param boolean $strip Optional removal of HTML
	 * @return void
	 **/
	function Highlighter( $text, $words, $strip = false ) {
		if ( $strip )
			$this->text = $this->strip( $text );
		else
			$this->text = $text;

		$this->matches     = 0;
		$this->first_match = strlen( $text );

		// Find the first matched term
		foreach ( (array)$words AS $word ) {
			if ( preg_match( '/('.$word.')(?!=")/i', $this->text, $matches, PREG_OFFSET_CAPTURE ) > 0 )
				$this->first_match = min( $this->first_match, $matches[0][1] );
				
			$this->words[] = $word;
		}

		$this->words = array_filter( $this->words );
		$this->words = apply_filters( 'su_highlight_words', $this->words );
		
		if ( $this->first_match >= strlen( $this->text ) )
			$this->first_match = -1;
	}
	
	/**
	 * Remove all HTML
	 *
	 * @param string $text HTML text
	 * @return string Plain text
	 **/
	function strip( $text )	{
		$text = preg_replace( preg_encoding( '/<script(.*?)<\/script>/s' ), '', $text );
		$text = preg_replace( preg_encoding( '/<!--(.*?)-->/s' ), '', $text );
		
		$text = str_replace( '>', '> ', $text );   // Makes the strip function look better
		$text = wp_filter_nohtml_kses( $text );
		$text = stripslashes( $text );
		$text = preg_replace( preg_encoding( '/<!--(.*?)-->/s' ), '', $text );
		$text = strip_html( $text );    // Remove all HTML
		
		return $text;
	}

	/**
	 * Zooms to a particular portion of text that represents the first highlighted term
	 * If no highlighted term is found the zoomed portion is set to the start
	 *
	 * @param integer $before Number of characters to display before the first matched term
	 * @param integer $after Number of characters to display after the first matched term
	 * @return string Zoomed text
	 **/
	function zoom( $before = 100, $after = 400 ) {
		$text = $this->text;

		// Now zoom
		if ( $this->first_match != -1 ) {
			$start = max( 0, $this->first_match - $before );
			$end   = min( $this->first_match + $after, strlen( $text ) );

			$new = substr( $text, $start, $end - $start );
			
			if ( $start != 0 )
				$new = preg_replace( '/^[^ ]*/', '', $new );
				
			if ( $end != strlen( $text ) )
				$new = preg_replace( '/[^ ]*$/', '', $new );
			
			$new = str_replace( ' ,', ',', $new );
			$new = str_replace( ' .', '.', $new );

			$new = trim( $new );
			$text = ( $start > 0 ? '&hellip; ' : '' ).$new.( $end < strlen( $text ) ? ' &hellip;' : '' );
		}
		elseif ( $this->first_match == -1 ) {
			$text = substr( $text, 0, $after );
			$text = preg_replace( '/[^ ]*$/', '', $text );
			$text .= '&hellip;';
		}
		
		$this->text = $text;
	}
	
	/**
	 * Does this instance have any matched terms?
	 *
	 * @param string
	 * @return void
	 **/
	function has_matches() {
		return $this->first_match != -1;
	}
	
	/**
	 * Reformat zoomed text suitable for display
	 *
	 * @param string $text Text to reformat
	 * @return string Reformatted text
	 **/
	function reformat( $text ) {
		return str_replace( '<br />', '<br /><br />', wpautop( $text ) );
	}
	
	/**
	 * Get highlighted text
	 *
	 * @return string Text
	 **/
	function get() {
		return $this->text;
	}
	
	/**
	 * Highlight individual words
	 *
	 * @param object $links Not sure
	 * @return string Highlighted text
	 **/
	function mark_words( $links = false ) {
		$text = $this->text;
		$html = strpos( $text, '<' ) === false ? false : true;
		
		$this->mark_links = $links;
		foreach ( $this->words AS $pos => $word ) {
			if ( $pos > 5 )
				$pos = 1;
		
			$this->word_count = 0;
			$this->word_pos   = $pos;

			if ( $html )
				$text = @preg_replace_callback( preg_encoding( '/(?<=>)([^<]+)?('.$word.')(?!=")/i' ), array( &$this, 'highlight_html_word' ), $text );
			else
				$text = preg_replace_callback( '/('.$word.')(?!=")/iu', array( &$this, 'highlight_plain_word' ), $text );
		}
		
		$this->text = $text;
    return $text;
	}

	/**
	 * Highlight plain text word
	 *
	 * @return void
	 **/
	function highlight_plain_word( $words ) {
		$id = '';
		if ( $this->word_count == 0 && $this->mark_links )
			$id = 'id="high_'.( $this->word_pos + 1 ).'"';

		$this->word_count++;
		return '<span '.$id.' class="searchterm'.( $this->word_pos + 1 ).'">'.$words[1].'</span>';
	}

	/**
	 * Highlight HTML word
	 *
	 * @return void
	 **/
	function highlight_html_word( $words ) {
		$id = '';
		if ( $this->word_count == 0 && $this->mark_links )
			$id = 'id="high_'.( $this->word_pos + 1 ).'"';
			
		$this->word_count++;
		return $words[1].'<span '.$id.' class="searchterm'.( $this->word_pos + 1 ).'">'.$words[2].'</span>';
	}
}
