<?php
/*------------------------------------------------------------------------------
http://stackoverflow.com/questions/168455/how-do-you-post-to-an-iframe
But there was a bug on that page... the src URL of the iFrame and the action URL
of the form must be the same.
------------------------------------------------------------------------------*/
@include_once( realpath('../../../').'/wp-load.php' );
include_once('includes/constants.php');
?>
<form id="file-form" enctype="multipart/form-data" action="<?php print CCTM_URL; ?>/upload_form_handler.php" method="post" target="cctm_upload_iframe">	
	<?php wp_nonce_field('client-file-upload'); ?>
	<div id="html-upload-ui">
		<p id="async-upload-wrap">
			<label class="screen-reader-text" for="async-upload">Upload</label>
			<input type="file" name="async-upload" id="async-upload" /> 
			<input type="submit" class="button" value="Upload" name="html-upload" />
			<span onclick="javascript:clear_search();">Cancel</span>
		</p>
		
		<div class="clear"></div>
	</div>
</form>
<iframe name="cctm_upload_iframe" src="<?php print CCTM_URL; ?>/upload_form_handler.php"></iframe>