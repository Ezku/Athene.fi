<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php	$this->render_admin ('annoy'); ?>

	<?php screen_icon(); ?>
	
	<h2><?php _e ('Search Unleashed | Modules', 'search-unleashed'); ?></h2>

	<?php $this->submenu (true); ?>

	<p style="clear: both"><?php _e ('Select what to include in the search', 'search-unleashed'); ?>:</p>

	<ul class="modules">
		<?php foreach ($types AS $module) : ?>
			<li class="<?php if (!isset ($options['active'][$module->id ()])) echo 'disabled' ?>" id="module_<?php echo $module->id () ?>">
				<?php $this->render_admin ('module', array ('module' => $module, 'active' => isset ($options['active'][$module->id ()]) ? true : false))?>
			</li>
		<?php endforeach; ?>
	</ul>
	
	<p><?php _e( 'Note that enabling or disabling a module will require you to re-index the search database', 'search-unleashed' ); ?></p>
	
	<script type="text/javascript" charset="utf-8">
		jQuery(function() {
			SearchUnleashed.module_list('<?php echo admin_url('admin-ajax.php') ?>','<?php echo wp_create_nonce ('searchunleashed-module')?>');
		});
	</script>
</div>
