<?php
/*------------------------------------------------------------------------------

------------------------------------------------------------------------------*/
?>
<div class="wrap">
	<h2>
		<img src="<?php print CCTM_URL; ?>/images/cctm-logo.jpg" alt="summarize-posts-logo" width="88" height="55" class="polaroid"/> 
		CCTM : Export 
		<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/Export" title="Exporting your CCTM Definition" target="_blank">
			<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
		</a>

	</h2>

	<?php print $msg; ?>

	<h2>Export</h2>


		<p><?php _e('Before exporting, please add a bit more information to your current setup. Your settings will be preserved.', CCTM_TXTDOMAIN); ?></p>
		
		<form id="cctm_export_form"  method="post">
			<?php wp_nonce_field($action_name, $nonce_name); ?>
			
			<label for="title" class="<?php print self::_get_class('title', 'text_label'); ?>" id="title_label"><?php _e('Title', CCTM_TXTDOMAIN); ?></label><br/>
			<input type="text" name="title" class="<?php print self::_get_class('title', 'text'); ?>" id="title" value="<?php print self::_get_value($settings['export_info'], 'title'); ?>" /><br/>
			
			<label for="author" class="<?php print self::_get_class('author', 'text_label'); ?>" id="author_label"><?php _e('Author', CCTM_TXTDOMAIN); ?></label><br/>
			<input type="text" name="author" class="<?php print self::_get_class('author', 'text'); ?>"id="author" value="<?php print self::_get_value($settings['export_info'], 'author'); ?>" /><br/>
		
			<label for="url" class="<?php print self::_get_class('url', 'text_label'); ?>" id="author_label">Your URL</label><br/>
			<input type="text" name="url" class="<?php print self::_get_class('url', 'text'); ?>" id="url" size="60" value="<?php print self::_get_value($settings['export_info'], 'url'); ?>" /><br/>
		
			<label for="description" class="<?php print self::_get_class('author', 'description_label'); ?>" id="description_label"><?php _e('Description', CCTM_TXTDOMAIN); ?></label><br/>
			<textarea name="description" class="<?php print self::_get_class('description', 'textarea'); ?>" id="description" rows="5" cols="40"><?php print self::_get_value($settings['export_info'], 'description'); ?></textarea>
			<br/>
			<input type="submit" name="submit" class="button" value="<?php _e('Download'); ?>"/>
			 
		</form>

	<br/>
	
	<?php include('components/footer.php'); ?>
	
</div>