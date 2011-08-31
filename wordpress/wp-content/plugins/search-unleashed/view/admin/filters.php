<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php	$this->render_admin ('annoy'); ?>
	
	<?php screen_icon(); ?>
	
	<h2><?php _e ('Search Unleashed | Active Filters', 'search-unleashed'); ?></h2>
	<?php $this->submenu (true); ?>
	
	<p style="clear: both"><?php _e( 'The following filters will be active when Search Unleashed is indexing posts or comments. If you wish to prevent a filter from running during this indexing operation then disable it here. This will not affect the running of the filter anywhere else.', 'search-unleashed' ); ?></p>
	
	<form method="post" action="">
		<?php wp_nonce_field ('searchunleashed-options'); ?>
		
		<ul>
			<?php foreach ( (array)$filters AS $name => $id ) : ?>
				<li><label><input type="checkbox" name="filters[]" value="<?php echo $id; ?>"<?php if ( !in_array( $id, $disabled_filters )) echo ' checked="checked"'; ?>/> <?php echo $name; ?></label></li>
			<?php endforeach; ?>
		</ul>
	
		<p><input type="submit" class="button-primary" name="save" value="<?php _e ('Save Filters', 'search-unleashed'); ?>"/></p>
	</form>
	
	<p><?php _e( 'Note that this feature is experimental', 'search-unleashed' ); ?></p>
</div>
