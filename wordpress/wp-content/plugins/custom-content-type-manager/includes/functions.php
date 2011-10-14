<?php
/**
These are functions in the main namespace, primarily reserved for use in 
theme files.

See http://code.google.com/p/wordpress-custom-content-type-manager/wiki/TemplateFunctions
for the official documentation.
*/

//------------------------------------------------------------------------------
/**
Scour the custom field definitions for any fields
of the type specified.  This is useful e.g. if you want to return all images 
attached to a post.

Must be used when there is an active post.

A $def looks something like this:
 Array
(
    [label] => Author
    [name] => author
    [description] => This is who wrote the book
    [type] => text
    [sort_param] => 
)

@param	string	$type is one of the defined field types , currently:
	'checkbox','dropdown','media','relation','text','textarea','wysiwyg'
@param	string	$prefix	string identifying the beginning of the name of each field.
@return	array	List of names for each field of the type specified. 
*/
function get_all_fields_of_type($type, $prefix='')
{
	global $post;

	$values = array();

	$data = get_option( CCTM::db_key );
	
	$post_type = $post->post_type;
	if ( !isset($data[$post_type]['custom_fields']) )
	{
		return  sprintf( __('No custom fields defined for the %1$s field.', CCTM_TXTDOMAIN), $fieldname );
	}
	
	foreach ( $data[$post_type]['custom_fields'] as $def )
	{
		if ($def['type'] == $type )
		{
			if ($prefix)
			{			
				if ( preg_match('/^'.$prefix.'/', $def['name']) )
				{
					$values[] = get_custom_field($def['name']);
				}
			}
			else
			{
				$values[] = get_custom_field($def['name']);
			}
		}		
	}
	
	return $values;

}

//------------------------------------------------------------------------------
/**
 * SYNOPSIS: Used inside theme files, e.g. single.php or single-my_post_type.php
 * where you need to print out the value of a specific custom field.
 * 
 * WordPress allows for multiple rows in wp_postmeta to share the same meta_key for 
 * a single post; the CCTM plugin expects all meta_key's for a given post_id to be 
 * unique.  To deal with the possibility that the user has created multiple custom 
 * fields that share the same name for a single post (e.g. created manually with the 
 * CCTM plugin disabled), this prints the 1st instance of the meta_key identified by 
 * $fieldname associated with the current post. See get_post_meta() for more details.
 *
 * See also 	
 * http://codex.wordpress.org/Function_Reference/get_post_custom_values
 *
 * @param	string the name of the custom field (exists in wp_postmeta).
 * 		Optionally this string can be in the format of 'fieldname:output_filter'
 * @param	mixed	can be used to specify additional arguments
 * @return	mixed	The contents of the custom field, processed through output filters
 */
function get_custom_field($raw_fieldname, $options=null)
{
	global $post;
	$options_array = func_get_args();
	
	// Extract any output filters.
	$input_array = explode(':',$raw_fieldname);	
	$fieldname = array_shift($input_array);
	
	// We need the custom field definition for 2 reasons:
	// 1. To find the default Output Filter
	// 2. To find any default value (if the field is not defined)
	if ( !isset(CCTM::$data['custom_field_defs'][$fieldname]) ) {
		// return get_post_meta($post->ID, $fieldname, true); // ???
		return sprintf( __('The %s field is not defined as a custom field.', CCTM_TXTDOMAIN), $fieldname ); // ! TODO: just return the fieldname?
	}
	
	
	// Get default output filter
	if (empty($input_array)){
		if (isset(CCTM::$data['custom_field_defs'][$fieldname]['output_filter']) 
			&& !empty(CCTM::$data['custom_field_defs'][$fieldname]['output_filter'])) {
			$input_array[] = CCTM::$data['custom_field_defs'][$fieldname]['output_filter'];
		}
	}
	// Raw value from the db
	$value = get_post_meta($post->ID, $fieldname, true);

	// Default value?
	if ( empty($value) && isset(CCTM::$data['custom_field_defs'][$fieldname]['default_value'])) {
		$value = CCTM::$data['custom_field_defs'][$fieldname]['default_value'];
	}

	// Pass thru Output Filters
	$i = 1; // <-- skip 0 b/c that's the $raw_fieldname in the $options_array
	foreach($input_array as $outputfilter) {

		if (isset($options_array[$i])) {
			$options = $options_array[$i];
		}
		else {
			$options = null;
		}
		
		$value = CCTM::filter($value, $outputfilter, $options);

		$i++;
	}

	return $value;	
}

//------------------------------------------------------------------------------
/**
* Gets info about a custom field's definition (i.e. the meta info about the
* field). Returns error messages if no data found.
*
* Sample usage: <?php print get_custom_field_meta('my_dropdown','label'); ?>
*
* @param	string	$fieldname	The name of the custom field
* @param	string	$item		The name of the definition item that you want
* @return	mixed	Usually a string, but some items are arrays (e.g. options)
*/
function get_custom_field_meta($fieldname, $item) {
	$data = get_option( CCTM::db_key, array() );
	
	if ( $data['custom_field_defs'][$fieldname] ) {
		return $data['custom_field_defs'][$fieldname];
	}
	else {
		return sprintf( __('Invalid field name: %s', CCTM_TXTDOMAIN), $fieldname );
	}
}

//------------------------------------------------------------------------------
/**
* Gets the custom image referenced by the custom field $fieldname. 
* Relies on the WordPress wp_get_attachment_image() function.
*
* @param	string	$fieldname name of the custom field
* @return	string	an HTML img element or empty string on failure.
*/
function get_custom_image($fieldname)
{
	$id = get_custom_field($fieldname);
	return wp_get_attachment_image($id, 'full');
}


