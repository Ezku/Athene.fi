<?php
/**
* SummarizePosts
*
* Handles the 'summarize-posts' shortcode and related template tags.
* Placeholders must be lowercase.  This acts as a front-end to the 
* powerful GetPostsQuery class.
*/
class SummarizePosts
{
	const name 			= 'Summarize Posts';
	const version 		= '0.7';
	// See http://php.net/manual/en/function.version-compare.php
	// any string not found in this list < dev < alpha =a < beta = b < RC = rc < # < pl = p
	const version_meta 	= 'dev'; // dev, rc (release candidate), pl (public release)

	const wp_req_ver 	= '3.1';
	const php_req_ver 	= '5.2.6';
	const mysql_req_ver	= '5.0.0';
	
	// used in the wp_options table
	const db_key			= 'summarize_posts';
	const admin_menu_slug 	= 'summarize_posts';
	
	public static $default_options = array(
		'group_concat_max_len' 	=> 4096,	// integer
		'output_type'			=> OBJECT, // ARRAY_N, OBJECT
	);
	// The default options after being read from get_option()
	public static $options; 
	
	// This goes to true if we were unable to increase the group_concat_max_len MySQL variable.
	// If true, this means we have to run an additional query for EACH post returned in order to
	// get that post's meta-data (i.e. to get that post's custom fields).  It's generally much
	// faster if we don't have to do that, but the crazy complicated MySQL query that grabs it 
	// all in one go does not work on all servers because it requires a beefy setting for the 
	// 'group_concat_max_len' variable, or the ability to set it manually before we do our big
	// query.
	public static $manually_select_postmeta = false;
	
	const txtdomain 	= 'summarize-posts';
	
	const result_tpl = '<li><a href="[+permalink+]">[+post_title+]</li>';
	
	// One placeholder can be designated 
	public static $help_placeholder = '[+help+]';
		
	// These are defaults for OTHER settings, outside of the get_posts()
	public static $formatting_defaults = array(
		'get_meta'		=> false,
		'before' 		=> '<ul class="summarize-posts">',
		'after' 		=> '</ul>',
		'paginate' 		=> false,
		'tpl'			=> null,
		'help'			=> false,
	);
	
	//! Private functions
	//------------------------------------------------------------------------------
	/**
	* Get the template (tpl) to format each search result.
	* @param	string	$content
	* @param	array	$args associative array
	*/
	private static function _get_tpl($content, $args)
	{

		$content = trim($content);
		if ( empty($content) )
		{
			$content = self::result_tpl; // default
		}
		elseif( !empty($args['tpl']) )
		{
			// strip possible leading slash
			$args['tpl'] = preg_replace('/^\//','',$args['tpl']);
			$file = ABSPATH .$args['tpl'];
			
			if ( file_exists($file) )
			{
				$content = file_get_contents($file);
			}
			else
			{
				// throw an error
			}
		}
		// Read from between [summarize-posts]in between[/summarize-posts]
		else
		{
			$content = html_entity_decode($content); 
			// fix the quotes back to normal 
			$content = str_replace(array('&#8221;','&#8220;'), '"', $content );
			$content = str_replace(array('&#8216;','&#8217;'), "'", $content );		
		}
		return $content;
	}


	
	
	//! Public Functions
	/**
	* Create custom post-type menu
	*/
	public static function create_admin_menu()
	 {
	 	add_options_page( 
	 		'Summarize Posts', 					// page title
	 		'Summarize Posts', 					// menu title
			'manage_options', 					// capability
	 		self::admin_menu_slug, 				// menu slug
	 		'SummarizePosts::get_admin_page' // callback	 	
	 	);
	}
	
	
	//------------------------------------------------------------------------------
	/**
	* 
	*/
	public static function format_results($results, $args)
	{
		$output = '';
		foreach ( $results as $r )
		{
			$output .= self::parse($args['tpl_str'], $r);
		}
		return $args['before'] . $output . $args['after'];
	}
	
