<?php
/**
* GetPostsQuery
* 
* New and improved post selection functions, now with formatting!
*
* This class has similar functionality (and arguments) to the WordPress
* get_posts() function, but this class does things that were simply not
* possible using the built-in WP function.  In particular, paginating
* results was not possible using the built-in function because WP did
* not make a "count_results" function available (?). 
* 
* I've constructed a custom MySQL query that does the searching because I ran into
* weird and whacky restrictions with the WP db API functions; this lets me
* join on foreign tables and cut down on multiple inefficient select queries.

TODO: Nonces for search forms.

wp_create_nonce('cctm_delete_field')
$nonce = self::_get_value($_GET, '_wpnonce');
if (! wp_verify_nonce($nonce, 'cctm_delete_field') ) {
	die( __('Invalid request.', CCTM_TXTDOMAIN ) );
}

* 
*/
class GetPostsQuery
{
	// Used to separate post data from wp_postmeta into key=>value pairs.
	// These values should be distinct enough so they will NOT appear in 
	// any of the custom fields' content.
	const colon_separator = '::::';
	const comma_separator = ',,,,';
	// Used to check that the group_concat is getting everything.
	const caboose = '$$$$'; 
	
	private $P; // stores the Pagination object.
	private $pagination_links = ''; // stores the html for the pagination links (if any).

	private $page;
	
	
	// Goes to true if orderby is set to a value not in the $wp_posts_columns array
	private $sort_by_meta_flag = false;
		
	// Set in the controller. If set to true, some helpful debugging msgs are printed.
	public $debug = false; 

	// Stores the number of results available (used only when paginate is set to true)
	public $found_rows = null; 
	
	// See http://codex.wordpress.org/Function_Reference/wpdb_Class
	private $output_type;	// ARRAY_A, OBJECT
		
	// Contains all arguments listed in the $defaults, with any modifications passed by the user
	// at the time of instantiation.
	public $args = array();
	
	public $registered_post_types = array();
	public $registered_taxonomies = array();
	
	// Stores any errors encountered
	public $errors = array();
	
	// Added by the set_default() function: sets default values to use for empty fields.
	public $default_values_empty_fields = array();
	
	// Some functions need to know which columns exist in the wp_posts, e.g.
	// the orderby parameter can only sort by columns in this table. 
	private $wp_posts_columns = array(
		'ID',
		'post_author',
		'post_date',
		'post_date_gmt',
		'post_content',
		'post_title',
		'post_excerpt',
		'post_status',
		'comment_status',
		'ping_status',
		'post_password',
		'post_name',
		'to_ping',
		'pinged',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'post_parent',
		'guid',
		'menu_order',
		'post_type',
		'post_mime_type',
		'comment_count'	
	);
	
	// For date searches (greater than, less than)
	private $date_cols = array('post_date','post_date_gmt','post_modified','post_modified_gmt');
	
	//! Defaults
	// args and defaults for get_posts()
	public static $defaults = array(
		'limit'			=> 0, 
		'offset' 		=> null,  
		'orderby'		=> 'ID', // valid column (?) cannot be a metadata column
		'order'			=> 'DESC', // ASC or DESC
		// include: comma-sparated string or array of IDs. Any posts you want to include. This shrinks the "pool" of resources available: all other search parameters will only search against the IDs listed, so this paramters is probably best suited to be used by itself alone. If you want to always return a list of IDs in addition to results returned by other search parameters, use the "append" parameter instead.
		'include'		=> '', 
		'exclude'		=> '', // comma-sparated string or array of IDs. Any posts you want to include.
		'append'		=> '', // comma-sparated string or array of IDs. Any posts you always want to include *in addition* to any search criteria. (This uses the 'OR' criteria)
		
		// used to search custom fields
		'meta_key'		=> '', 
		'meta_value'	=> '',    
		
		// Direct searches (mostly by direct column matches)
		'post_type'		=> '',	// comma-sparated string or array
		'omit_post_type'	=> 'revision', // comma-sparated string or array
		'post_mime_type' => '', // comma-sparated string or array
		'post_parent'	=> '',	// comma-sparated string or array 
		'post_status' 	=> 'publish',	// comma-sparated string or array
		'post_title'	=> '', // for exact match
		'author'		=> '', // search by author's display name
		'post_date'		=> '', // matches YYYY-MM-DD.
		'post_modified'	=> '', // matches YYYY-MM-DD.
		'yearmonth'		=> '', // yyyymm
		
		// Date searches: set the date_column to change the column used to filter the dates.
		'date_min'		=> '', 	// YYYY-MM-DD (optionally include the time)
		'date_max'		=> '',	// YYYY-MM-DD (optionally include the time)
		
		// Search by Taxonomies
		'taxonomy'		=> null, 	// category, post_tag (tag), or any custom taxonomy
		'taxonomy_term'	=> null,	// comma-separated string or array
		'taxonomy_slug'	=> null,	// comma-separated string or array 
		
		// uses LIKE %matching%
		'search_term'	=> '', // Don't use this with the above search stuff 
		'search_columns' => 'post_title, post_content', // comma-sparated string or array or more one of the following columns; if not one of the post columns, this will search the meta columns.
		
		// Global complicated stuff
		'join_rule'		=> 'AND', // AND | OR. You can set this to OR if you really know what you're doing. Defines how the WHERE criteria are joined.
		'match_rule'	=> 'contains', // contains|starts_with|ends_with corresponding to '%search_term%', 'search_term%', '%search_term'
		'date_column'	=> 'post_modified', // which date column to use for date searches: post_date, post_date_gmt, post_modified, post_modified_gmt
		
		'paginate'		=> false, // limit will become the 'results_per_page'
		
		
	);

