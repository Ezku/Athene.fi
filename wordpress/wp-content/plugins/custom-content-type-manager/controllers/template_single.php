<?php
//------------------------------------------------------------------------------
/**
* Manager Page -- called by page_main_controller()
* Show what a single page for this custom post-type might look like.  This is
* me throwing a bone to template editors and creators.
*
* I'm using a tpl and my parse() function because I have to print out sample PHP
* code and it's too much of a pain in the ass to include PHP without it executing.
*
* @param string $post_type
*/

$data 				= array();
$data['page_title']	= sprintf(__('Sample Themes for %s', CCTM_TXTDOMAIN), "<em>$post_type</em>");
$data['help']		= 'http://code.google.com/p/wordpress-custom-content-type-manager/wiki/SampleTemplates?ts=1317363617&updated=SampleTemplates';
$data['menu'] 		= sprintf('<a href="?page=cctm&a=list_post_types" class="button">%s</a>', __('Back', CCTM_TXTDOMAIN) );
$data['msg']		= '';
$data['post_type'] = $post_type;

// Validate post type
if (!self::_is_existing_post_type($post_type) ) {
	self::_page_display_error();
	return;
}

$current_theme_name = get_current_theme();
$current_theme_path = get_stylesheet_directory();

$hash = array();

$tpl = file_get_contents( CCTM_PATH.'/tpls/samples/single_post.tpl');
$tpl = htmlentities($tpl);

$data['single_page_msg'] = sprintf( __('WordPress supports a custom theme file for each registered post-type (content-type). Copy the text below into a file named <strong>%s</strong> and save it into your active theme.', CCTM_TXTDOMAIN)
	, 'single-'.$post_type.'.php'
);
$data['single_page_msg'] .= sprintf( __('You are currently using the %1$s theme. Save the file into the %2$s directory.', CCTM_TXTDOMAIN)
	, '<strong>'.$current_theme_name.'</strong>'
	, '<strong>'.$current_theme_path.'</strong>'
);


// built-in content types don't verbosely display what fields they display
/* Array
(
[product] => Array
(
    [supports] => Array
        (
            [0] => title
            [1] => editor
            [2] => author
            [3] => thumbnail
            [4] => excerpt
            [5] => trackbacks
            [6] => custom-fields
        )
*/

// Check the TYPE of custom field to handle image and relation custom fields.
// title, author, thumbnail, excerpt
$custom_fields_str = '';
$builtin_fields_str = '';
$comments_str = '';

// Built-in Fields
if (isset(self::$data['post_type_defs'][$post_type]['supports']) && is_array(self::$data['post_type_defs'][$post_type]['supports'])) {
	if ( in_array('title', self::$data['post_type_defs'][$post_type]['supports']) ) {
		$builtin_fields_str .= "\n\t<h1><?php the_title(); ?></h1>";
	}
	if ( in_array('editor', self::$data['post_type_defs'][$post_type]['supports']) ) {
		$builtin_fields_str .= "\n\t\t<?php the_content(); ?>";
	}
	if ( in_array('author', self::$data['post_type_defs'][$post_type]['supports']) ) {
		$builtin_fields_str .= "\n\t\t<?php the_author(); ?>";
	}
	if ( in_array('thumbnail', self::$data['post_type_defs'][$post_type]['supports']) ) {
		$builtin_fields_str .= "\n\t\t<?php the_post_thumbnail(); ?>";
	}
	if ( in_array('excerpt', self::$data['post_type_defs'][$post_type]['supports']) ) {
		$builtin_fields_str .= "\n\t\t<?php the_excerpt(); ?>";
	}
	if ( in_array('comments', self::$data['post_type_defs'][$post_type]['supports']) ) {
		$comments_str .= "\n\t\t<?php comments_template(); ?>";
	}
} 
// We show this for built-in types
elseif ($post_type == 'post') {
	$builtin_fields_str .= "\n\t<h1><?php the_title(); ?></h1>";
	$builtin_fields_str .= "\n\t\t<?php the_content(); ?>";
	$builtin_fields_str .= "\n\t\t<?php the_author(); ?>";
	$builtin_fields_str .= "\n\t\t<?php the_post_thumbnail(); ?>";
	$builtin_fields_str .= "\n\t\t<?php the_excerpt(); ?>";
	$comments_str .= "\n\t\t<?php comments_template(); ?>";
}
elseif ($post_type == 'page') {
	$builtin_fields_str .= "\n\t<h1><?php the_title(); ?></h1>";
	$builtin_fields_str .= "\n\t\t<?php the_content(); ?>";
	$builtin_fields_str .= "\n\t\t<?php the_author(); ?>";
	$builtin_fields_str .= "\n\t\t<?php the_post_thumbnail(); ?>";
}


// Custom fields
/*
if ( isset(self::$data['post_type_defs'][$post_type]['custom_fields']) 
	&& is_array(self::$data['post_type_defs'][$post_type]['custom_fields']) ) {
	foreach ( 	$def = self::$data['post_type_defs'][$post_type]['custom_fields'] as $cf ) {
		if (isset(self::$data['custom_field_defs'][$cf])) {
			$custom_fields_str .= sprintf("\t\t<strong>%s:</strong> <?php print_custom_field('%s'); ?><br />\n"
				, self::$data['custom_field_defs'][$cf]['label'], self::$data['custom_field_defs'][$cf]['name']);
		}
	}
}
*/
if ( isset(self::$data['post_type_defs'][$post_type]['custom_fields']) 
	&& is_array(self::$data['post_type_defs'][$post_type]['custom_fields']) ) {
	foreach ( 	$def = self::$data['post_type_defs'][$post_type]['custom_fields'] as $cf ) {
		if (isset(self::$data['custom_field_defs'][$cf])) {
			// Get the example from the Output Filter
			if (isset(self::$data['custom_field_defs'][$cf]['output_filter']) 
				&& !empty(self::$data['custom_field_defs'][$cf]['output_filter'])
				&& self::$data['custom_field_defs'][$cf]['output_filter'] != 'raw'
				&& CCTM::include_output_filter_class(self::$data['custom_field_defs'][$cf]['output_filter'])
			) {
				$filter_class = CCTM::classname_prefix.self::$data['custom_field_defs'][$cf]['output_filter'];		
				$OutputFilter = new $filter_class();
				$custom_fields_str .= sprintf("\t\t<strong>%s:</strong> %s<br />\n"
					, self::$data['custom_field_defs'][$cf]['label']
					, $OutputFilter->get_example(self::$data['custom_field_defs'][$cf]['name'])
				);

			
			}
			else {
				$custom_fields_str .= sprintf("\t\t<strong>%s:</strong> <?php print_custom_field('%s'); ?><br />\n"
					, self::$data['custom_field_defs'][$cf]['label'], self::$data['custom_field_defs'][$cf]['name']);
			}
		}
	}
}

// Populate placeholders
$hash['post_type'] = $post_type;
$hash['built_in_fields'] = $builtin_fields_str;
$hash['custom_fields'] = $custom_fields_str;
$hash['comments'] = $comments_str;

$data['single_page_sample_code'] = self::parse($tpl, $hash, true);
//die('d.x.x.');
// include CCTM_PATH.'/views/sample_template.php';
$data['content'] = CCTM::load_view('sample_template.php', $data);
print CCTM::load_view('templates/default.php', $data);