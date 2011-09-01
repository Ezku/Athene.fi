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

    private $current_menu_stack = array();
    
    private $options = array(
        'levels_shown' => array(0,1,2),
        'only_current_branch' => true
    );
    
    /**
     * @var array<string | Closure>
     */
    private $wrap = array(
        'link' => "%s",
        'intro' => "%s",
        'item' => "%s"
    );
    
    /**
     * @var array<string>
     */
    private $format = array(
        'lvl_start' => "\n%s<ul class=\"sub-menu\">\n",
        'lvl_end' => "%s</ul>\n",
        'el_start' => '%s<li%s>',
        'link' => '<a%s>%s</a>',
        'intro' => '<div class="intro">%s</div>',
        'el_end' => "</li>\n",
    );
    
    /**
     * @var array<depth => array | Closure>
     */
    private $depth_classes = array(
        0 => array(),
        1 => array(),
        2 => array()
    );
    
    function __construct($options = array(), $wrap = array(), $depth_classes = array()) {
        $this->options = $options + $this->options;
        $this->wrap = $wrap + $this->wrap;
        $this->depth_classes = $depth_classes + $this->depth_classes;
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
		array_push($this->current_menu_stack, $item);
		if (!$this->toBeShown($depth)) {
		    return;
	    }
	    $indent = $this->indent($depth);
	
	    $output .= $this->format('el_start',
	        $this->indent($depth),
	        $this->item_id($item, $args) . $this->item_classes($item, $args, $depth));
        $item_output = $this->item_output($item, $args, $depth);
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
        return $output;
	}
	
	private function item_classes($item, $args, $depth) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		$classes = array_merge($classes, $this->item_depth_classes($depth));

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = ' class="' . esc_attr( $class_names ) . '"';
		return $class_names;
	}
	
	private function item_depth_classes($depth)
	{
	    $classes = array();
	    if (isset($this->depth_classes[$depth])) {
	        $classes = $this->depth_classes[$depth];
	        if (!is_array($classes)) {
	            $classes = (array) $classes($depth);
	        }
	    }
	    return $classes;
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
	    if ($this->showCurrentBranchOnly()) {
	        if (!$this->isItemOnCurrentBranch()) {
	            return false;
	        }
	    }
	    
	    $levels = $this->levelsToShow();
	    if (is_array($levels)) {
	        return in_array($level, $levels);
	    } else {
	        return $level == $levels;
	    }
	}
	
	private function isItemOnCurrentBranch() {
        foreach($this->current_menu_stack as $stack_item) {
            if (in_array("current-menu-item",$stack_item->classes) ||
                    in_array("current-menu-ancestor",$stack_item->classes)) {
                return true;
            }
        }
        return false;
	}
	
	private function levelsToShow() {
	    return $this->option('levels_shown');
	}
	
	private function showCurrentBranchOnly() {
	    return (bool) $this->option('only_current_branch');
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
	
	private function option($name)
	{
	    if (!isset($this->options[$name])) {
	        throw new Exception('Undefined option: ' . $name);
	    }
	    return $this->options[$name];
	}
}

?>