	public $cnt; // number of search results
	public $SQL; // store the query here for debugging.

	//------------------------------------------------------------------------------
	/**
	* Dynamically handle getting of custom post types.
	*/
	public function __call($name, $args)
	{
	
	}
		
	//------------------------------------------------------------------------------
	/**
	* Read input arguments into the global parameters. Relies on the WP shortcode_atts()
	* function to "listen" for and filter a predefined set of inputs.
	*/
	public function __construct($raw_args=array())
	{
		$this->registered_post_types = array_keys( get_post_types() );
		$this->registered_taxonomies = array_keys( get_taxonomies() );

		$this->output_type = SummarizePosts::$options['output_type'];
		
		$tmp = shortcode_atts( self::$defaults, $raw_args );
		// Run these through the filters in __set()
		foreach ( $tmp as $k => $v )
		{
			$this->__set($k, $v);
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	* 
	*/
	public function __get($var)
	{
		if ( in_array($var, $this->args) )
		{
			return $this->args[$var];
		}
		else
		{
			return __('Invalid parameter:') . $var;
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	* 
	*/
	public function __toString() 
	{
		return sprintf(
			'<div class="summarize-posts-summary">
				<h1>Summarize Posts</h1>
				
				<h2>%s</h2>
					<div class="summarize-post-arguments">%s</div>

				<h2>%s</h2>
					<div class="summarize-post-output_type">%s</div>
					
				<h2>%s</h2>
					<div class="summarize-posts-query"><textarea rows="20" cols="80">%s</textarea></div>
					
				<h2>%s</h2>
					<div class="summarize-posts-errors">%s</div>
				
				<h2>%s</h2>
					<div class="summarize-posts-shortcode"><textarea rows="3" cols="80">%s</textarea></div>
					
				<h2>%s</h2>
					<div class="summarize-posts-results"><textarea rows="20" cols="80">%s</textarea></div>
			</div>'
			, __('Arguments')
			, $this->format_args()
			, __('Output Type')
			, $this->output_type
			, __('Raw Database Query')
			, $this->SQL
			, __('Errors')
			, $this->format_errors()
			, __('Comparable Shorcode')
			, $this->get_comparable_shortcode()
			, __('Results')
			, print_r( $this->get_posts(), true)
		);
	}

	//------------------------------------------------------------------------------
	/**
	* Validate and set parameters
	*/
	public function __set($var, $val)
	{
		$var = strtolower($var);

		if ( in_array($var, array_keys(self::$defaults) ) )
		{
			switch ( $var )
			{
				// Integers
				case 'limit':
				case 'offset':
				case 'yearmonth':
					$this->args[$var] = (int) $val;
					break;
				// ASC or DESC
				case 'order':

					$val = strtoupper($val);
					if ( $val == 'ASC' || $val == 'DESC' )
					{
						$this->args[$var] = $val;
					}
					break;
				case 'orderby':
					if ( !in_array( $val, $this->wp_posts_columns) )
					{
						$this->sort_by_meta_flag = true;
						$this->args[$var] = $val;
						$this->errors[] = __('Possible error: orderby column not a default post column: ') . $val;
					}
					else
					{
						$this->args[$var] = $val;
					}
					break;
				// List of Integers
				case 'include':
				case 'exclude':
				case 'append':
				case 'post_parent':
					$this->args[$var] = $this->_comma_separated_to_array($val,'integer');
					break;
				// Dates
				case 'post_modified':
				case 'post_date':
					// if it's a date
					if ( !empty($val) && !$this->_is_date($val) )
					{
						$this->errors[] = sprintf( __('Invalid date argument: %s'), $var.':'.$val );
					}
					else
					{
						$this->args[$var] = $val;
					}				
					break;
				// Datetimes
				case 'date_min':
				case 'date_max':
					// if is a datetime
					if ( !empty($val) && !$this->_is_datetime($val) )
					{
						$this->errors[] = sprintf( __('Invalid datetime argument: %s'), $var.':'.$val );
					}
					else
					{
						$this->args[$var] = $val;
					}
					break;
				// Post Types
				case 'post_type':
				case 'omit_post_type':
					$this->args[$var] = $this->_comma_separated_to_array($val,'post_type');
					break;
				// Post Status
				case 'post_status':
					$this->args[$var] = $this->_comma_separated_to_array($val,'post_status');
					break;
					
				// Almost any value... prob. should use $wpdb->prepare( $query, $mime_type.'%' )
				case 'meta_key':
				case 'meta_value':
				case 'post_title':
				case 'author':
				case 'search_term':
					$this->args[$var] = $val;
					break;
					
				// Taxonomies
				case 'taxonomy':
					if ( taxonomy_exists($val) )
					{
						$this->args[$var] = $val;
					}
					else 
					{
						$this->args[$var] = null;
					}
					break;
				// The category_description() function adds <p> tags to the value.
				case 'taxonomy_term':
					$this->args[$var] = $this->_comma_separated_to_array($val,'no_tags');
					break;
				case 'taxonomy_slug':
					$this->args[$var] = $this->_comma_separated_to_array($val,'alpha');
					// print $val;
					
					//print_r($this->_comma_separated_to_array($val,'alpha')); exit;
					break;
				case 'search_columns':
					$this->args[$var] = $this->_comma_separated_to_array($val,'search_columns');
					break;
				
				// And or Or
				case 'join_rule':
					if ( in_array($val, array('AND', 'OR')) )
					{
						$this->args[$var] = $val;
					}
					else
					{
						$this->errors[] = __('Invalid parameter for join_rule.');
					}
					break;
				// match rule...
				case 'match_rule':
					if ( in_array($val, array('contains','starts_with','ends_with')) )
					{
						$this->args[$var] = $val;
					}
					else
					{
						$this->errors[] = __('Invalid parameter for match_rule.');
					}
					break;
				case 'date_column':
					// ??? how to do searches for a custom date field
					if ( in_array($val, $this->date_cols) )
					{
						$this->args[$var] = $val;
					}
					else
					{
						$this->errors[] = __('Invalid date column.');
					}
					
					break;
				case 'paginate':
					$this->args[$var] = (bool) $val;
					break;
				default:
					$this->args[$var] = $val;
			}
			
		}
		else
		{
			$this->errors[] = __('Invalid input parameter:') . $var;
		}
    }
    
    
	//! Private Functions
	
	//------------------------------------------------------------------------------
	/**
	* Takes a comma separated string and turns it to an array, or passes the array
	*
	* @param	mixed	$input is either a comma-separated string or an array
	* @param	string	$type describing the type of input: 'integer','alpha',
	* @return	array
	*/
	private function _comma_separated_to_array($input, $type)
	{
		$output = array();
		if ( empty($input) )
		{
			return $output;
		}
		if ( is_array($input) )
		{
			$output = $input;

		}
		else
		{
			$output = explode(',',$input);			
		}

		foreach ($output as $i => $item)
		{
			$output[$i] = trim($item);
			switch ($type)
			{
				case 'integer':
					$output[$i] = (int) $item;
					break;
				// Only a-z, _, - is allowed.
				case 'alpha':
					if ( !preg_match('/[a-z_\-]/i', $item) )
					{
						$this->errors[] = __('Invalid alpha input:') . $item;
					}
					break;
				case 'post_type':
					if ( !post_type_exists($item) )
					{
						$this->errors[] = __('Invalid post_type:') . $item . ' '. print_r($this->registered_post_types, true);
						
					}
					break;
				case 'post_status':
					if ( !in_array($item, array('inherit','publish','auto-draft')) )
					{
						$this->errors[] = __('Invalid post_status:') . $item;
					}
					break;
				case 'search_columns':
					if ( !in_array($item, $this->wp_posts_columns ) )
					{
						$this->errors[] = __('Invalid search_column:') . $item;
					}
					break;
				case 'no_tags':
					$output[$i] = strip_tags($item);
			}
		}
		
		return $output;
	}
	//------------------------------------------------------------------------------
	/**
	OUTPUT: integer: the number of results for this particular query
	Must have included the SQL_CALC_FOUND_ROWS option in the query. This is done if
	the paginate option is set to true.
	*/
	private function _count_posts()
	{
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT FOUND_ROWS() as cnt', OBJECT );
		return $results[0]->cnt;
	}	
	//------------------------------------------------------------------------------
	/**
	Ensure a valid date. 0000-00-00 or '' qualify as valid; if you need to ensure a REAL
	date (i.e. where '0000-00-00' is not allowed), then simply marking the field required
	won't work because the string '0000-00-00' is not empty.  To require a REAL date, use
	the following syntax in your definitions:
	
	'mydatefield' => 'date["YYYY-MM-DD","required"]
	
	(Any TRUE value for the 2nd argument will force the date to be a real, non-null date)
	
	@param	string	date to be checked
	@return	boolean whether or not the input is a valid date
	*/
	private function _is_date( $date )
    {
		list( $y, $d, $m ) = explode('-', $date );
		
		if ( is_numeric($m) && is_numeric($d) && is_numeric($y) && checkdate( $m, $d, $y ) )
        {
        	return true;
        }
        else
        {
        	return false;
        }
    }
	
	//------------------------------------------------------------------------------
	/**
	* Is a datetime in MySQL YYYY-MM-DD HH:MM:SS date format?  (Time is optional). 
	* @param	string	
	* @return	boolean
	*/
	private function _is_datetime( $datetime, $raw_args=null)
	{
		list ($date, $time) = explode(' ', $datetime);
		
		//print $date . '<<<<'; exit;
		if ( !$this->_is_date($date) )
		{
			return false;
		}
		elseif ( empty($time) )
		{
			return true; 
		}
		
		$time_format = 'H:i:s';
		$unixtime = strtotime($time);
		$converted_time =  date($time_format, $unixtime);
		//print $converted_time . '<-----------'; exit;
		if ( $converted_time != $time )
		{
			 return false;		
		}
		
		return true;

	}	
	
	//! SQL
	/*------------------------------------------------------------------------------	
	This is the main SQL query constructor. Home rolled...
	It's meant to be called by the various querying functions:
		get_posts()
		count_posts()
		query_distinct_yearmonth()
	
	INPUT:
		$select
		$limit
		$use_offset
		
	
	OUTPUT: 
	A set of results.
	Options: 
		$mime_type
		$searchterm
		$limit
		$offset
	
	You can't use the WP query_posts() function here because the global $wp_the_query
	isn't defined yet.  get_posts() works, however, but its format is kinda whack.  
	Jeezus H. Christ. Crufty ill-defined API functions.
	http://shibashake.com/wordpress-theme/wordpress-query_posts-and-get_posts



SELECT 
wp_posts.*
, parent.ID as 'parent_ID'
, parent.post_title as 'parent_post_title'
, author.display_name as 'author'
, thumbnail.ID as 'thumbnail_id'
, thumbnail.post_content as 'thumbnail_src'
, metatable.metadata

FROM wp_posts wp_posts
LEFT JOIN wp_posts parent ON wp_posts.ID=parent.post_parent
LEFT JOIN wp_users author ON wp_posts.post_author=author.ID
LEFT JOIN wp_term_relationships ON wp_posts.ID=wp_term_relationships.object_id 
LEFT JOIN wp_term_taxonomy ON wp_term_taxonomy.term_taxonomy_id=wp_term_relationships.term_taxonomy_id
LEFT JOIN wp_terms ON wp_terms.term_id=wp_term_taxonomy.term_id
LEFT JOIN wp_postmeta thumb_join ON wp_posts.ID=thumb_join.post_id
	AND thumb_join.meta_key='_thumbnail_id'
LEFT JOIN wp_posts thumbnail ON thumbnail.ID=thumb_join.meta_key
LEFT JOIN wp_postmeta ON wp_posts.ID=wp_postmeta.post_id
-- a big mess of metadata here:
LEFT JOIN
(
	SELECT
	wp_postmeta.post_id, 
	GROUP_CONCAT( CONCAT(wp_postmeta.meta_key,'<!--COLON-->', wp_postmeta.meta_value) SEPARATOR '<!--COMMA-->') as metadata
	FROM wp_postmeta
	GROUP BY wp_postmeta.post_id
) metatable ON wp_posts.ID=metatable.post_id
WHERE
1
AND wp_posts.post_type != 'revision'

-- AND wp_posts.post_title = 'About'
-- AND DATE_FORMAT(wp_posts.post_modified, '%Y%m') = '201102'
-- AND wp_postmeta.meta_key = 'yarn'
-- AND wp_postmeta.meta_value = 'nada'
-- AND author.display_name = 'fireproofsocks'
-- AND DATE(wp_posts.post_date) = '2010-11-13'
-- AND DATE(wp_posts.post_modified) = '2010-11-13'
-- AND wp_term_taxonomy.taxonomy='post_tag'
-- AND wp_terms.name='star wars'
-- AND wp_terms.slug='uncategorized'
AND (
	wp_posts.post_title LIKE '%elcom%'
	OR
	wp_posts.post_content LIKE '%elcom%'
	OR 
	wp_postmeta.meta_value LIKE '%elcom%'
)

AND wp_posts.post_modified >= '2010-11-13'
AND wp_posts.post_modified <= '2011-02-15'
GROUP BY wp_posts.ID
ORDER BY wp_posts.ID DESC
LIMIT 10 
OFFSET 0
	
	------------------------------------------------------------------------------*/
	private function _get_sql()
	{
		global $wpdb; 

		$this->SQL = 
			"SELECT 			
			[+select+]
			{$wpdb->posts}.*
			, parent.ID as 'parent_ID'
			, parent.post_title as 'parent_title'
			, parent.post_excerpt as 'parent_excerpt'
			, author.display_name as 'author'
			, thumbnail.ID as 'thumbnail_id'
			, thumbnail.post_content as 'thumbnail_src'
			, metatable.metadata
			
			[+select_metasortcolumn+]
			
			FROM {$wpdb->posts}
			LEFT JOIN {$wpdb->posts} parent ON {$wpdb->posts}.post_parent=parent.ID
			LEFT JOIN {$wpdb->users} author ON {$wpdb->posts}.post_author=author.ID
			LEFT JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id 
			LEFT JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id={$wpdb->term_relationships}.term_taxonomy_id
			LEFT JOIN {$wpdb->terms} ON {$wpdb->terms}.term_id={$wpdb->term_taxonomy}.term_id
			LEFT JOIN {$wpdb->postmeta} thumb_join ON {$wpdb->posts}.ID=thumb_join.post_id
				AND thumb_join.meta_key='_thumbnail_id'
			LEFT JOIN {$wpdb->posts} thumbnail ON thumbnail.ID=thumb_join.meta_key
			LEFT JOIN {$wpdb->postmeta} ON wp_posts.ID={$wpdb->postmeta}.post_id
			LEFT JOIN
			(
				SELECT
				{$wpdb->postmeta}.post_id, 
				CONCAT( GROUP_CONCAT( CONCAT({$wpdb->postmeta}.meta_key,'[+colon_separator+]', {$wpdb->postmeta}.meta_value) SEPARATOR '[+comma_separator+]'), '[+caboose+]') as metadata
				FROM {$wpdb->postmeta}
				WHERE {$wpdb->postmeta}.meta_key NOT LIKE '\_%'
				GROUP BY {$wpdb->postmeta}.post_id
			) metatable ON {$wpdb->posts}.ID=metatable.post_id
			
			[+join_for_metasortcolumn+]
			
			WHERE
			(
			1
			[+include+]
			[+exclude+]
			[+omit_post_type+]
			[+post_type+]
			[+post_mime_type+]
			[+post_parent+]
			[+post_status+]
			[+yearmonth+]
			[+meta+]
			[+author+]
			
			
			[+taxonomy+]
			[+taxonomy_term+]
			[+taxonomy_slug+]
			
			[+search+]	
			
			[+date_min+]
			[+date_max+]
			)
			[+append+]
			
			GROUP BY {$wpdb->posts}.ID
			ORDER BY [+orderby+] [+order+]
			[+limit+]
			[+offset+]";
		
		// Substitute into the query.
		$hash = array();
		$hash['select'] = ($this->args['paginate'])? 'SQL_CALC_FOUND_ROWS' : '';
		$hash['colon_separator'] = self::colon_separator;
		$hash['comma_separator'] = self::comma_separator;
		$hash['caboose']		= self::caboose;
		
		$hash['include'] = $this->_sql_filter($wpdb->posts,'ID','IN', $this->args['include']);
		$hash['exclude'] = $this->_sql_filter($wpdb->posts,'ID','NOT IN', $this->args['exclude']);
		$hash['append'] = $this->_sql_append($wpdb->posts);
		
		$hash['omit_post_type'] = $this->_sql_filter($wpdb->posts,'post_type','NOT IN', $this->args['omit_post_type']);
		$hash['post_type'] = $this->_sql_filter($wpdb->posts,'post_type','IN', $this->args['post_type']);
		$hash['post_mime_type'] = $this->_sql_filter_post_mime_type();
		$hash['post_parent'] = $this->_sql_filter($wpdb->posts,'post_parent','IN', $this->args['post_parent']); 
		$hash['post_status'] = $this->_sql_filter($wpdb->posts,'post_status','IN', $this->args['post_status']);
		$hash['yearmonth'] = $this->_sql_yearmonth();
		$hash['meta'] = $this->_sql_meta();
		$hash['author'] = $this->_sql_filter('author','display_name','=', $this->args['author']);
			
		$hash['taxonomy'] = $this->_sql_filter($wpdb->term_taxonomy,'taxonomy','=', $this->args['taxonomy']);
		$hash['taxonomy_term'] = $this->_sql_filter($wpdb->terms,'name','IN', $this->args['taxonomy_term']);
		$hash['taxonomy_slug'] = $this->_sql_filter($wpdb->terms,'slug','IN', $this->args['taxonomy_slug']);
			
		$hash['date_min'] = $this->_sql_filter($wpdb->posts, $this->date_column,'>=', $this->args['date_min']);
		$hash['date_max'] = $this->_sql_filter($wpdb->posts, $this->date_column,'<=', $this->args['date_max']);
			
		$hash['search'] = $this->_sql_search();
		
		// Custom handling for sorting on custom fields
		if ($this->sort_by_meta_flag)
		{
			$hash['orderby'] = 'metasortcolumn';
			$hash['select_metasortcolumn'] = ', orderbymeta.meta_value as metasortcolumn';
			$hash['join_for_metasortcolumn'] = sprintf('LEFT JOIN wp_postmeta orderbymeta ON %s.ID=orderbymeta.post_id AND orderbymeta.meta_key = %s'
				, $wpdb->posts
				, $wpdb->prepare('%s', $this->args['orderby'])
			);
		}
		// Standard: sort by a column in wp_posts
		else
		{
			$hash['orderby'] = $wpdb->posts.'.'.$this->args['orderby']; 
			$hash['select_metasortcolumn'] = '';
			$hash['join_for_metasortcolumn'] = '';
		}	
		
		$hash['order'] = $this->args['order'];
		$hash['limit'] = $this->_sql_limit();
		$hash['offset'] = $this->_sql_offset();
		
		
		$this->SQL = self::parse($this->SQL, $hash);
		// Strip whitespace
		$this->SQL  = preg_replace('/\s\s+/', ' ', $this->SQL );
		return $this->SQL;
		// $results = $wpdb->get_results( $this->SQL, ARRAY_A );
		//return $results;
	}

	//------------------------------------------------------------------------------
	/**
	* This kicks in when pagination is used. It allows $_GET parameters to override 
	* normal args when pagination is used.
	*/
	private function _override_args_with_url_params()
	{
		if ( $this->args['paginate'])
		{
			if ( isset($_GET['page']))
			{
				$this->page = (int) $_GET['page'];
			}
			
			foreach ( $this->args as $k => $v )
			{
				if ( isset($_GET[$k]) )
				{
					$this->__set($k, $_GET[$k]);
				}
			}
		}
	}
	
	//------------------------------------------------------------------------------
	/**
	* _sql_append: always include the IDs listed.
	*/
	private function _sql_append($table)
	{
		if ($this->args['append'])
		{
			return "OR $table.ID IN ({$this->args['append']})";
		}
	}
	

	
	//------------------------------------------------------------------------------
	/**
	* SQL filter to handle multiple filters.
	* 
	* @param	string	$table name (verbatim, including any prefixes)
	* @param	string	$column name
	* @param	string	$operation logical operator, e.g. '=' or 'NOT IN'
	* @param	string	$value being filtered for.
	*/
	private function _sql_filter($table, $column, $operation, $value)
	{
		global $wpdb;
		
		if ( empty($value) )
		{
			return '';
		}
		
		if ( is_array($value) )
		{
			foreach ($value as &$v)
			{
				$v = $wpdb->prepare('%s', $v);
			}
			
			$value = '('. implode(',',$value) . ')';
		}
		else
		{
			$value = $wpdb->prepare('%s', $value);
		}
		
		return sprintf("%s %s.%s %s %s"
			, $this->args['join_rule']
			, $table
			, $column
			, $operation
			, $value
		);
	}

	/*------------------------------------------------------------------------------
	OUTPUT: string to be used in *the* main SQL query's LIMIT/OFFSET clause
	$limit should be passed in as $this->results_per_page; (like when you're selecting
	rows) or as zero (like when you're counting rows).
	------------------------------------------------------------------------------*/
	private function _sql_limit()
	{		
		if ( $this->args['limit'] )
		{
			return ' LIMIT ' . $this->args['limit'];
		}
		else
		{
			return '';
		}
	}


	/*------------------------------------------------------------------------------
	OUTPUT: string to be used in *the* main SQL query's LIMIT/OFFSET clause
	------------------------------------------------------------------------------*/
	private function _sql_offset()
	{
		if ( $this->args['limit'] && $this->args['offset'] )
		{
			return ' OFFSET '. $this->args['offset'];
		}
		else
		{
			return '';
		}
	}
	
	/*------------------------------------------------------------------------------
	OUTPUT: string to be used in *the* main SQL query's WHERE clause.
	Construct the part of the query for searching by mime type
	------------------------------------------------------------------------------*/
	private function _sql_filter_post_mime_type()
	{
		global $wpdb;
		if ( $this->args['post_mime_type'])
		{
			$query = " AND {$wpdb->posts}.post_mime_type LIKE %s";
			return $wpdb->prepare( $query, $this->args['post_mime_type'].'%' );			
		}
		else
		{
			return '';
		}
	}
	
	/*------------------------------------------------------------------------------
	OUTPUT: string to be used in *the* main SQL query's WHERE clause.
	Construct the part of the query for searching by name.
	
				AND (
				wp_posts.post_title LIKE '%elcom%'
				OR
				wp_posts.post_content LIKE '%elcom%'
				OR 
				wp_postmeta.meta_value LIKE '%elcom%'
			)
			
	$this->wp_posts_columns
	contains|starts_with|ends_with
	------------------------------------------------------------------------------*/
	private function _sql_search()
	{
		global $wpdb;
		
		if (empty($this->args['search_term']))
		{
			return '';
		}
		
		$criteria = array();
		foreach ( $this->args['search_columns'] as $c )
		{
			switch ($this->args['match_rule'])
			{
				case 'contains':
					$criteria[] = $wpdb->prepare("{$wpdb->posts}.$c LIKE %s", '%'.$this->args['search_term'].'%');
					break;
				case 'starts_with':
					$criteria[] = $wpdb->prepare("{$wpdb->posts}.$c LIKE %s", '%'.$this->args['search_term']);
					break;
				case 'ends_with':
					$criteria[] = $wpdb->prepare("{$wpdb->posts}.$c LIKE %s", $this->args['search_term'].'%');
					break;
			}
		}
		
		$query = implode(' OR ', $criteria);
		$query = $this->args['join_rule'] . " ($query)";
		return $query;
	}	


	
	/*------------------------------------------------------------------------------
	OUTPUT: string to be used in *the* main SQL query.  This function is called 
	in distinction to the _sql_select_columns() when the purpose of the query is
	to count available rows (e.g. for paginating results).
	------------------------------------------------------------------------------*/
	private function _sql_select_count()
	{
		return ' COUNT(*) as cnt';
	}
	
	/*------------------------------------------------------------------------------
	Which columns do we normally return? 
	OUTPUT: string to be used in *the* main SQL query: this string defines which
	columns we will select.
	------------------------------------------------------------------------------*/
	private function _sql_select_columns()
	{
		global $wpdb;
		
		return " {$wpdb->posts}.ID as 'post_id', 
			{$wpdb->posts}.guid as 'original_post_url',
			{$wpdb->posts}.*";
	}
	
	
	/*------------------------------------------------------------------------------
	OUTPUT: string to be used in *the* main SQL query.  This function is called 
	in distinction to the _sql_select_columns() when the purpose of the query is
	to return distinct year-months of posts for the purposes of offering the user
	simple date-based groups of posts. 
	
	SELECT DISTINCT DATE_FORMAT(post_modified, '%Y%m') FROM wp_posts;
	http://dev.mysql.com/doc/refman/5.1/en/date-and-time-functions.html#function_date-format
	------------------------------------------------------------------------------*/
	private function _sql_yearmonth()
	{	
		global $wpdb;
		if ( !$this->args['yearmonth'] )
		{
			return '';
		}
		// AND DATE_FORMAT(wp_posts.post_modified, '%Y%m') = '201102'
		return sprintf("%s DATE_FORMAT(%s.%s, '%Y%m') = %s"
			, $this->args['join_rule']
			, $wpdb->posts
			, $this->args['date_column']
			, $wpdb->prepare('%s', $this->args['yearmonth']) 
		);
	}
	
	//------------------------------------------------------------------------------
	/**
	* 
		AND wp_postmeta.meta_key = 'yarn'
		AND wp_postmeta.meta_value = 'nada'

	*/
	private function _sql_meta()
	{
		global $wpdb;

		if ( $this->args['meta_key'] && $this->args['meta_value'])
		{
			return sprintf("%s (%s.meta_key=%s AND %s.meta_value=%s)"
				, $this->args['join_rule']
				, $wpdb->postmeta
				, $wpdb->prepare('%s', $this->args['meta_key']) 
				, $wpdb->postmeta
				, $wpdb->prepare('%s', $this->args['meta_value']) 
			);		
		}
		elseif ($this->args['meta_key']) 
		{
			return $this->_sql_filter($wpdb->postmeta,'meta_key','=', $this->args['meta_key']);
		}
		else
		{
			return $this->_sql_filter($wpdb->postmeta,'meta_value','=', $this->args['meta_value']);
		}
	}

	//------------------------------------------------------------------------------
	/**
	* See http://codex.wordpress.org/Function_Reference/wp_insert_post
	*/
	private function _log_post($post)
	{
		$my_post = array(
			'post_title' => 'My post',
			'post_content' => 'This is my post.',
			'post_status' => 'draft',
			'post_type'	=> 'summarize_post_log',
			'post_author' => 1,
		);
		
		// Insert the post into the database
		wp_insert_post( $my_post );
	}
	
	


	//! Public Functions

	//------------------------------------------------------------------------------
	/**
	* Prints a formatted version of filtered input arguments. 
	*/
	public function format_args()
	{
		$output = '<ul class="summarize-posts-argument-list">'."\n";
		#print_r($this->args); exit;
		foreach ($this->args as $k => $v)
		{
			if ( is_array($v) && !empty($v) )
			{
				$output .= '<li class="summarize-posts-arg"><strong>'.$k.'</strong>: Array 
				('.implode(', ', $v).')</li>'."\n";		
			}
			else
			{
				if ( $v === false )
				{
					$v = 'false';
				}
				elseif ( $v === true )
				{
					$v = 'true';
				}
				elseif ( empty($v) )
				{
					$v = '--';
				}
				$output .= '<li class="summarize-posts-arg"><strong>'.$k.'</strong>: '.$v.'</li>'."\n";	
			}
		}
		$output .= '</ul>'."\n";
		return $output;
	}
	
	
	//------------------------------------------------------------------------------
	/**
	* Format any errors in an unordered list, or returns a message saying there were no errors.
	*/
	public function format_errors()
	{

		if ($this->errors)
		{
			$output = '';
			$items = '';
			foreach ($this->errors as $e)
			{
				$items .= '<li>'.$e.'</li>' ."\n";
			}
			$output = '<ul>'."\n".$items.'</ul>'."\n";
			return $output;
		}
		else
		{
			return __('There were no errors.');
		}	
		
	}

	//------------------------------------------------------------------------------
	/**
	* Returns a string of a comparable shortcode for the query entered.
	*/
	public function get_comparable_shortcode()
	{
		$args = array();
		foreach ($this->args as $k => $v)
		{
			if ( !empty($v) )
			{
				if ( is_array($v) )
				{
					$args[] = $k.'="'.implode(',',$v).'"';
				}
				else
				{
					$args[] = $k.'="'.$v.'"';
				}
			}
		}
		$args = implode(' ', $args);
		if (!empty($args)) {
			$args = ' '.$args;
		}
		return '[summarize-posts'.$args.']';
	}
	
	//------------------------------------------------------------------------------
	/**
	* http://www.webcheatsheet.com/PHP/get_current_page_url.php
	* This uses wp_kses() to reduce risk of some a-hole 
	*/
	static function get_current_page_url() 
	{
		//print_r($_SERVER); exit;
		if ( isset($_SERVER['REQUEST_URI']) ) 
		{
			$_SERVER['REQUEST_URI'] = preg_replace('/&?offset=[0-9]*/','', $_SERVER['REQUEST_URI']);
		}
		return wp_kses($_SERVER['REQUEST_URI'], '');
	}
	
	//------------------------------------------------------------------------------
	/**
	* Only valid if the pagination option has been set.  This is how the user should
	* retrieve the pagination links that have been generated.
	*/
	public function get_pagination_links()
	{
		return $this->pagination_links;
	}

	/*------------------------------------------------------------------------------
	This is the main event here (i.e. function).
		
	------------------------------------------------------------------------------*/
	public function get_posts($args=array())
	{
		global $wpdb;
		
		$tmp = shortcode_atts( $this->args, $args );

		foreach ( $tmp as $k => $v )
		{
			$this->__set($k, $v);
		}

		$this->_override_args_with_url_params(); // only kicks in when pagination is active

		// ARRAY_A or OBJECT
		$results = $wpdb->get_results( $this->_get_sql(), $this->output_type );
		if ( $this->args['paginate'] )
		{
			$this->found_rows = $this->_count_posts();
			# $this->_override_args_with_url_params();
			include_once('PostPagination.conf.php');
			include_once('PostPagination.php');
			$this->P = new PostPagination();
			$this->P->set_base_url( self::get_current_page_url() );
			$this->P->set_offset($this->args['offset']); //
			$this->P->set_results_per_page($this->args['limit']);  // You can optionally expose this to the user.
			$this->pagination_links = $this->P->paginate($this->found_rows); // 100 is the count of records
		}
		
		foreach ($results as &$r)
		{
			// OBJECT
			if ( $this->output_type == OBJECT )
			{

				if ( !empty($r->metadata) )
				{
					// Manually grab the data
					if ( SummarizePosts::$manually_select_postmeta )
					{
						$r = SummarizePosts::get_post_complete($r->ID);
					}
					// Parse out the metadata, concat'd by MySQL
					else
					{
						$caboose = preg_quote(self::caboose);
						$count = 0;
						$r->metadata = preg_replace("/$caboose$/", '', $r->metadata, -1, $count );
						if (!$count)
						{
							$this->errors[] = __('There was a problem accessing custom fields. Try increasing the group_concat_max_len setting in the Summarize-Posts settings page.');
						}
						else
						{
							$pairs = explode( self::comma_separator, $r->metadata );
							foreach ($pairs as $p)
							{
								list($key, $value) = explode(self::colon_separator, $p);
								$r->$key = $value;
							}
						}
					}
				}
				
				unset($r->metadata);
				
				$r->permalink		= get_permalink( $r->ID );
				$r->parent_permalink	= get_permalink( $r->parent_ID );
				// $r->the_content 	= get_the_content(); // only works inside the !@#%! loop
				$r->content 		= $r->post_content;
				//$r['the_author']	->= get_the_author(); // only works inside the !@#%! loop
				$r->title 			= $r->post_title;
				$r->date			= $r->post_date;
				$r->excerpt			= $r->post_excerpt;
				$r->mime_type 		= $r->post_mime_type;
				$r->modified		= $r->post_modified;
				$r->parent			= $r->post_parent;
				$r->modified_gmt	= $r->post_modified_gmt;
			}

			// ARRAY_A
			else
			{
				if ( !empty($r['metadata']) )
				{
					// Manually grab the data
					if ( SummarizePosts::$manually_select_postmeta )
					{
						$r = SummarizePosts::get_post_complete($r['ID']);	
					}
					// Parse out the metadata, concat'd by MySQL
					else
					{
						$caboose = preg_quote(self::caboose);
						$count = 0;
						$r['metadata'] = preg_replace("/$caboose$/", '', $r['metadata'], -1, $count );
						if (!$count)
						{
							$this->errors[] = __('There was a problem accessing custom fields. Try increasing the group_concat_max_len setting.');
						}
						else
						{
					
							$pairs = explode( self::comma_separator, $r['metadata'] );
							foreach ($pairs as $p)
							{
								list($key, $value) = explode(self::colon_separator, $p);
								$r[$key] = $value;
							}
						}
					}
				}
				
				unset($r['metadata']);
				
				$r['permalink']		= get_permalink( $r['ID'] );
				$r['parent_permalink']	= get_permalink( $r['parent_ID'] );
				// $r['the_content'] 	= get_the_content(); // only works inside the !@#%! loop
				$r['content'] 		= $r['post_content'];
				//$r['the_author']	= get_the_author(); // only works inside the !@#%! loop
				$r['title'] 		= $r['post_title'];
				$r['date']			= $r['post_date'];
				$r['excerpt']		= $r['post_excerpt'];
				$r['mime_type'] 	= $r['post_mime_type'];
				$r['modified']		= $r['post_modified'];
				$r['parent']		= $r['post_parent'];
				$r['modified_gmt']	= $r['post_modified_gmt'];
			}
		}

		return $results;

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
	* This sets a default value for any field.  This kicks in only if the field is empty.
	*/
	public function set_default($fieldname, $value)
	{
	
	}

	//------------------------------------------------------------------------------
	/**
	* 
	*/
	public function set_output_type($output_type)
	{
		if ( $output_type != OBJECT && $output_type != ARRAY_A )
		{
			$this->errors[] = __('Invalid output type');
		}
		else
		{
			$this->output_type = $output_type;
		}
	}
}
/*EOF*/