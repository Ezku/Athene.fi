<?php
/*------------------------------------------------------------------------------
This file is an independent controller, used to query the WordPress database
and provide an ID from wp_posts identifying a specific post of some kind.  This
is effectively a foreign key, and it can point to a PDF, a JPEG image, a post,
a page, etc. because they ALL live in the wp_posts table.

This controller can be called as an iFrame or via an AJAX request, and it 
spits out different output
during each -- this is dictaged by the $_GET['mode'] parameter:

http://site.com/this_page.php --> triggers "normal" iFrame access
	(this will return the full tpl/main.tpl -- full <html>,<head>,<body>, etc.

http://site.com/this_page.php?mode=1  --> triggers AJAX access
	This is used to only provide the <divs> which power dynamic search results.

See the PostSelector class for details about the return_Ajax() and return_iFrame()
functions.

This is never meant to be accessed directly, rather it is intended to be triggered
via the WP manager when a user chooses a new image or a rew relation via 
a custom field setup with this plugin.

INCOMING URL PARAMETERS:
	See $PostSelector->_read_inputs()

	mode	(optional).  If set, this controller returns results for AJAX
	
	fieldname = (req) id of field receiving the wp_posts.ID 

	s = (opt) search term
	m = (opt) month+year 
	post_mime_type = (opt) image | video | audio | all. Default: all
	page (opt) integer defining which page of results we're displaying. Default: 0

//! NEW TODO:
	sort_col
	dir sorting direction


OUTPUT:
The value of the fieldname identified by 'fieldname' will be updated, e.g.

	<input type="hidden" id="myMediaField" value="123" />
	
A div with the id of fieldname + '_preview' will get injected with an img tag
representing a thumbnail of the selected media item, e.g. 

	<div id="myMediaField_preview"><img src="..." /></div>
------------------------------------------------------------------------------*/
// To tie into WP, we come in through the backdoor, by including the config.
@include_once( realpath('../../../').'/wp-load.php' );
@include_once( realpath('../../../').'/wp-admin/includes/post.php'); // TO-DO: what if the wp-admin dir changes?
$this_dir = dirname(__FILE__);
include_once($this_dir.'/includes/constants.php');
include_once($this_dir.'/includes/PostSelector.php');
include_once($this_dir.'/includes/Pagination.php');
include_once($this_dir.'/includes/Pagination.conf.php');

// Future: if we ever change permissions on the custom fields, this should change too. 
// put this into constants.php ??
if ( !current_user_can('edit_posts') )
{
	wp_die(__('You do not have permission to edit posts.'));
}

$PS = new PostSelector();

$output = '';

// Determines if this is an AJAX request or an iFramed thickbox		
if ( isset($_GET['mode']) )
{
	$output = $PS->return_Ajax();
}
else
{
	$output = $PS->return_iFrame();
}

print $output;

/*EOF*/