<?php
/*------------------------------------------------------------------------------
This is complicated.  You can't submit $_FILES to be uploaded via a simple
Ajax form submission (other form fields are fine to submit like this). 
So the problem is that we NEED to submit the upload form via Ajax, and because
the upload form is iFramed in a thickbox, we can't submit the page directly, 
else we lose the thickbox. The solution is to post the data to an iFrame that
includes this page.
------------------------------------------------------------------------------*/
@include_once( realpath('../../../').'/wp-load.php' );
include_once('includes/constants.php');
@include_once( realpath('../../../').'/wp-admin/includes/admin.php' );
@include_once( realpath('../../../').'/wp-admin/includes/media.php' );
@include_once( realpath('../../../').'/wp-admin/includes/file.php' );

if ( !empty($_POST) && !empty($_FILES) )// && isset($_POST['async-upload']) && !empty($_FILES) )
{
	print '<img src="'.CCTM_URL.'/images/progress.gif" width="75" height="75" />';

	// This is a WP built-in, poorly documented.
	$id = media_handle_upload('async-upload',''); //post id of Client Files page
	// on success, $id should be an inteter (last_insert_id), on error, it's a WP_Error Object 
	// BUT.. it's a valid use case that this would get called when defining a custom field.  In that case, 
	// there is no post_id.
	if ( is_object($id) )
	{
		printf( __("<p>There was a problem uploading. Did you select a file? If you continue to have problems, please try using WordPress' <a href='media-new.php'>built-in manager</a> to upload new media.</p>", CCTM_TXTDOMAIN) );
	}
	else
	{
		/*------------------------------------------------------------------------------
		This javascript should refresh the parent frame after the form submits.
		------------------------------------------------------------------------------*/
		?>
		<script type="text/javascript">
			function addLoadEvent(func) {
				var oldonload = window.onload;
				if (typeof window.onload != 'function') {
					window.onload = func;
				} else {
					window.onload = function() {
				  		if (oldonload) {
				    		oldonload();
				  		}
			  			func();
					}
				}
			}
			
			addLoadEvent(parent.clear_search);
		</script>
		
		<p><span class="button" onclick="javascript:parent.clear_search();">Click here</span> if your page does not refresh.</p>
			
<?php
	}

}
//Form not submitted yet
else
{
	
?>
	<p>If you are having trouble using this uploader, try using the standard WordPress uploader. Save your work, then click the "Media" menu item on the left to <a href="<?php print get_admin_url(); ?>media-new.php" target="_parent">Add New Media</a>.</p>
	<!-- span class="button" onclick="javascript:parent.clear_search();">Back</span -->
	
<?php 
}


/*EOF*/