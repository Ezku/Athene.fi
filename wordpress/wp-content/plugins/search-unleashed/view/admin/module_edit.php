<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<strong><?php echo $module->name (); ?></strong>

<form action="<?php echo admin_url('admin-ajax.php') ?>" method="post" accept-charset="utf-8" id="form_<?php echo $module->id () ?>">
	<table class="form-table">
		<?php $module->edit (); ?>
		<tr>
			<th><?php _e( 'Priority', 'search-unleashed' )?></th>
			<td>
				<input type="text" name="priority" value="<?php echo $module->priority ?>" />
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input class="button-primary" type="submit" name="save" value="<?php _e ('Save', 'search-unleashed'); ?>"/>
				<input class="button-secondary" type="submit" name="cancel" value="<?php _e ('Cancel', 'search-unleashed'); ?>"/>

				<input type="hidden" name="id" value="<?php echo $module->id () ?>"/>
				<input type="hidden" name="action" value="su_module_save"/>
				<?php wp_nonce_field ('searchunleashed-module_save'); ?>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript" charset="utf-8">
	SearchUnleashed.module_form('#form_<?php echo $module->id () ?>', '#module_<?php echo $module->id () ?>','<?php echo wp_create_nonce ('searchunleashed-module')?>');
</script>