	//------------------------------------------------------------------------------
	/**
	* 
	*/
	public static function get_admin_page()
	{
		$msg = '';
		
		if ( !empty($_POST) && check_admin_referer('summarize_posts_options_update','summarize_posts_admin_nonce') )
		{
			$new_values = array();
			$new_values['group_concat_max_len'] = (int) $_POST['group_concat_max_len'];
			$new_values['output_type'] = $_POST['output_type'];
			update_option( self::db_key, $new_values);
			$msg = '<div class="updated"><p>Your settings have been <strong>updated</strong></p></div>';
		}
		// Read Stored Values (i.e. recently saved values)
		self::$options = get_option(self::db_key, self::$default_options);
		
		$object_selected = '';
		$array_a_selected = '';
		if ( self::$options['output_type'] == OBJECT )
		{
			$object_selected = 'selected="selected"';	
		}
		else
		{
			$array_a_selected = 'selected="selected"';	
		}
		
		include('admin_page.php');
	
	}

		
	//------------------------------------------------------------------------------
	/**
	* Get from Array. Safely retrieves a value from an array, bypassing the 'isset()' 
	* errors.
	* INPUT:
	* 	$array (array) the array to be searched
	* 	$key (str) the place in that key to return (if available)
	* 	$default (mixed) default value to return if that spot in the array is not set
	*/
	public static function get_from_array($array, $key, $default='') 
	{		
		if ( isset($array[$key]) ) 
		{
			return $array[$key];
		}
		else
		{
			return $default;
		}
	}

	//------------------------------------------------------------------------------
	/**
	* @param	object	$QueryObj	Instantiation of GetPostsQuery
	*/
	public static function get_help_msg(&$QueryObj)
	{
		print $QueryObj; // relies on the __toString() magic method
	}
	
	//------------------------------------------------------------------------------
	/**
	* Retrieves a complete post object, including all meta fields.
	* Ah... get_post_custom() will treat each custom field as an array, because in WP
	* you can tie multiple rows of data to the same fieldname (which can cause some
	* architectural related headaches).
	* 
	* At the end of this, I want a post object that can work like this:
	* 
	* print $post->post_title;
	* print $post->my_custom_field; // not $post->my_custom_fields[0];
	* 
	* INPUT: $id (int) valid ID of a post (regardless of post_type).
	* OUTPUT: post object with all attributes, including custom fields.
	*/
	public function get_post_complete($id)
	{
		$complete_post = get_post($id, self::$options['output_type']);
		if ( empty($complete_post) )
		{
			return array();
		}
		$custom_fields = get_post_custom($id);
		if (empty($custom_fields))
		{
			return $complete_post;
		}
		foreach ( $custom_fields as $fieldname => $value )
		{
			if ( self::$options['output_type'] == OBJECT )
			{			
				$complete_post->$fieldname = $value[0];
			}
			// ARRAY_A
			else
			{
				$complete_post[$fieldname] = $value[0];		
			}
		}
		
		return $complete_post;	
	}

	//------------------------------------------------------------------------------
	/**
	* This is our __construct() effectively.
	* Handle a couple misspellings here
	*/
	public static function initialize()
	{	
		add_shortcode('summarize-posts', 'SummarizePosts::summarize');
		add_shortcode('summarize_posts', 'SummarizePosts::summarize');
	}

	//------------------------------------------------------------------------------
	/**
	 * Rewrite of built-in get_categories() function.
	 *
		http://codex.wordpress.org/Function_Reference/get_categories
		see wp-includes/category.php
		
	Adds a few extra attributes to the output.
	
	Returns an array of Ojects, e.g. 
	
	Array
	(
	    [0] => stdClass Object
	        (
	            [term_id] => 1
	            [name] => Uncategorized
	            [slug] => uncategorized
	            [term_group] => 0
	            [term_taxonomy_id] => 1
	            [taxonomy] => category
	            [description] => 
	            [parent] => 0
	            [count] => 1
	            [cat_ID] => 1
	            [category_count] => 1
	            [category_description] => 
	            [cat_name] => Uncategorized
	            [category_nicename] => uncategorized
	            [category_parent] => 0
	            [permalink]	=> http://pretasurf:8888/?taxonomy=collection&term=spring_summer_2011 
	            [is_active]	=> 1
	        )
	
	)
	*/
	public static function get_taxonomy_terms( $args = '' ) 
	{
		// get_categories() defaults
		$defaults = array(
		    'type'                     => 'post',
		    'child_of'                 => 0,
		    'parent'                   => null,
		    'orderby'                  => 'name',
		    'order'                    => 'ASC',
		    'hide_empty'               => 1,
		    'hierarchical'             => 1,
		    'exclude'                  => null,
		    'include'                  => null,
		    'number'                   => null,
		    'taxonomy'                 => 'category',
		    'pad_counts'               => false 
	    );
	
		// We use both so we can parse the URL type inputs that come in as a string.
		$args = wp_parse_args($args, $defaults); // This converts the input to an array
		$args = shortcode_atts($defaults, $args); // This will filter out invalid input
		
		$active_taxonomy = get_query_var('taxonomy');
		$active_slug = get_query_var('term');
		
		$taxonomies = get_categories($args);
		
		// Add a few custom attributes for convenience
		foreach ( $taxonomies as &$t )
		{
			$t->permalink = home_url("?taxonomy=$t->taxonomy&amp;term=$t->slug");
			if ( $t->slug == $active_slug )
			{
				$t->is_active = true;	
			}
			else
			{
				$t->is_active = false;			
			}
		}
		
		return $taxonomies;

	}

