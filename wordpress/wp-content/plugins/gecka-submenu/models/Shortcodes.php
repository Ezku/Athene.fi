<?php

class Gecka_Submenu_Shortcodes {
	
	public function __construct()  {
		
		add_shortcode( 'submenu', 	array( $this, 'submenu') );
		add_shortcode( 'menu', 	array( $this, 'menu') );
        add_action( 'gk-menu', array( $this,'gk_menu'), 10, 1 );
				
	}
	
	public function menu ($Attributes) {

		$Menu = new Gecka_Submenu_Submenu();
		return $Menu->Get($Attributes);
		
	}
	
	public function submenu ($Attributes) {

        if( empty($Attributes['submenu']) ) $Attributes['submenu'] = 'current';

		return $this->menu($Attributes);
		
	}
	
	function gk_menu ($Options) {
	
	    $Menu = new Gecka_Submenu_Submenu($Options);

	    if( isset( $Options['echo']) && $Options['echo'] === false ) return $Menu->Get();
	    else $Menu->Show();
	
    }
	
}
