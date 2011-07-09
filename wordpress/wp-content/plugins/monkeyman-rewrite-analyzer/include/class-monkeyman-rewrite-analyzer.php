<?php
/*
This plugin adds one page under "Tools" to display the current rewrite rules.

The patterns are matched using the Monkeyman_Regex class.
The substitutions are split up, assuming they are of the form
'index.php?query_var=$matches[1]&fixed_query_var=fixed_value'.
The query vars that are not marked as public are highlighted.

At the top of the page there is a test box where you can try out URLs.
This functionality is provided using Javascript. When a rule matches, the corresponding query values are filled in.

The pages (main page and help text) are stored in the ui/ directory.

If you find a way to break this plugin, or extend it, please let me know.
I welcome all bug reports and other contributions on my e-mail address:
jan.fabry@monkeyman.be


If you are the kind of person who reads plugin code, then I am sure we have some
challenging questions for you at the WordPress Stack Exchange. Mine, for example! [ http://wordpress.stackexchange.com/search?tab=votes&q=user%3a8%20hasaccepted%3a0 ]
*/
require_once( dirname( __FILE__ ) . '/class-monkeyman-regex.php' );

class Monkeyman_Rewrite_Analyzer
{
	protected $plugin_basename;
	protected $page_hook;
	protected $base_file;
	protected $gettext_domain = 'monkeyman-rewrite-analyzer';
	
	public function __construct( $base_file )
	{
		$this->base_file = $base_file;
		$this->plugin_basename = plugin_basename( $this->base_file );
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_filter( 'plugin_action_links_' . $this->plugin_basename, array( &$this, 'plugin_action_links' ) );
	}
	
	public function init()
	{
		load_plugin_textdomain( $this->gettext_domain, null, dirname( $this->plugin_basename ) . '/languages/' );
	}
	
	public function admin_menu()
	{
		$this->page_hook = add_management_page( __( 'Rewrite analyzer', $this->gettext_domain ), __( 'Rewrite analyzer', $this->gettext_domain ), 'administrator', 'monkeyman-rewrite-analyzer', array( &$this, 'do_analyze_page' ) );
		add_action( 'admin_print_styles-' . $this->page_hook, array( &$this, 'admin_print_styles' ) );
		add_action( 'admin_print_scripts-' . $this->page_hook, array( &$this, 'admin_print_scripts' ) );
		
		add_filter( 'contextual_help', array( &$this, 'contextual_help' ), 10, 3 );
	}
	
	public function admin_print_styles()
	{
		wp_enqueue_style( $this->gettext_domain, plugins_url( 'rewrite-analyzer.css', $this->base_file ) );
	}
	
	public function admin_print_scripts()
	{
		wp_enqueue_script( $this->gettext_domain, plugins_url( 'rewrite-analyzer.js', $this->base_file ), array( 'jquery' ), false, true );
	}
	
	public function plugin_action_links( $actions )
	{
		$actions[] = '<a href="' . menu_page_url( 'monkeyman-rewrite-analyzer', false ) . '">' . __( 'Show rewrite rules', $this->gettext_domain ) . '</a>';
		return $actions;
	}
	
	public function do_analyze_page()
	{
		$rewrite_rules = $GLOBALS['wp_rewrite']->wp_rewrite_rules();
		$rewrite_rules_ui = array();
		$public_query_vars = apply_filters( 'query_vars', $GLOBALS['wp']->public_query_vars );
		$rewrite_patterns = array();
		
		// URL prefix
		$prefix = '';
		if ( ! got_mod_rewrite() && ! iis7_supports_permalinks() ) {
			$prefix = '/index.php';
		}
		$url_prefix = get_option( 'home' ) .  $prefix . '/';
		
		$idx = 0;
		if ( $rewrite_rules ) {
			foreach ( $rewrite_rules as $pattern => $substitution ) {
				$idx++;
				$rewrite_patterns[$idx] = addslashes( $pattern );
				$rewrite_rule_ui = array(
					'pattern' => $pattern,
				);
				
				try {
					$regex_tree = Monkeyman_Regex::parse( $pattern );
				} catch ( Exception $e ) {
					$rewrite_rule_ui['error'] = $e;
				}
				$regex_groups = self::collect_groups( $regex_tree );
				
				$rewrite_rule_ui['print'] = self::print_regex( $regex_tree, $idx );
				
				$substitution_parts = self::parse_substitution( $substitution );
				
				$substitution_parts_ui = array();
				foreach ( $substitution_parts as $query_var => $query_value ) {
					$substitution_part_ui = array(
						'query_var' => $query_var,
						'query_value' => $query_value,
					);
					$query_value_ui = $query_value;
					
					// Replace `$matches[DD]` with URL regex part
					// This is so complicated to handle situations where `$query_value` contains multiple `$matches[DD]`
					$query_value_replacements = array();
					if ( preg_match_all( '/\$matches\[(\d+)\]/', $query_value, $matches, PREG_OFFSET_CAPTURE ) ) {
						foreach ( $matches[0] as $m_idx => $match ) {
							$regex_group_idx = $matches[1][$m_idx][0];
							$query_value_replacements[$match[1]] = array(
								'replacement' => self::print_regex( $regex_groups[$regex_group_idx], $idx, true ),
								'length' => strlen( $match[0] ),
								'offset' => $match[1],
							);
						}
					}
					krsort( $query_value_replacements );
					foreach ( $query_value_replacements as $query_value_replacement ) {
						$query_value_ui = substr_replace( $query_value_ui, $query_value_replacement['replacement'], $query_value_replacement['offset'], $query_value_replacement['length'] );
					}
					$substitution_part_ui['query_value_ui'] = $query_value_ui;
					
					// Highlight non-public query vars
					$substitution_part_ui['is_public'] = in_array( $query_var, $public_query_vars );
					$substitution_parts_ui[] = $substitution_part_ui;
				}
				
				$rewrite_rule_ui['substitution_parts'] = $substitution_parts_ui;
				$rewrite_rules_ui[$idx] = $rewrite_rule_ui;
			}
		}
		
		wp_localize_script( $this->gettext_domain, 'Monkeyman_Rewrite_Analyzer_Regexes', $rewrite_patterns );
		
		$gettext_domain = $this->gettext_domain;
		
		include( dirname( $this->base_file ) . '/ui/rewrite-analyzer.php' );
	}
	