	//------------------------------------------------------------------------------
	/**
	 * Retrieve the post content and chop it off at the marker specified.  OMFG WP is
	 * so F'd up here. No reason to copy this function from wp-includes/post-template.php
	 * because the built-in function is a total mess.
	 * //! TODO... this is a mess
	 * The goal is to make this damn thing loop-agnostic.  Remove all the F'ing global variables.
	 
		the_content('read more &raquo;'); // This ignores the <!--more--> bit if used in a single template file. *facepalm*
		the <!--more--> bit is translated to <span id="more-524"></span> where 524 is the post id.
		This is my home-rolled version of how the_content() works.

	 */
	static function get_the_content($post_id, $content, $more_link_text = null, $stripteaser = 0) {

		$content = get_the_content( 'read more &raquo;');
		#print $content;
		// $post_id = get_the_ID();
		//print $post_id; exit;
		// $more = '<span id="more-'.$post_id.'"></span>';
		$more = '<span id="more';
#						$more = preg_quote('<span id="more-'.$post_id.'"></span>');						
		// print $more; exit;
		$content = preg_replace('/'.$more.'.*$/ms', '', $content);
		$content = strip_tags($content) . '<a href="'.get_permalink(get_the_ID()).'">read more &raquo;</a>'; 
		
		return $content;
	}
	
	//------------------------------------------------------------------------------
	/**
	SYNOPSIS: a simple parsing function for basic templating.
	INPUT:
		$tpl (str): a string containing [+placeholders+]
		$hash (array): an associative array('key' => 'value');
	OUTPUT
		string; placeholders corresponding to the keys of the hash will be replaced
		with the values and the string will be returned.
	*/
	public static function parse($tpl, $hash) 
	{
		$verbose_placeholders = array(); // used for populating [+help+]
		
	    foreach ($hash as $key => $value) 
	    {
	    	if ( !is_array($value) )
	    	{
	        	$tpl = str_replace('[+'.$key.'+]', $value, $tpl);
	        }
	    }
	    
	    // Remove any unparsed [+placeholders+]
	    $tpl = preg_replace('/\[\+(.*?)\+\]/', '', $tpl);
	    
	    return $tpl;
	}

