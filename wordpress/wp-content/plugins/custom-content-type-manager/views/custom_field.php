<form id="custom_post_type_manager_basic_form" method="post" action="">


	<table class="custom_field_info">
		<tr>
			<td colspan="2">
				<h3 class="field_type_name"><?php print $data['name']; ?></h3>
			</td>
		</tr>
		<tr>
			<td>
				<span class="custom_field_icon"><?php print $data['icon']; ?></span>
			</td>
			<td>
				<span class="custom_field_description"><?php print $data['description']; ?>
				<br />
				<a href="<?php print $data['url']; ?>" target="_blank"><?php _e('More Information', CCTM_TXTDOMAIN); ?></a>
				</span>
				
				
			</td>
		</tr>
	</table>
	<?php wp_nonce_field($data['action_name'], $data['nonce_name']); ?>
	
	<?php print $data['fields']; ?>
	
	<br />
	<input type="submit" class="button-primary" value="<?php _e('Save', CCTM_TXTDOMAIN ); ?>" />

</form>
