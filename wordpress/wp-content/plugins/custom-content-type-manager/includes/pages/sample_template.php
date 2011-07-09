<div class="wrap">
<style type="text/css">
	.sample_code_textarea { 
		width: 100%; 
		margin: 0; 
		padding: 0; 
		border-width: 0; }
</style>

	
	<h2><a href="?page=<?php print self::admin_menu_slug;?>" title="<?php _e('Back'); ?>"><img src="<?php print CCTM_URL; ?>/images/cctm-logo.jpg" alt="summarize-posts-logo" width="88" height="55" /></a>
		<?php print $post_type;?> Sample Templates <a href="?page=<?php print self::admin_menu_slug; ?>" class="button add-new-h2"><?php _e('Back'); ?></a></h2>

	<h3 class="cctm_subheading">single-<?php print $post_type ?>.php</h3>
	<p>
		<?php print $single_page_msg; ?>
	</p>
	<br />

	<textarea cols="80" rows="10" class="sample_code_textarea" style="border: 1px solid black;"><?php print $single_page_sample_code; ?></textarea>

	<h3 class="cctm_subheading"><?php _e('CSS for Manager Pages', CCTM_TXTDOMAIN); ?></h3>
		<p>All of the form fields have plenty of CSS hooks so you can customize the way the manager displays for any particular content type. Add any overrides you want to your theme's <code>editor-style.css</code> file.</p>
	
	<h3 class="cctm_subheading"><?php _e('HTML for Manager Pages', CCTM_TXTDOMAIN); ?></h3>
		<p>Coming...</p>

</div>