//------------------------------------------------------------------------------
/**
Retrieves a complete post object, including all meta fields.
Note: get_post_custom() will treat each custom field as an array, because in WP
you can tie multiple rows of data to the same fieldname (which can cause some
architectural headaches).

At the end of this, I want a post object that can work like this:

print $post->post_title;
print $post->my_custom_field; // no $post->my_custom_fields[0];

and if the custom field *is* a list of items, then attach it as such.
@param	integer	$id is valid ID of a post (regardless of post_type).
@return	object	post object with all attributes, including custom fields.
*/
function get_post_complete($id)
{
	$complete_post = get_post($id, OBJECT);
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
		if ( count($value) == 1 )
		{
			$complete_post->$fieldname = $value[0];
		}
		else
		{
			$complete_post->$fieldname = $value[0];		
		}
	}
	
	return $complete_post;	
}

//------------------------------------------------------------------------------
/**
Returns an array of post "complete" objects (including all custom fields)
where the custom fieldname = $fieldname and the value of that field is $value.
This is used to find a bunch of related posts in the same way you would with 
a taxonomy, but this uses custom field values instead of taxonomical labels.

INPUT: 
	$fieldname (str) name of the custom field
	$value (str) the value that you are searching for.

OUTPUT:
	array of post objects (complete post objects, with all attributes).

USAGE:
	One example:
	$posts = get_posts_sharing_custom_field_value('genre', 'comedy');
	
	foreach ($posts as $p)
	{
		print $p->post_title;
	}

This is a hefty, db-intensive function... (bummer).
*/
function get_posts_sharing_custom_field_value($fieldname, $value)
{
	global $wpdb;
	$query = "SELECT DISTINCT {$wpdb->posts}.ID 
		FROM {$wpdb->posts} JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id  
		WHERE 
		{$wpdb->posts}.post_status = 'publish'
		AND {$wpdb->postmeta}.meta_key=%s AND {$wpdb->postmeta}.meta_value=%s";
	$results = $wpdb->get_results( $wpdb->prepare( $query, $fieldname, $value ), OBJECT );
	
	$completes = array();
	foreach ( $results as $p )
	{
		$completes[] = get_post_complete($p->ID);
	}
	return $completes;
}


//------------------------------------------------------------------------------
/**
A relation field stores a post ID, and that ID identifies another post.  So given 
a fieldname, this returns the complete post object for that was referenced by
the custom field.  You can see it's a wrapper function which relies on 
get_post_complete() and get_custom_field().
INPUT: 
	$fieldname (str) name of a custom field
OUTPUT:
	post object
*/
function get_relation($fieldname)
{
	return get_post_complete( get_custom_field($fieldname) );
}

//------------------------------------------------------------------------------
/**
Given a specific custom field name ($fieldname), return an array of all unique
values contained in this field by *any* published posts which use a custom field 
of that name, regardless of post_type, and regardless of whether or not the custom 
field is defined as a "standardized" custom field. 

This filters out empty values ('' or null). 

INPUT:
@param	string	$fieldname	name of a custom field
@param	string	$order	specify the order of the results returned, either 'ASC' (default) or 'DESC'

@return	array 	unique values.

USAGE:
Imagine a custom post_type that profiles you and your friends. There is a custom 
field that defines your favorite cartoon named 'favorite_cartoon':

	$array = get_unique_values_this_custom_field('favorite_cartoon');
	
	print_r($array);
		Array ( 'Family Guy', 'South Park', 'The Simpsons' );

*/
function get_unique_values_this_custom_field($fieldname, $order='ASC')
{
	global $wpdb;

	$order = strtoupper($order);
	// Sanitize
	if ($order != 'ASC' && $order != 'DESC') {
		$order = 'ASC';  // back to default.
	}
	$query = "SELECT DISTINCT {$wpdb->postmeta}.meta_value 
		FROM {$wpdb->postmeta} JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
		WHERE {$wpdb->postmeta}.meta_key=%s 
		AND {$wpdb->postmeta}.meta_value !=''
		AND {$wpdb->posts}.post_status = 'publish'
		ORDER BY {$wpdb->postmeta}.meta_value $order";

	$sql = $wpdb->prepare($query, $fieldname);
	//print '<textarea>'.$sql.'</textarea>';
	$results = $wpdb->get_results( $sql, ARRAY_N );	
	//print_r($results); exit;
	// Repackage
	$uniques = array();
	foreach ($results as $r )
	{
		$uniques[] = $r[0];
	}

	return array_unique($uniques);
}

//------------------------------------------------------------------------------
/**
SYNOPSIS: Used inside theme files, e.g. single.php or single-my_post_type.php
where you need to print out the value of a specific custom field.

This prints the 1st instance of the meta_key identified by $fieldname 
associated with the current post. See get_post_meta() for more details.

INPUT: 
	$fieldname (str) the name of the custom field as defined inside the 
		Manage Custom Fields area for a particular content type.
OUTPUT:
	The contents of that custom field for the current post.
*/
function print_custom_field($fieldname, $extra=null)
{
	print get_custom_field($fieldname, $extra);
}

//------------------------------------------------------------------------------
/**
* Convenience function to print the result of get_custom_field_meta().  See
* get_custom_field_meta.
*/
function print_custom_field_meta($fieldname, $item, $post_type=null)
{
	print call_user_func_array('get_custom_field_meta', func_get_args());
}

/*EOF*/