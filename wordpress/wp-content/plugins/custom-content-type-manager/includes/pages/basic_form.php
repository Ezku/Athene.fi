<?php
/*------------------------------------------------------------------------------
Basic form template. Made for use by the CCTM class.
This template expects the following variables:

	$style; 		// can be used to print <style> block above the form.
	$page_header; 	// appears at the top of the page  
	$fields;		// any additional form fields
	$action_name; 	// used by wp_nonce_field
	$nonce_name; 	// used by wp_nonce_field
	$submit;		// text that appears on the primary submit button
	
	$cancel_target_url // (optional) Default is '?page='.self::admin_menu_slug;
------------------------------------------------------------------------------*/
if ( !isset($cancel_target_url) ) {
	$cancel_target_url = '?page='.self::admin_menu_slug;
}

?>
<?php if (isset($style)) { print $style; } ?>
<div class="wrap">

	<h2>
	<a href="?page=<?php print self::admin_menu_slug;?>" title="<?php _e('Back'); ?>"><img src="<?php print CCTM_URL; ?>/images/cctm-logo.jpg" alt="summarize-posts-logo" width="88" height="55" /></a> 
	<?php print $page_header ?> <a class="button" href="?page=<?php print self::admin_menu_slug;?>"><?php _e('Cancel'); ?></a> </h2>
	
	<?php print $msg; ?>

	<form id="custom_post_type_manager_basic_form" method="post">
	
		<?php print $fields; ?>
	
		<?php wp_nonce_field($action_name, $nonce_name); ?>
	<br/>
		<div class="custom_content_type_mgr_form_controls">
			<input type="submit" name="Submit" class="button-primary" value="<?php print $submit; ?>" />
			<a class="button" href="<?php print $cancel_target_url; ?>"><?php _e('Cancel'); ?></a> 
		</div>
	
	</form>
</div>