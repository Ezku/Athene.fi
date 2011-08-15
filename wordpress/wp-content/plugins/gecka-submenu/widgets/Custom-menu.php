<?php

class GKSM_Widget_Custom_Menu extends WP_Widget {
	
	protected $default_options = array( 
	                                    'auto_title'=>0, 
	                                    'show_parent'=>0,
	                                    'show_description'=>0, 
	                                     );
	
	public function GKSM_Widget_Custom_Menu () {
		$widget_ops = array('classname' => 'gksm-custom-menu-widget', 'description' => __('Use this widget to add one of your custom menus as a widget.'));
		parent::WP_Widget('gksm-custom-menu-widget', __('Custom Menu') . ' ' .  __('[advanced]', Gecka_Submenu::Domain ), $widget_ops);
	    
	    // ajax select update
	    add_action('wp_ajax_gksm_update', array($this, 'submenuOptionsAjax'));
	    
	    // needed javasccript for widget admin      
	    wp_register_script('gecka-submenu-widget-custom-menu', GKSM_URL . '/javascripts/widget-custom-menu.js', 'jquery' );
        
        add_action( "admin_print_scripts-widgets.php", array($this, 'admin_print_scripts') );
        
	    
    }
    
    public function admin_print_scripts () {
        wp_enqueue_script('gecka-submenu-widget-custom-menu');
        
        wp_localize_script( 'gecka-submenu-widget-custom-menu', 'gksm_widget', array(
	        'custom_menu_nonce' => wp_create_nonce( 'gksm_widget_custom_menu_nonce' ),
	                            )
                        );

    }
    
    public function widget($args, $instance) {

    	$Menu = new Gecka_Submenu_Submenu($instance);
        $Menu->Widget($args, $instance);

    }
    
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        
        $instance['title'] = strip_tags( stripslashes($new_instance['title']) );
        $instance['menu']  = (int) $new_instance['menu'];
        $instance['submenu']  = $new_instance['submenu'];
        
        if( !in_array($instance['submenu'], array('top', 'parent', 'current'))) $instance['submenu'] = (int)$instance['submenu'];
        
        foreach ( $this->default_options as $field => $val ) {
            if ( isset($new_instance[$field]) )
                $instance[$field] = 1;
        }
        
        $instance['depth'] = (int) $new_instance['depth'];
        
        $instance['show_description'] = $new_instance['show_description'];
        
