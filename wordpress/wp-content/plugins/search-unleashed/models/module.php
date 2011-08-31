<?php

class Search_Module
{
	function name () { }
	function id () { return strtolower (get_class ($this)); }
	function highlight ($post, $words, $content) { return '';}
	function has_config () { return false; }
	function edit () { }
	function load () { }
	function gather_for_post ($post) { return ''; }
	function gather_for_comment ($post) { return ''; }
	function gather_for_priority ($post) { return ''; }
}

?>
