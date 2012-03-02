<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
	<div class="option">
		<?php printf( __( 'Priority: %1.1f', 'search-unleashed' ), $module->priority ); ?>
		
		<a href="<?php echo admin_url('admin-ajax.php') ?>?action=su_module_edit&amp;id=<?php echo $module->id(); ?>">
			<img src="<?php echo $this->url () ?>/images/edit.png" width="16" height="16" alt="Edit"/>
		</a>
	</div>
	
<label>
	<input type="checkbox" name="<?php echo $module->id () ?>" <?php if ($module->is_active()) echo ' checked="checked"' ?> />

	<?php echo $module->name (); ?>
</label>