	public static function print_regex( $regex, $idx, $is_target = false )
	{
		if ( is_a( $regex, 'Monkeyman_Regex_Group' ) ) {
			$output = '';
			if ( $is_target ) {
				$output .= '<span class="regexgroup-target-value" id="regex-' . $idx . '-group-' . $regex->counter . '-target-value"></span>';
			}
			$output .= '<span';
			if ( $regex->counter != 0 ) {
				$output .= ' class="regexgroup' . ( $is_target? '-target' : '' ) . '" id="regex-' . $idx . '-group-' . $regex->counter . ( $is_target? '-target' : '' ) . '">';
				$output .= '(';
			} else {
				$output .= '>';
			}
			foreach ( $regex as $regex_part ) {
				$output .= self::print_regex( $regex_part, $idx );
			}
			if ( $regex->counter != 0 ) {
				$output .= ')';
			}
			$output = self::wrap_repeater( $regex, $output );
			$output .= '</span>';
			return $output;
		}
		if ( is_a( $regex, 'Monkeyman_Regex_Range' ) ) {
			return self::wrap_repeater( $regex, '[' . $regex->value . ']' );
		}
		if ( is_a( $regex, 'Monkeyman_Regex_Escape' ) ) {
			return self::wrap_repeater( $regex, '\\' . $regex->value );
		}
		if ( is_a( $regex, 'Monkeyman_Regex_Char' ) ||
		     is_a( $regex, 'Monkeyman_Regex_Special' ) ) {
			return self::wrap_repeater( $regex, $regex->value );
		}
		if ( is_null( $regex ) ) {
			return 'Regex is empty!';
		}
		return 'Unknown regex class!';
	}
	
	public static function wrap_repeater( $regex, $value )
	{
		if ( $regex->repeater ) {
			$value = '<span class="regex-repeater-target">' .
				$value .
				'<span class="regex-repeater">' .
				$regex->repeater->value .
				'</span>' .
				'</span>';
			// Can a repeater have a repeater?
			// Probably not, '?' is a greedy modifier
			$value = self::wrap_repeater( $regex->repeater, $value );
		}
		return $value;
	}
	
	public static function collect_groups( $regex_tree )
	{
		$groups = array();
		if ( is_a( $regex_tree, 'Monkeyman_Regex_Group' ) ) {
			$groups[$regex_tree->counter] = &$regex_tree;
			foreach ( $regex_tree as $regex_child ) {
				$groups += self::collect_groups( $regex_child );
			}
		}
		return $groups;
	}
	
	public static function parse_substitution( $substitution )
	{
		if ( strncmp( 'index.php?', $substitution, 10 ) == 0 ) {
			$substitution = substr( $substitution, 10 );
		}
		parse_str( $substitution, $parsed_url_parts );
		
		$cleaned_url_parts = array();
		
		foreach ( $parsed_url_parts as $query_var => $query_value ) {
			if ( is_array( $query_value ) ) {
				foreach ( $query_value as $idx => $value ) {
					$cleaned_url_parts[$query_var . '[' . $idx . ']'] = $value;
				}
			} else {
				$cleaned_url_parts[$query_var] = $query_value;
			}
		}
		
		return $cleaned_url_parts;
	}
	
	public function contextual_help( $contextual_help, $screen_id, $screen )
	{
		if ( $this->page_hook == $screen_id ) {
			$gettext_domain = $this->gettext_domain;
			ob_start();
			include( dirname( $this->base_file ) . '/ui/rewrite-analyzer-help.php' );
			$contextual_help = ob_get_contents();
			ob_end_clean();
		}
		return $contextual_help;
	}
}