	//------------------------------------------------------------------------------
	/**
	* print_notices
	
	Print errors if they were thrown by the tests. Currently this is triggered as 
	an admin notice so as not to disrupt front-end user access, but if there's an
	error, you should fix it! The plugin may behave erratically!
	INPUT: none... ideally I'd pass this a value, but the WP interface doesn't make
		this easy, so instead I just read the class variable: SummarizePostsTests::$errors
	OUTPUT: none directly.  But errors are printed if present.
	*/
	public static function print_notices()
	{
		if ( !empty(SummarizePostsTests::$errors) )
		{

			$error_items = '';
			foreach ( SummarizePostsTests::$errors as $e )
			{
				$error_items .= "<li>$e</li>";
			}

			$msg = sprintf( __('The %s plugin encountered errors! It cannot load!', self::txtdomain)
				, self::name);

			printf('<div id="summarize-posts-warning" class="error">
					<p>
					<strong>%1$s</strong>
					<ul style="margin-left:30px;">
						%2$s
					</ul>
				</p>
				</div>'
				, $msg
				, $error_items);

		}
	}
	//------------------------------------------------------------------------------
	/**
	*
	http://codex.wordpress.org/Template_Tags/get_posts
	sample usage
	
	shortcode params:
	
	'numberposts'     => 5,
    'offset'          => 0,
    'category'        => ,
    'orderby'         => any valid column from the wp_posts table (minus the "post_")
    	ID
		author
		date
		date_gmt
		content
		title
		excerpt
		status
		comment_status
		ping_status
		password
		name
		to_ping
		pinged
		modified
		modified_gmt
		content_filtered
		parent
		guid
		menu_order
		type
		mime_type
		comment_count
		
		rand -- randomly sort results. This is not compatible with the paginate options! If set, 
			the 'paginate' option will be ignored!
    
    'order'           => 'DESC',
    'include'         => ,
    'exclude'         => ,
    'meta_key'        => ,
    'meta_value'      => ,
    'post_type'       => 'post',
    'post_mime_type'  => ,
    'post_parent'     => ,
    'post_status'     => 'publish'

	** CUSTOM **
	get_meta
	before
	after
	paginate	true|false


placeholders:
	[+help+]
	
	[shortcode x="1" y="2"]<ul>Formatting template goes here</ul>[/shortcode]
	
	The $content comes from what's between the tags.
	
A standard post has the following attributes:
    [ID] => 6
    [post_author] => 2
    [post_date] => 2010-11-13 20:13:28
    [post_date_gmt] => 2010-11-13 20:13:28
    [post_content] => http://pretasurf.com/blog/wp-content/uploads/2010/11/cropped-LIFE_04_DSC_0024.bw_.jpg
    [post_title] => cropped-LIFE_04_DSC_0024.bw_.jpg
    [post_excerpt] => 
    [post_status] => inherit
    [comment_status] => closed
    [ping_status] => open
    [post_password] => 
    [post_name] => cropped-life_04_dsc_0024-bw_-jpg
    [to_ping] => 
    [pinged] => 
    [post_modified] => 2010-11-13 20:13:28
    [post_modified_gmt] => 2010-11-13 20:13:28
    [post_content_filtered] => 
    [post_parent] => 0
    [guid] => http://pretasurf.com/blog/wp-content/uploads/2010/11/cropped-LIFE_04_DSC_0024.bw_.jpg
    [menu_order] => 0
    [post_type] => attachment
    [post_mime_type] => image/jpeg
    [comment_count] => 0
    [filter] => raw



But notice that some of these are not very friendly.  E.g. post_author, the user expects the author's name.  So we do some duplicating, tweaking to make this easier on the user.

Placeholders:

Generally, these correspond to the names of the database columns in the wp_posts table, but some 
convenience placeholders were added.

drwxr-xr-x   8 everett2  staff   272 Feb  5 20:16 .
[+ID+]
[+post_author+]
[+post_date+]
[+post_date_gmt+]
[+post_content+]
[+post_title+]
[+post_excerpt+]
[+post_status+]
[+comment_status+]
[+ping_status+]
[+post_password+]
[+post_name+]
[+to_ping+]
[+pinged+]
[+post_modified+]
[+post_modified_gmt+]
[+post_content_filtered+]
[+post_parent+]
[+guid+]
[+menu_order+]
[+post_type+]
[+post_mime_type+]
[+comment_count+]
[+filter+]

Convenience:

[+permalink+]
[+the_content+]
[+the_author+]
[+title+]
[+date+]
[+excerpt+]
[+mime_type+]
[+modified+]
[+parent+]
[+modified_gmt+]

;

	*/
	public static function summarize($raw_args=array(), $content_tpl = null)
	{	
		$formatting_args = shortcode_atts( self::$formatting_defaults, $raw_args );

		$formatting_args['tpl_str'] = self::_get_tpl($content_tpl, $formatting_args);		

		$output = '';
		//print_r($formatting_args); exit;
		$Q = new GetPostsQuery( $raw_args );
		$results = $Q->get_posts();

		// Print help message.  Should include the SQL statement, errors
		if (isset($raw_args['help']) )
		{
			self::get_help_msg($Q); // this prints the results
		}
		else
		{
			$output .= self::format_results($results, $formatting_args);
			if ( $Q->paginate )
			{
				$output .= '<div class="summarize-posts-pagination-links">'.$Q->get_pagination_links().'</div>';
			}
		}
		
		return $output;
	}
}
/*EOF*/