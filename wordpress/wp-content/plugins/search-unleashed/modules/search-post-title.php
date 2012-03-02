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
class Search_Post_Title extends Search_Post_Module {
	function gather( $data ) {
		return apply_filters( 'the_title', $data->post_title, '', '' );
	}
	
	function field_name() {
		return 'post_title';
	}

	function name () {
		return __( 'Post/page title', 'search-unleashed' );
	}
}
