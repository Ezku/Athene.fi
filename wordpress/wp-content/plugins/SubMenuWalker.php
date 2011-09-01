<?php
/**
 * @package SubMenuWalker
 * @uses Walker
 * @version 1.0
 */
/*
Plugin Name: Submenu Walker
Plugin URI: http://pkroger.org
Description: Submenu Walker enables submenus and sitemaps for themes. Requires code changes in theme menu handling, namely specifying a new SubMenuWalker(). See source for details.
Author: Pyry Kröger
Version: 1.2
Author URI: http://pkroger.org/
*/

/**
 * Create HTML list of subnav menu items.
 */
class SubMenuWalker extends Walker {

    var $levels_shown;
    var $only_current;
    var $current_menu_stack;
    
    private $format = array(
        'lvl_start' => "\n%s<ul class=\"sub-menu\">\n",
        'lvl_end' => "%s</ul>\n",
        'el_start' => '%s<li%s>',
        'link' => '<a%s>%s</a>',
        'intro' => '<div class="intro">%s</div>',
        'el_end' => "</li>\n",
    );
    
    private $wrap = array(
        'link' => "%s",
        'intro' => "%s",
        'item' => "%s"
    );
    
    function __construct($levels_shown, $only_current = true, $wrap = array()) {
        $this->levels_shown = $levels_shown;
        $this->only_current = $only_current;
        $this->wrap = $wrap + $this->wrap;
        
        $this->current_menu_stack = array();
    }
    
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
	        $output .= $this->format('lvl_start', $this->indent($depth));
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
		    $output .= $this->format('lvl_end', $this->indent($depth));
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
		if (!$this->toBeShown($depth)) {
		    return;
	    }
	    $indent = $this->indent($depth);
	
	    $output .= $this->format('el_start',
	        $this->indent($depth),
	        $this->item_id($item, $args) . $this->item_classes($item, $args));
        $item_output = $this->item_output($item, $args, $depth);
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
        return $output;
	}
	
	private function item_classes($item, $args) {
		$class_names = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		$classes[] = 'menu-item-' . $item->ID;

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = ' class="' . esc_attr( $class_names ) . '"';
		return $class_names;
	}
	
	private function item_id($item, $args) {
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';
		return $id;
	}
	
	private function item_output($item, $args, $depth) {
		$item_output = $args->before;
		$item_output .= $this->item_link($item, $args, $depth);
		$item_output .= $this->item_intro($item);
		$item_output .= $args->after;
		return $this->wrap('item', $item_output, $depth);
	}
	
	private function item_link($item, $args, $depth) {
	    $link = $this->format('link',
	        $this->item_attributes($item),
	        $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after);
	    
		return $this->wrap('link', $link, $depth);
	}
	
	private function item_attributes($item) {
	    $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
	    $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
	    $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
	    $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
	    return $attributes;
	}
	
	private function item_intro($item) {
	    $intro = get_post_complete($item->object_id)->intro;
	    if (strlen($intro)) {
	        $intro = $this->format('intro', $intro);
    	    return $this->wrap('intro', $intro);
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
		$output .= $this->format('el_end');
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
	
	private function indent($depth) {
	    return empty($depth) ? '' : str_repeat( "\t", $depth );
	}
	
	private function wrap($name, $content, $depth = null) {
	    if (empty($this->wrap[$name])) {
	        return $content;
	    }
	    $wrapper = $this->wrap[$name];
	    if (is_callable($wrapper)) {
	        return $wrapper($content, $depth);
	    }
	    return sprintf($wrapper, $content);
	}
	
	private function format($name /*, $args... */)
	{
	    $args = func_get_args();
	    array_shift($args);
	    
	    return vsprintf($this->format[$name], $args);
	}
}

?>