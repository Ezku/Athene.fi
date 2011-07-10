<?php
/**
 * @package SubMenuWalker
 * @uses Walker
 * @version 1.0
 */
/*
Plugin Name: Submenu Walker
Plugin URI: http://pkroger.org
Description: 
Author: Pyry Kröger
Version: 1.0
Author URI: http://pkroger.org/
*/

/**
 * Create HTML list of subnav menu items.
 */
class SubMenuWalker extends Walker {
  
  function __construct($levels_shown, $only_current = true) {
    $this->levels_shown = $levels_shown;
    $this->only_current = $only_current;
    $this->current_menu_stack = array();
  }
  
  var $levels_shown;
  var $only_current;
  var $current_menu_stack;
  
	/**
	 * @see Walker::$tree_type
	 * @var string
	 */
	var $tree_type = array( 'post_type', 'taxonomy', 'custom' );

	/**
	 * @see Walker::$db_fields
	 * @todo Decouple this.
	 * @var array
	 */
	var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

	/**
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function start_lvl(&$output, $depth) {
	  if ($this->toBeShown($depth)) {
		  $indent = str_repeat("\t", $depth);
		  $output .= "\n$indent<ul class=\"sub-menu\">\n";
	  }
	}

	/**
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function end_lvl(&$output, $depth) {
	  if ($this->toBeShown($depth)) {
		  $indent = str_repeat("\t", $depth);
		  $output .= "$indent</ul>\n";
	  }
	}

	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	function start_el(&$output, $item, $depth, $args) {
		global $wp_query;
		array_push($this->current_menu_stack, $item);
		if ($this->toBeShown($depth)) {
		  $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

  		$class_names = $value = '';

  		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

  		$classes[] = 'menu-item-' . $item->ID;

  		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
  		$class_names = ' class="' . esc_attr( $class_names ) . '"';

  		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
  		$id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';
  		
  		$item_intro = get_post_complete($item->object_id)->intro;

  		$output .= $indent . '<li' . $id . $value . $class_names .'>';

  		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
  		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
  		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
  		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

  		$item_output = $args->before;
  		$item_output .= '<a'. $attributes .'>';
  		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
  		$item_output .= '<br/>'.$item_intro;
  		$item_output .= '</a>';
  		$item_output .= $args->after;

      $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
	}

	/**
	 * @see Walker::end_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el(&$output, $item, $depth) {
		$output .= "</li>\n";
		array_pop($this->current_menu_stack);
	}
	
	private function toBeShown($level) {
	  if ($this->only_current) {
	    $current = false;
	    foreach($this->current_menu_stack as $stack_item) {
	      if (in_array("current-menu-item",$stack_item->classes) ||
	          in_array("current-menu-ancestor",$stack_item->classes)) {
	        $current = true;
	      }
	    }
	    if (!$current) {
	      return false;
	    }
	  }
	  if (is_array($this->levels_shown)) {
	    return in_array($level,$this->levels_shown);
	  } else {
	    return $level == $this->levels_shown;
	  }
	}
}

?>