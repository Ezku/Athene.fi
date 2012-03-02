<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><div class="wrap">
	<?php	$this->render_admin ('annoy'); ?>

	<?php screen_icon(); ?>
	<h2><?php _e ('Search Unleashed | Index', 'search-unleashed'); ?></h2>
	<?php $this->submenu (true); ?>
	
	<?php if ($total == 0) : ?>
		<p style="clear: both"><?php _e ('You have no items in the search index.  Please re-index soon!', 'search-unleashed'); ?></p>
	<?php else : ?>
		<p style="clear: both"><?php printf (_n('You have <strong>%s</strong> item in the search index.', 'You have <strong>%s</strong> items in the search index.', $total, 'search-unleashed'), number_format( $total, 0, '.', ',' )) ?>
		</p>
	<?php endif; ?>
	
	<p><?php printf (@__ngettext ('<strong>%s search</strong> has been performed using this plugin. ', '<strong>%s searches</strong> have been performed using this plugin. ', $options['count'], 'search-unleashed'), number_format( $options['count'], 0, '.', ',') )?></p>
</div>

<?php if ( strtolower( get_class( $engine ) ) != 'defaultengine' ) : ?>
<div class="wrap">
	<h3><?php _e ('Re-Index Search Unleashed', 'search-unleashed'); ?></h3>
	
	<p><?php _e ('You need to re-index the search database when', 'search-unleashed'); ?>:</p>
	<ul class="bulleted">
		<li><?php _e ('You first install this plugin', 'search-unleashed'); ?></li>
		<li><?php _e ('You change what to include in the search', 'search-unleashed'); ?></li>
		<li><?php _e ('You install another plugin that may alter posts or comments', 'search-unleashed'); ?></li>
	</ul>
	
	<p><?php _e ('Changes to individual posts &amp; comments will be automatically re-indexed - <strong>you do not need to re-index after editing posts</strong>.', 'search-unleashed'); ?></p>
	
	<p style="text-align: center">
		<input class="button-primary" type="submit" name="reindex" value="<?php _e ('Re-Index', 'search-unleashed'); ?>"/>
		<img id="loading" style="vertical-align: middle; display: none" src="<?php echo $this->url(); ?>/images/small.gif" width="16" height="16" alt="Small"/>
	</p>
	
	<div id="wrapper" style="display: none">
	</div>
</div>

<script type="text/javascript" charset="utf-8">
	jQuery(function() {
		jQuery('#wrapper').Progressor( {
			start:    jQuery('input[name=reindex]'),
			cancel:   '<?php _e( 'Cancel', 'search-unleashed' ); ?>',
			url:      '<?php echo admin_url('admin-ajax.php') ?>',
			nonce:    '<?php echo wp_create_nonce ('searchunleashed-index')?>',
			finished: '<?php _e( 'Finished!', 'search-unleashed'); ?>'
		});
	});
</script>
<?php else : ?>
	<div class="wrap">
	<p><?php _e( 'You currently have the Default WordPress search engine enabled - there is no need to index anything.', 'search-unleashed' )?></p>
	</div>
<?php endif; ?>