        return $instance;
    }
	
    public function form($instance) {              
        
    	$instance = wp_parse_args( (array) $instance, $this->default_options);
        
    	$title     = isset( $instance['title'] ) ? $instance['title'] : '';
        $menu      = isset( $instance['menu'] ) ? $instance['menu'] : '';
        $submenu   = isset( $instance['submenu'] ) ? $instance['submenu'] : '0';
        $depth     = isset( $instance['depth'] ) ? $instance['depth'] : 0;
        
         // Get menus
        $menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

        // If no menus exists, direct the user to go and create some.
        if ( !$menus ) {
            echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
            return;
        }
   
        ?>
            
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </p>

            <p><input class="checkbox" type="checkbox" <?php checked($instance['auto_title'], true) ?> id="<?php echo $this->get_field_id('auto_title'); ?>" name="<?php echo $this->get_field_name('auto_title'); ?>" />
            <label for="<?php echo $this->get_field_id('auto_title'); ?>"><?php _e('Use top parent item title', Gecka_Submenu::Domain); ?></label><br />
            </p>
            
            <p><label for="<?php echo $this->get_field_id('menu'); ?>"><?php _e('Select Menu:'); ?></label>
            <select id="<?php echo $this->get_field_id('menu'); ?>" name="<?php echo $this->get_field_name('menu'); ?>" onchange="gksm_widget_update_select(this, '#<?php echo $this->get_field_id('submenu'); ?>');" >
            <?php 
            echo '<option value="">' . __('Select') . '</option>';
            
           
            foreach ( $menus as $_menu ) {
                $selected = $menu == $_menu->term_id ? ' selected="selected"' : '';
                echo '<option'. $selected .' value="'. $_menu->term_id .'">'. $_menu->name .'</option>';
            
            }	
            ?>
            </select></p>
            
            <p><label for="<?php echo $this->get_field_id('submenu'); ?>"><?php _e('Start menu from:', Gecka_Submenu::Domain); ?></label>
            <select id="<?php echo $this->get_field_id('submenu'); ?>" name="<?php echo $this->get_field_name('submenu'); ?>" >
            
            <?php if(!sizeof($instance)) echo '<option'. $selected .' value="'. $_menu->term_id .'">'.__('Click save first', Gecka_Submenu::Domain).'</option>';
            else 
            echo $this->menu_items_options ($menu, $submenu)
            ?>
            </select></p>
            
            <p><label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e( 'Depth:', Gecka_Submenu::Domain ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>" type="text" value="<?php echo $depth; ?>" />
            </p>
            
            <p>         
            <!-- <input class="checkbox" type="checkbox" <?php checked($instance['show_parent'], true) ?> id="<?php echo $this->get_field_id('show_parent'); ?>" name="<?php echo $this->get_field_name('show_parent'); ?>" />
	        <label for="<?php echo $this->get_field_id('show_parent'); ?>"><?php _e('Show parent item', Gecka_Submenu::Domain); ?></label><br />
	         -->
	        
	        <label for="<?php echo $this->get_field_id('show_description'); ?>"><?php _e('Show description', Gecka_Submenu::Domain); ?>:</label><br />
	        <select name="<?php echo $this->get_field_name('show_description'); ?>" id="<?php echo $this->get_field_id('show_description'); ?>">
	            <option value="0" <?php selected('0', $instance['show_description']) ?>><?php _e('No'); ?></option>
	            <option value="into_link" <?php selected('into_link', $instance['show_description']) ?>><?php _e('Into link element', Gecka_Submenu::Domain); ?></option>
	            <option value="into_li" <?php selected('into_li', $instance['show_description']) ?>><?php _e('Into li element', Gecka_Submenu::Domain); ?></option>
	        </select>
	        </p>
        
        <?php 
    }
	
    private function getMenuItem ($item_id, &$menu_items) {
    	
       foreach($menu_items as $Item) {
            if($Item->ID == $item_id) return $Item;
       }
       return false;
    }
	
	function submenuOptionsAjax() {
        
    	check_ajax_referer('gksm_widget_custom_menu_nonce');
    	
        $id = isset($_POST['ID'])? (int)$_POST['ID'] : null;
        if(!$id) die('<option>' . __('First select a nav menu', Gecka_Submenu::Domain) .'</option>');
        echo $this->menu_items_options($id);
        
        die();
    }
	    

    public function menu_items_options ($id, $default='') {
    	
    	if(!$id) return '<option>' . __('First select a nav menu', Gecka_Submenu::Domain) .'</option>';
    	
        // Get the nav menu based on the requested menu
        $menu = wp_get_nav_menu_object( $id );
        
        if ( $menu && ! is_wp_error($menu) && !isset($menu_items) )
            $menu_items = wp_get_nav_menu_items( $menu->term_id );
        else return '<option>' . __('Error: Unknow menu.', Gecka_Submenu::Domain) .'</option>';
        
        $walker = new Walker_Nav_Menu_DropDown;
        
        $args = array( $menu_items, 0, array( 'selected'=>$default ) );

        $options = '<option value="0">' . __('Nav menu root', Gecka_Submenu::Domain) .'</option>';
        $options .= '<option value="current"'.selected('current', $default).'>' . __('Current page', Gecka_Submenu::Domain) .'</option>';
        $options .= '<option value="top"'.selected('top', $default).'>' . __('Current page\'s top parent', Gecka_Submenu::Domain) .'</option>';
        $options .= call_user_func_array( array(&$walker, 'walk'), $args );  
        
        return $options;
        
    }
	
	
}
