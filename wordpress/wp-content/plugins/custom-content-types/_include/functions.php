<?php

if(!function_exists("get_content_types")){

	function get_content_types(){
		global $cct;
		return $cct->get_content_types();
	}

}


if(!function_exists("add_content_type")){

	function add_content_type(){
		global $cct;
		return $cct->get_content_types();
	}

}

?>