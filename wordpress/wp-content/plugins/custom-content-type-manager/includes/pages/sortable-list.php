<script>
	
	function save_order()
	{
		var i=0;
		jQuery(".store_me").each(function(){
	        jQuery(this).toggleClass("example");
	        jQuery(this).val(i);
			i=i+1;
      	});
	}
</script>


<script>
jQuery(function() {
	jQuery( "#custom-field-list" ).sortable();
	jQuery( "#custom-field-list" ).disableSelection();

	
});
</script>

<form id="custom_post_type_manager_basic_form" method="post" action="<?php print $action_link; ?>">

<div class="wrap">
	<h2>
	<a href="?page=<?php print self::admin_menu_slug;?>" title="<?php _e('Back'); ?>"><img src="<?php print CCTM_URL; ?>/images/cctm-logo.jpg" alt="summarize-posts-logo" width="88" height="55" /></a>
	<?php _e('Content Type', CCTM_TXTDOMAIN);?> <strong><?php print $post_type; ?></strong> : <?php _e('Custom Fields', CCTM_TXTDOMAIN);?> 
		<a href="?page=<?php print self::admin_menu_slug;?>" title="<?php _e('Back'); ?>" class="button"><?php _e('Back'); ?></a> 
		<a href="http://code.google.com/p/wordpress-custom-content-type-manager/wiki/ManageCustomFields" title="Managing custom fields" target="_blank">
			<img src="<?php print CCTM_URL; ?>/images/question-mark.gif" width="16" height="16" />
		</a>
		
		</h2>

	<?php print $msg; ?>
	<br />
	<?php print self::_get_available_custom_field_types($post_type); ?>
	<?php 
	// The page ends here if there are no custom fields defined.
	if (!$def_cnt) { return; } 
	?>	
	

	<br />
	
	<?php wp_nonce_field($action_name, $nonce_name); ?>
	
<table class="wp-list-table widefat plugins" cellspacing="0">
<thead>
	<tr>
		<th scope='col' id='sorter' class=''  style="width: 10px;">&nbsp;</th>
		<th scope='col' id='icon' class=''  style="width: 20px;">&nbsp;</th>
		<th scope='col' id='name' class=''  style="width: 200px;"><?php _e('Field', CCTM_TXTDOMAIN); ?></th>
		<th scope='col' id='description' class='manage-column column-description'  style=""><?php _e('Description', CCTM_TXTDOMAIN); ?></th>	
	</tr>
</thead>

<tfoot>
	<tr>
		<th scope='col' id='sorter' class=''  style="">&nbsp;</th>
		<th scope='col' id='icon' class=''  style="width: 20px;">&nbsp;</th>
		<th scope='col' id='name' class=''  style="width: 200px;"><?php _e('Field', CCTM_TXTDOMAIN); ?></th>
		<th scope='col' id='description' class='manage-column column-description'  style=""><?php _e('Description', CCTM_TXTDOMAIN); ?></th>	
	</tr>
</tfoot>

<tbody id="custom-field-list">

	<?php print $fields; ?>
	
</tbody>
</table>

<br />

<input type="submit" 
		class="button-primary" 
		onclick="javascript:save_order();" value="<?php _e('Save Field Order', CCTM_TXTDOMAIN ); ?>" />

<?php print self::_link_reset_all_custom_fields($post_type); ?>

</form>
</div>